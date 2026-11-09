<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\HrAttendance;
use App\Models\HrAttendanceRemark;
use App\Models\HrAttendanceUnlock;
use App\Models\GenericCase;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class FinanceAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Enforce strict Finance user access in All Divisions mode.
     */
    protected function authorizeFinance(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isSuperAdmin = ($user->acc_username === 'superadminrdw') || session('impersonated_by_god');

        $isFinance = $user->isFinance() || in_array($area, ['fin', 'finance', 'it'], true);

        if (!$isFinance && !$isSuperAdmin) {
            abort(403, 'Access denied. Only Finance department users (in All Divisions mode) are authorized to unlock attendance.');
        }
    }

    /**
     * List of employees for attendance unlocking.
     */
    public function unlockList(Request $request): View
    {
        $this->authorizeFinance();

        $term = trim((string) $request->get('term', ''));
        $unitId = $request->get('unit_id', 'all');
        $status = $request->get('status', 'Current');

        $query = DB::table('hr.emps')
            ->leftJoin('cen.units', 'hr.emps.emp_unt_id', '=', 'cen.units.unt_id')
            ->select(
                'hr.emps.emp_id',
                'hr.emps.emp_name',
                'hr.emps.emp_rank',
                'hr.emps.emp_status',
                'hr.emps.emp_joindt',
                'cen.units.unt_name',
                'cen.units.unt_namesh'
            );

        if ($status === 'Current') {
            $query->whereRaw('LOWER(hr.emps.emp_status) IN (?, ?)', ['active', 'current']);
        } elseif ($status === 'Previous') {
            $query->whereRaw('LOWER(hr.emps.emp_status) NOT IN (?, ?)', ['active', 'current']);
        }

        if ($unitId !== 'all' && !empty($unitId)) {
            $query->where('hr.emps.emp_unt_id', (int) $unitId);
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('hr.emps.emp_name', 'ilike', "%{$term}%")
                  ->orWhere('hr.emps.emp_id', 'ilike', "%{$term}%")
                  ->orWhere('hr.emps.emp_rank', 'ilike', "%{$term}%");
            });
        }

        $employees = $query->orderBy('hr.emps.emp_name', 'asc')->paginate(25)->withQueryString();
        $divisions = DB::table('cen.units')->orderBy('unt_name')->get();

        return view('finance.attendance.unlock_list', compact('employees', 'divisions', 'term', 'unitId', 'status'));
    }

    /**
     * View monthly attendance of a specific employee for selective unlocking.
     */
    public function unlockEmployee(Request $request, string $empId): View
    {
        $this->authorizeFinance();

        $employee = DB::table('hr.emps')
            ->leftJoin('cen.units', 'hr.emps.emp_unt_id', '=', 'cen.units.unt_id')
            ->where('hr.emps.emp_id', $empId)
            ->first();

        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        $monthStr = $request->get('month', Carbon::now()->format('Y-m'));
        try {
            $monthDate = Carbon::parse($monthStr . '-01');
        } catch (Exception) {
            $monthDate = Carbon::now()->startOfMonth();
            $monthStr = $monthDate->format('Y-m');
        }

        $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');

        // Retrieve or generate attendance record
        $att = HrAttendance::where('att_emp_id', $empId)
            ->whereDate('att_startdt', $monthDate->format('Y-m-d'))
            ->first();

        if (!$att) {
            // Attempt to create sheet if not yet generated
            $this->attendanceService->makeAttendanceSheet($monthStr);
            $att = HrAttendance::where('att_emp_id', $empId)
                ->whereDate('att_startdt', $monthDate->format('Y-m-d'))
                ->first();
        }

        // Fetch all general case files for searchable dropdown authorization
        $caseFiles = GenericCase::orderBy('gcs_id', 'desc')->get([
            'gcs_id', 'gcs_file_no', 'gcs_title', 'gcs_status', 'gcs_category'
        ]);

        $approvedCaseFiles = $caseFiles->filter(function($c) {
            return strtolower(trim((string)$c->gcs_status)) === 'approved';
        });

        $otherCaseFiles = $caseFiles->filter(function($c) {
            return strtolower(trim((string)$c->gcs_status)) !== 'approved';
        });

        // Fetch regular employee daily remarks for this attendance sheet
        $remarksMap = [];
        if ($att) {
            $remarksRows = DB::table('hr.attendanceremarks')
                ->where('atr_att_id', $att->att_id)
                ->get();
            foreach ($remarksRows as $rem) {
                // Ignore legacy tags in employee daily remarks
                $cleanedRem = trim(str_replace(['[FINANCE_UNLOCKED]', '[FINANCE_RELOCKED]', '[FINANCE_LOCKED]'], '', (string)$rem->atr_remarks));
                $remarksMap[$rem->atr_attday] = $cleanedRem;
            }
        }

        // Fetch dedicated unlock records from hr.attendance_unlocks
        $unlocksMap = [];
        if ($att) {
            $unlockRows = HrAttendanceUnlock::where('aul_att_id', $att->att_id)
                ->orderBy('aul_id', 'desc')
                ->get();
            foreach ($unlockRows as $uRow) {
                if (!isset($unlocksMap[$uRow->aul_day])) {
                    $unlocksMap[$uRow->aul_day] = $uRow;
                }
            }
        }

        $daysInMonth = (int) $monthDate->daysInMonth;
        $days = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayCarbon = $monthDate->copy()->setDay($d);
            $col = 'att_' . $d;
            $code = $att ? ($att->$col ?? '') : '';
            $rawRemark = $remarksMap[$d] ?? '';

            $unlockRecord = $unlocksMap[$d] ?? null;
            $isUnlockedByFinance = $unlockRecord ? (bool)$unlockRecord->aul_is_active : false;
            $isSystemLocked = $att ? $this->attendanceService->isCellLocked($att, $d) : true;

            $unlockCaseFileNo = $unlockRecord ? $unlockRecord->aul_case_file_no : null;
            $unlockCaseId = $unlockRecord ? $unlockRecord->aul_case_id : null;
            $unlockReason = $unlockRecord ? $unlockRecord->aul_remarks : '';
            $unlockedBy = ($unlockRecord && $unlockRecord->aul_unlocked_at) 
                ? ($unlockRecord->aul_username . ' on ' . Carbon::parse($unlockRecord->aul_unlocked_at)->format('d-M-Y H:i'))
                : null;
            $relockedBy = ($unlockRecord && $unlockRecord->aul_relocked_at)
                ? ($unlockRecord->aul_username . ' on ' . Carbon::parse($unlockRecord->aul_relocked_at)->format('d-M-Y H:i'))
                : null;

            $days[] = [
                'day'                 => $d,
                'date'                => $dayCarbon->format('Y-m-d'),
                'date_formatted'      => $dayCarbon->format('d-M-Y'),
                'day_name'            => $dayCarbon->format('D'),
                'is_weekend'          => $dayCarbon->isWeekend(),
                'code'                => $code,
                'raw_remark'          => $rawRemark,
                'clean_reason'        => $unlockReason,
                'case_file_no'        => $unlockCaseFileNo,
                'case_id'             => $unlockCaseId,
                'unlocked_by'         => $unlockedBy,
                'relocked_by'         => $relockedBy,
                'is_finance_unlocked' => $isUnlockedByFinance,
                'is_locked'           => $isSystemLocked,
            ];
        }

        return view('finance.attendance.unlock_employee', compact(
            'employee',
            'att',
            'monthStr',
            'monthDate',
            'prevMonth',
            'nextMonth',
            'caseFiles',
            'approvedCaseFiles',
            'otherCaseFiles',
            'days'
        ));
    }

    /**
     * Save unlock / lock status and remarks with mandatory General Case File reference.
     */
    public function saveUnlockDay(Request $request, string $empId): JsonResponse|RedirectResponse
    {
        $this->authorizeFinance();

        $request->validate([
            'month'   => 'required|date_format:Y-m',
            'day'     => 'required|integer|min:1|max:31',
            'status'  => 'required|in:unlocked,locked',
            'remarks' => 'nullable|string|max:1000',
            'case_id' => 'nullable|integer',
        ]);

        $monthStr = $request->input('month');
        $day = (int) $request->input('day');
        $status = $request->input('status');
        $remarksInput = trim((string) $request->input('remarks', ''));
        $caseId = $request->input('case_id');

        // Validation for unlock: Case File AND Remarks are strictly mandatory!
        if ($status === 'unlocked') {
            if (empty($caseId)) {
                $msg = 'Selecting an authorizing General Case File (E-Office) is mandatory to unlock attendance.';
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withErrors(['case_id' => $msg]);
            }

            if (empty($remarksInput)) {
                $msg = 'Mandatory Reason/Remarks is required to unlock an attendance day.';
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withErrors(['remarks' => $msg]);
            }
        }

        $case = null;
        if (!empty($caseId)) {
            $case = GenericCase::find($caseId);
            if (!$case && $status === 'unlocked') {
                $msg = 'Selected Case File does not exist.';
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withErrors(['case_id' => $msg]);
            }
        }

        $monthDate = Carbon::parse($monthStr . '-01');
        $dayCarbon = $monthDate->copy()->setDay($day);

        $att = HrAttendance::where('att_emp_id', $empId)
            ->whereDate('att_startdt', $monthDate->format('Y-m-d'))
            ->first();

        if (!$att) {
            $this->attendanceService->makeAttendanceSheet($monthStr);
            $att = HrAttendance::where('att_emp_id', $empId)
                ->whereDate('att_startdt', $monthDate->format('Y-m-d'))
                ->firstOrFail();
        }

        $user = Auth::user();
        $username = $user->acc_name ?? $user->acc_username ?? 'Finance';
        $accId = (int)($user->acc_id ?? 0);
        $now = Carbon::now();

        if ($status === 'unlocked') {
            // Deactivate previous active unlock for this day
            HrAttendanceUnlock::where('aul_att_id', $att->att_id)
                ->where('aul_day', $day)
                ->update(['aul_is_active' => false]);

            // Create new dedicated unlock entry
            $unlock = HrAttendanceUnlock::create([
                'aul_att_id'      => $att->att_id,
                'aul_emp_id'      => $empId,
                'aul_date'        => $dayCarbon->format('Y-m-d'),
                'aul_day'         => $day,
                'aul_status'      => 'unlocked',
                'aul_case_id'     => $case->gcs_id,
                'aul_case_file_no' => $case->gcs_file_no,
                'aul_remarks'     => $remarksInput,
                'aul_acc_id'      => $accId,
                'aul_username'    => $username,
                'aul_is_active'   => true,
                'aul_unlocked_at' => $now,
            ]);

            $message = "Day {$day} has been UNLOCKED successfully under Case File {$case->gcs_file_no}.";
            $unlockedByStr = "{$username} on " . $now->format('d-M-Y H:i');
            $caseFileNoStr = $case->gcs_file_no;
            $caseIdVal = $case->gcs_id;
        } else {
            // Relock: Deactivate active unlock record
            HrAttendanceUnlock::where('aul_att_id', $att->att_id)
                ->where('aul_day', $day)
                ->where('aul_is_active', true)
                ->update([
                    'aul_is_active'   => false,
                    'aul_status'      => 'locked',
                    'aul_relocked_at' => $now,
                ]);

            // Also clean legacy remark tag if present
            $existing = DB::table('hr.attendanceremarks')
                ->where('atr_att_id', $att->att_id)
                ->where('atr_attday', $day)
                ->value('atr_remarks');
            if ($existing && str_contains($existing, '[FINANCE_UNLOCKED]')) {
                $cleaned = trim(str_replace('[FINANCE_UNLOCKED]', '[FINANCE_RELOCKED]', $existing));
                DB::table('hr.attendanceremarks')
                    ->where('atr_att_id', $att->att_id)
                    ->where('atr_attday', $day)
                    ->update(['atr_remarks' => $cleaned]);
            }

            $message = "Day {$day} has been LOCKED successfully.";
            $unlockedByStr = null;
            $caseFileNoStr = null;
            $caseIdVal = null;
        }

        // Re-evaluate lock status
        $isNowLocked = $this->attendanceService->isCellLocked($att, $day);

        if ($request->wantsJson()) {
            return response()->json([
                'success'             => true,
                'message'             => $message,
                'day'                 => $day,
                'status'              => $status,
                'is_finance_unlocked' => ($status === 'unlocked'),
                'is_locked'           => $isNowLocked,
                'case_file_no'        => $caseFileNoStr,
                'case_id'             => $caseIdVal,
                'unlocked_by'         => $unlockedByStr,
            ]);
        }

        return back()->with('success', $message);
    }
}
