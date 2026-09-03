<?php

namespace App\Services;

use App\Models\HrCtrCase;
use App\Models\HrEmployee;
use App\Services\ContractCaseApprovalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EmployeeCreationService
{
    protected ContractCaseApprovalService $approvalService;

    public function __construct(ContractCaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Confirmed department number prefix map from production database.
     */
    public static function getDepartmentMap(): array
    {
        return config('hr_dept_numbers.map', [
            200000 => '11', // Communication Division
            250000 => '12', // Enabling Technology Division
            300000 => '13', // Naval Weapons System Division
            350000 => '14', // Sensors Division
            400000 => '15', // System of Systems Engineering Division
            450000 => '16', // Systems Division
            800000 => '17', // Finance Department
            840000 => '18', // Administration Department
            820000 => '19', // Human Resource Department
            880000 => '21', // Information System Department
        ]);
    }

    /**
     * Look up department number prefix or handle unconfirmed fallback.
     *
     * @param int $unitId
     * @return array ['dept_number' => string, 'is_confirmed' => bool, 'is_fallback' => bool, 'unit_name' => string]
     */
    public function getDepartmentNumber(int $unitId): array
    {
        $map = self::getDepartmentMap();
        $unit = DB::table('cen.units')->where('unt_id', $unitId)->first();
        $unitName = $unit ? $unit->unt_name : "Department #{$unitId}";

        if (isset($map[$unitId])) {
            return [
                'dept_number'  => (string)$map[$unitId],
                'is_confirmed' => true,
                'is_fallback'  => false,
                'unit_name'    => $unitName,
            ];
        }

        // Fallback for unconfirmed departments (e.g. IT 860000, Procurement 810000):
        // Take first 2 digits of unit ID (e.g. 86 or 81) to strictly guarantee emp_id fits in varchar(13)
        $fallbackNumber = substr(str_pad((string)$unitId, 2, '0', STR_PAD_LEFT), 0, 2);

        Log::warning("EmployeeCreationService: Using unconfirmed department number fallback '{$fallbackNumber}' for unit {$unitId} ({$unitName}).");

        return [
            'dept_number'  => $fallbackNumber,
            'is_confirmed' => false,
            'is_fallback'  => true,
            'unit_name'    => $unitName,
        ];
    }

    /**
     * Derive 4-character suffix from CNIC string.
     * Legacy Access formula: Mid(emp_cnic, 11, 3) & Right(emp_cnic, 1)
     *
     * Example: "42201-9387735-9" -> characters 11-13 ("735") + last char ("9") -> "7359"
     *
     * @param string $cnicWithDashes
     * @return string 4-digit suffix
     * @throws InvalidArgumentException
     */
    public function deriveCnicSuffix(string $cnicWithDashes): string
    {
        $raw = trim($cnicWithDashes);

        // Handle formatted CNIC: XXXXX-XXXXXXX-X (15 chars)
        if (preg_match('/^\d{5}-\d{7}-\d{1}$/', $raw)) {
            // 0-indexed positions 10-12 (chars 11-13) and char 14 (15th char)
            $mid = substr($raw, 10, 3);
            $last = substr($raw, -1);
            return $mid . $last;
        }

        // Handle unformatted digits-only CNIC: 13 digits
        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 13) {
            $mid = substr($digits, 9, 3);
            $last = substr($digits, 12, 1);
            return $mid . $last;
        }

        throw new InvalidArgumentException("Invalid CNIC format '{$raw}'. Expected 15 characters with dashes (e.g. 42101-1234567-1).");
    }

    /**
     * Normalize CNIC into standard XXXXX-XXXXXXX-X format with dashes.
     */
    public function normalizeCnic(string $cnic): string
    {
        $raw = trim($cnic);
        if (preg_match('/^\d{5}-\d{7}-\d{1}$/', $raw)) {
            return $raw;
        }

        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 13) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
        }

        throw new InvalidArgumentException("Invalid CNIC length. Must be 13 digits or formatted with dashes (e.g. 42101-1234567-1).");
    }

    /**
     * Generate standard employee ID: {dept_number}-{yy}-{mm}-{suffix}
     * Must never exceed varchar(13).
     *
     * @param int $unitId
     * @param string $joinDate (Y-m-d)
     * @param string $cnic
     * @return array ['emp_id' => string, 'dept_number' => string, 'is_confirmed' => bool, 'is_fallback' => bool]
     */
    public function generateEmpId(int $unitId, string $joinDate, string $cnic): array
    {
        $deptInfo = $this->getDepartmentNumber($unitId);
        $deptNum = str_pad($deptInfo['dept_number'], 2, '0', STR_PAD_LEFT);

        $dt = Carbon::parse($joinDate);
        $yy = $dt->format('y');
        $mm = $dt->format('m');

        $suffix = $this->deriveCnicSuffix($cnic);

        $empId = "{$deptNum}-{$yy}-{$mm}-{$suffix}";

        // Column length guard: hr.emps.emp_id is varchar(13)
        if (strlen($empId) > 13) {
            $empId = substr($empId, 0, 13);
        }

        return [
            'emp_id'       => $empId,
            'dept_number'  => $deptNum,
            'is_confirmed' => $deptInfo['is_confirmed'],
            'is_fallback'  => $deptInfo['is_fallback'],
            'unit_name'    => $deptInfo['unit_name'],
        ];
    }

    /**
     * Backfill attendance records from joining month through current month,
     * ONLY for months where an attendance sheet has already been generated.
     * Mirrors legacy AddInAttendanceSheet subroutine.
     *
     * @param string $empId
     * @param string $empName
     * @param int $unitId
     * @param string $joinDate
     * @return int Number of monthly records backfilled
     */
    public function addInAttendanceSheet(string $empId, string $empName, int $unitId, string $joinDate): int
    {
        $startDate = Carbon::parse($joinDate)->startOfMonth();
        $currentMonth = now()->startOfMonth();
        $cursor = $startDate->copy();
        $backfilledCount = 0;

        while ($cursor <= $currentMonth) {
            $monthStartStr = $cursor->format('Y-m-d');
            $monthEndStr = $cursor->copy()->endOfMonth()->format('Y-m-d');

            // 1. Check if attendance sheet exists for this month
            $templateRow = DB::table('hr.attendance')
                ->where('att_startdt', $monthStartStr)
                ->first();

            if (!$templateRow) {
                // No attendance sheet generated for this month yet — do NOT create new sheet months
                $cursor->addMonth();
                continue;
            }

            // 2. Check if employee already has an attendance record for this month
            $alreadyExists = DB::table('hr.attendance')
                ->where('att_emp_id', $empId)
                ->where('att_startdt', $monthStartStr)
                ->exists();

            if (!$alreadyExists) {
                // 3. Build day columns from template row (copy 'Z' for weekends, 'X' for off days)
                $dayData = [];
                for ($d = 1; $d <= 31; $d++) {
                    $col = "att_{$d}";
                    $val = $templateRow->$col ?? null;
                    $dayData[$col] = in_array($val, ['Z', 'X']) ? $val : null;
                }

                // 4. Determine lock status based on month difference from now
                $diffMonths = $cursor->diffInMonths($currentMonth);
                $locked1 = $diffMonths >= 1;
                $locked2 = $diffMonths >= 2;

                $insertData = array_merge([
                    'att_emp_id'      => $empId,
                    'att_empnamecomp' => $empName,
                    'att_unt_id'      => $unitId,
                    'att_startdt'     => $monthStartStr,
                    'att_enddt'       => $monthEndStr,
                    'att_firstdt'     => now()->format('Y-m-d'),
                    'att_locked1'     => $locked1,
                    'att_locked2'     => $locked2,
                    'att_eahreplace'  => false,
                ], $dayData);

                DB::table('hr.attendance')->insert($insertData);
                $backfilledCount++;
            }

            $cursor->addMonth();
        }

        return $backfilledCount;
    }

    /**
     * Add employee for an Approved Hg Contract Case in a single database transaction.
     *
     * @param HrCtrCase $case
     * @param array $data Contains emp_cnic, emp_joindt, emp_name, emp_unt_id, emp_title, emp_rank
     * @param mixed $user Current authenticated user
     * @return HrEmployee
     * @throws InvalidArgumentException
     */
    public function addEmployeeForContractCase(HrCtrCase $case, array $data, $user): HrEmployee
    {
        $type = strtoupper(trim((string)$case->ctc_type));
        if ($type !== 'HG') {
            throw new InvalidArgumentException("Add Employee flow is only applicable for Hg (Hiring) contract cases.");
        }

        if (!in_array($case->current_stage, ['Approved']) && $case->ctc_status !== 'Approved') {
            throw new InvalidArgumentException("Employee can only be added when the contract case is in 'Approved' stage.");
        }

        if (!empty($case->ctc_emp_id)) {
            throw new InvalidArgumentException("An employee ({$case->ctc_emp_id}) is already linked to this contract case.");
        }

        // Validate mandatory input data
        $cnic = trim((string)($data['emp_cnic'] ?? $case->ctc_cnic));
        if (empty($cnic)) {
            throw new InvalidArgumentException("Please enter CNIC.");
        }

        $joinDate = trim((string)($data['emp_joindt'] ?? ($case->ctc_approvedstartdt ?: $case->ctc_newstartdt)));
        if (empty($joinDate)) {
            throw new InvalidArgumentException("Please enter joining date.");
        }

        $unitId = (int)($data['emp_unt_id'] ?? ($case->ctc_approvedunt_id ?: ($case->ctc_newunt_id ?: $case->ctc_unt_id)));
        if ($unitId <= 0) {
            throw new InvalidArgumentException("Please select department.");
        }

        $name = trim((string)($data['emp_name'] ?? $case->ctc_empnamecomp));
        if (empty($name)) {
            throw new InvalidArgumentException("Please enter employee name.");
        }

        $title = trim((string)($data['emp_title'] ?? ($case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle)));
        $rank = trim((string)($data['emp_rank'] ?? ($case->ctc_approvedgrade ?: $case->ctc_newgrade)));

        // Normalize CNIC and generate emp_id
        $normalizedCnic = $this->normalizeCnic($cnic);
        $empIdInfo = $this->generateEmpId($unitId, $joinDate, $normalizedCnic);
        $empId = $empIdInfo['emp_id'];

        return DB::transaction(function () use ($case, $user, $empId, $normalizedCnic, $name, $unitId, $joinDate, $title, $rank, $empIdInfo) {
            // Check if emp_id or CNIC already exists in hr.emps
            $existingById = DB::table('hr.emps')->where('emp_id', $empId)->first();
            if ($existingById) {
                throw new InvalidArgumentException("Employee ID '{$empId}' already exists in hr.emps.");
            }

            // 1. Insert into hr.emps
            DB::table('hr.emps')->insert([
                'emp_id'      => $empId,
                'emp_cnic'    => $normalizedCnic,
                'emp_name'    => $name,
                'emp_unt_id'  => $unitId,
                'emp_joindt'  => $joinDate,
                'emp_title'   => !empty($title) ? $title : null,
                'emp_rank'    => !empty($rank) ? $rank : null,
                'emp_status'  => 'Active',
                'emp_locked'  => false,
                'emp_cleared' => false,
            ]);

            // 2. Insert two attachment slots into hr.empattachments
            // Slot 1: Appointment Letter
            DB::table('hr.empattachments')->insert([
                'eat_objtype' => 'emp',
                'eat_objid'   => $empId,
                'eat_type'    => 'Appointment Letter',
                'eat_path'    => null,
            ]);

            // Slot 2: Notice
            DB::table('hr.empattachments')->insert([
                'eat_objtype' => 'emp',
                'eat_objid'   => $empId,
                'eat_type'    => 'Notice',
                'eat_path'    => null,
            ]);

            // 3. Insert into fin.empeffheads (salary head)
            $eehExists = DB::table('fin.empeffheads')->where('eeh_emp_id', $empId)->exists();
            if (!$eehExists) {
                DB::table('fin.empeffheads')->insert([
                    'eeh_emp_id' => $empId,
                    'eeh_status' => 'Open',
                    'eeh_dtg'    => now(),
                ]);
            }

            // 4. Attendance backfill from joining month through current month
            $this->addInAttendanceSheet($empId, $name, $unitId, $joinDate);

            // 5. Update contract case with new emp_id
            DB::table('hr.ctrcases')
                ->where('ctc_id', $case->ctc_id)
                ->update([
                    'ctc_emp_id'      => $empId,
                    'ctc_cnic'        => $normalizedCnic,
                    'ctc_empnamecomp' => $name,
                ]);

            // 6. Record audit remark in hr.ctrcaseremarks
            $fallbackNotice = $empIdInfo['is_fallback'] ? " (Unconfirmed Dept #{$unitId} Fallback Used)" : "";
            $this->approvalService->recordRemark(
                $case,
                $user,
                "Employee {$empId} created and linked to contract case by HR{$fallbackNotice}.",
                'Approved'
            );

            $case->refresh();

            return HrEmployee::where('emp_id', $empId)->first();
        });
    }
}
