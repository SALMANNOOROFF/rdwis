<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceSaveRequest;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display the monthly attendance grid.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isCentral = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs'], true);

        // Mode handling with Session Persistence
        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = $isCentral ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));

        $gridData = $this->attendanceService->getAttendanceGrid($user, $monthStr, $mode);
        $isApprover = in_array(strtolower(trim((string) ($user->acc_auth ?? ''))), ['approver', 'editor'], true);

        return view('hr.attendance.index', array_merge($gridData, [
            'isCentral'    => $isCentral,
            'isApprover'   => $isApprover,
            'floorDate'    => AttendanceService::FLOOR_DATE,
            'currentMonth' => Carbon::now()->format('Y-m'),
        ]));
    }

    /**
     * Single-day drill-down view (dedicated page).
     */
    public function oneday(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isCentral = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs'], true);
        $isApprover = in_array(strtolower(trim((string) ($user->acc_auth ?? ''))), ['approver', 'editor'], true);

        $date = $request->query('date', now()->toDateString());
        $details = $this->attendanceService->getAttendanceDayDetails($user, $date);

        return view('hr.attendance.oneday', array_merge($details, [
            'isCentral'  => $isCentral,
            'isApprover' => $isApprover,
        ]));
    }

    /**
     * Monthly attendance summary for an employee (calls verified getEmpAttendanceSummary).
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $empId = $request->query('emp_id');
        if (!$empId) {
            return response()->json(['error' => 'Employee ID is required.'], 422);
        }

        if (!$this->attendanceService->canUserAccessEmployee($user, $empId)) {
            return response()->json(['error' => 'Unauthorized for this employee.'], 403);
        }

        $month = $request->query('month', now()->format('Y-m'));
        $dt = Carbon::parse($month . '-01');
        $startDate = $request->query('start_date', $dt->copy()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', $dt->copy()->endOfMonth()->toDateString());

        $counts = $this->attendanceService->getEmpAttendanceSummary($empId, $startDate, $endDate);

        $emp = DB::table('hr.emps')
            ->leftJoin('cen.units', 'emp_unt_id', '=', 'unt_id')
            ->where('emp_id', $empId)
            ->select('emp_id', 'emp_name', 'emp_title', 'emp_rank', 'emp_cnic', 'unt_name')
            ->first();

        return response()->json([
            'employee'   => $emp,
            'month'      => $month,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'counts'     => $counts,
        ]);
    }

    /**
     * Save cell changes via POST with unit-range authorization and server-side lock checks.
     */
    public function save(AttendanceSaveRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->input('month');
        $payload = $request->getParsedPayload();

        $updated = $this->attendanceService->saveAttendance($user, $month, $payload);

        return redirect()->route('divhr.attendance', ['month' => $month])
            ->with('success', "Attendance saved successfully ({$updated} records updated).");
    }

    /**
     * Single-day drill-down data JSON with remarks.
     */
    public function dayDetails(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $date = $request->query('date', now()->toDateString());
        $details = $this->attendanceService->getAttendanceDayDetails($user, $date);

        return response()->json($details);
    }

    /**
     * Save a per-day remark into hr.attendanceremarks.
     */
    public function saveRemark(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'att_id'  => ['required', 'integer'],
            'day'     => ['required', 'integer', 'between:1,31'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $this->attendanceService->saveDayRemark(
            $user,
            (int) $request->input('att_id'),
            (int) $request->input('day'),
            $request->input('remarks')
        );

        return response()->json(['success' => true]);
    }

    /**
     * Bulk-fill or holiday toggle for a date range within user's unit scope.
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'month'      => ['required', 'date_format:Y-m'],
            'action'     => ['required', 'in:fill,toggle_holiday'],
            'start_day'  => ['required', 'integer', 'between:1,31'],
            'end_day'    => ['required', 'integer', 'between:1,31'],
            'code'       => ['nullable', 'string', 'in:P,W,T,A,L,U,N'],
            'is_holiday' => ['nullable', 'boolean'],
        ]);

        $month = $request->input('month');
        $updated = $this->attendanceService->applyBulkAction($user, $month, $request->all());

        return redirect()->route('divhr.attendance', ['month' => $month])
            ->with('success', "Bulk action completed: {$updated} entries updated.");
    }

    /**
     * Generate attendance sheet for a new month (makeAttendanceSheet).
     */
    public function generateSheet(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->input('month', now()->format('Y-m'));
        $count = $this->attendanceService->makeAttendanceSheet($month);

        return redirect()->route('divhr.attendance', ['month' => $month])
            ->with('success', "Attendance sheet created for {$month} ({$count} new employee rows added).");
    }
}
