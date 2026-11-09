<?php

namespace App\Services;

use App\Models\HrAttendance;
use App\Models\HrAttendanceRemark;
use App\Models\User;
use App\Models\CenAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AttendanceService
{
    /**
     * User enterable codes.
     */
    public const USER_CODES = ['P', 'W', 'T', 'A', 'L', 'U', 'N'];

    /**
     * System computed codes (not directly selectable by user).
     */
    public const SYSTEM_CODES = ['Z', 'X'];

    /**
     * Active leave/absence codes subject to holiday absorption.
     */
    public const ABSORPTION_CODES = ['A', 'L', 'U', 'T', 'N'];

    /**
     * Legacy minimum floor date.
     */
    public const FLOOR_DATE = '2021-01-01';

    /**
     * Check if a code is valid for manual entry by user.
     */
    public function isValidUserCode(?string $code): bool
    {
        if ($code === null || $code === '') {
            return true;
        }
        return in_array(strtoupper(trim($code)), self::USER_CODES, true);
    }

    /**
     * Check if user is authorized to view or edit this employee's records.
     */
    public function canUserAccessEmployee(Authenticatable|User|CenAccount $user, string $empId): bool
    {
        $emp = DB::table('hr.emps')->where('emp_id', $empId)->first();
        if (!$emp) {
            return false;
        }

        return $user->canSeeRecord((int)$emp->emp_unt_id);
    }

    /**
     * Check whether the user is authorized to edit/mark attendance for a given employee.
     * Division users can only edit employees in their division.
     * Central/HR users can view all, but can ONLY edit employees in their own department (acc_unt_id).
     */
    public function canUserEditEmployeeAttendance(Authenticatable|User|CenAccount $user, string $empId, ?int $empUnitId = null): bool
    {
        $auth = strtolower(trim((string) ($user->acc_auth ?? '')));
        if (!in_array($auth, ['approver', 'editor'], true) && !empty($auth)) {
            return false;
        }

        if ($empUnitId === null) {
            $emp = DB::table('hr.emps')->where('emp_id', $empId)->first();
            if (!$emp) {
                return false;
            }
            $empUnitId = (int)$emp->emp_unt_id;
        }

        // Edit authority is strictly governed by user's single-scope / own department bounds (acc_lowers .. acc_uppers)
        $lower = (int)$user->acc_lowers;
        $upper = (int)$user->acc_uppers;

        if ($lower === 0 && $upper === 0) {
            $lower = (int)($user->acc_unt_id ?? 0);
            $upper = (int)($user->acc_unt_id ?? 0);
        }

        if ($lower === 0 && $upper === 0) {
            $lower = (int)$user->acc_lowerm;
            $upper = (int)$user->acc_upperm;
        }

        if ($lower === 0 && $upper === 0) {
            return true;
        }

        return $empUnitId >= $lower && $empUnitId <= $upper;
    }

    /**
     * Verify unit scoping on write, abort with 403 if unauthorized.
     */
    public function authorizeEmployeeWrite(Authenticatable|User|CenAccount $user, string $empId): void
    {
        if (!$this->canUserAccessEmployee($user, $empId)) {
            throw new HttpException(403, "Access denied. Employee {$empId} is outside your unit scope.", null, [], 403);
        }

        if (!$this->canUserEditEmployeeAttendance($user, $empId)) {
            throw new HttpException(403, "Access denied. You can only mark or edit attendance for employees in your own department.", null, [], 403);
        }
    }

    /**
     * Get attendance payroll cutoff day from cen.globalvars (attstart_for_pay).
     * Mirrors legacy AttMonthStartDay (Attendance.bas:175-190).
     */
    public function getPayrollCutoffDay(): int
    {
        static $cachedCutoff = null;
        if ($cachedCutoff !== null) {
            return $cachedCutoff;
        }

        $val = DB::table('cen.globalvars')
            ->where('gvar_name', 'attstart_for_pay')
            ->value('gvar_value');

        $day = (int)$val;
        if (in_array($day, [21, 22, 23, 24, 25, 26, 27], true)) {
            $cachedCutoff = $day;
        } else {
            $cachedCutoff = 1;
        }

        return $cachedCutoff;
    }

    /**
     * Cache for resolved paid months per employee.
     */
    protected array $paidMonthsCache = [];

    /**
     * Get all paid months for an employee (including past months covered by subsequent arrears).
     *
     * @param string $empId
     * @return array Array of Y-m strings, e.g. ['2024-08', '2024-09', ...]
     */
    public function getPaidMonthsForEmployee(string $empId): array
    {
        return array_keys($this->getPaidMonthsDetailsForEmployee($empId));
    }

    /**
     * Get full details of all paid months for an employee (direct + arrears-covered).
     *
     * @param string $empId
     * @return array Keyed by 'Y-m', with 'type' ('direct'|'arrears'), 'req_id', 'amount', etc.
     */
    public function getPaidMonthsDetailsForEmployee(string $empId): array
    {
        if (isset($this->paidMonthsCache[$empId])) {
            return $this->paidMonthsCache[$empId];
        }

        $reqs = DB::table('hr.salreqs')
            ->where('srq_emp_id', $empId)
            ->whereIn('srq_status', ['Fulfilled', 'Closed'])
            ->orderBy('srq_month', 'asc')
            ->get();

        $paidMonths = [];

        // Direct month payments (only from regular salary requisitions with gross salary > 0)
        foreach ($reqs as $r) {
            $hasGross = (float)($r->srq_grosalary ?? $r->srq_salary) > 0;
            $isStandaloneArrears = in_array($r->srq_remarks2 ?? '', ['ARREARS_ONLY', 'CLEARANCE'], true) 
                || in_array($r->srq_sudohed ?? '', ['ARREAR', 'CLEARANCE'], true)
                || ((float)($r->srq_grosalary ?? 0) == 0 && (float)($r->srq_arrears ?? 0) > 0);

            if ($hasGross && !$isStandaloneArrears) {
                $mStr = Carbon::parse($r->srq_month)->format('Y-m');
                $paidMonths[$mStr] = [
                    'type'    => 'direct',
                    'amount'  => (float)$r->srq_salary,
                    'req_id'  => $r->srq_id,
                    'status'  => $r->srq_status,
                    'month'   => $mStr,
                ];
            }
        }

        $emp = DB::table('hr.emps')->where('emp_id', $empId)->first();
        $joinDate = $emp && !empty($emp->emp_joindt) ? Carbon::parse($emp->emp_joindt)->startOfMonth() : null;

        // Resolve months covered by subsequent arrears payments
        foreach ($reqs as $r) {
            $arrears = (float)($r->srq_arrears ?? 0);
            if ($arrears <= 0) continue;

            $cur = Carbon::parse($r->srq_month)->subMonth()->startOfMonth();
            $remArrears = $arrears;
            $ctrSal = (float)($r->srq_ctrsalary ?: $remArrears);

            while ($remArrears > 0) {
                if ($joinDate && $cur->lt($joinDate)) {
                    break;
                }
                $mStr = $cur->format('Y-m');
                if (!isset($paidMonths[$mStr])) {
                    $slice = min($remArrears, $ctrSal > 0 ? $ctrSal : $remArrears);
                    $paidMonths[$mStr] = [
                        'type'    => 'arrears',
                        'amount'  => $slice,
                        'paid_in' => $r->srq_month,
                        'req_id'  => $r->srq_id,
                        'status'  => 'Fulfilled',
                        'month'   => $mStr,
                    ];
                    $remArrears -= $slice;
                }
                $cur->subMonth();
            }
        }

        ksort($paidMonths);
        $this->paidMonthsCache[$empId] = $paidMonths;
        return $paidMonths;
    }

    /**
     * Check if a specific cell/day is locked split by payroll cutoff day.
     * Mirrors legacy PrepareAttendanceSheet (hr_attendance_u.bas:67-74) with dynamic payroll cycle logic:
     *   - Day 1 to Cutoff-1 (e.g. 1..25): Belongs to CURRENT month's salary.
     *     Locked ONLY if current month's salary is Fulfilled/Closed (or covered by arrears) AND att_locked1 = true.
     *   - Day Cutoff to End (e.g. 26..31): Belongs to NEXT month's salary.
     *     Locked ONLY if NEXT month's salary is Fulfilled/Closed (or covered by arrears) AND att_locked2 = true.
     * Unpaid / newly added employees remain open for editing so HR can record working days.
     */
    public function isCellLocked(HrAttendance $row, int $day): bool
    {
        $cutoff = $this->getPayrollCutoffDay();
        $empId = (string) $row->att_emp_id;

        // Daily Date-Time Lock Rules:
        // 1. Previous days: Strictly LOCKED across the board. ONLY specifically unlocked if Finance has unlocked this day.
        // 2. Today: Open during the day, auto-locks at 5:00 PM (17:00).
        // 3. Future days: Strictly LOCKED (cannot mark future attendance).
        $cellDate = $row->att_startdt ? Carbon::parse($row->att_startdt)->setDay($day) : null;
        if ($cellDate) {
            $today = Carbon::today();
            $isPast5pm = Carbon::now()->format('H:i') >= '17:00';

            // Check if explicit unlock exists in dedicated hr.attendance_unlocks table
            $hasUnlockRemark = DB::table('hr.attendance_unlocks')
                ->where('aul_att_id', $row->att_id)
                ->where('aul_day', $day)
                ->where('aul_is_active', true)
                ->exists();

            // Fallback check for legacy remarks with [FINANCE_UNLOCKED]
            if (!$hasUnlockRemark) {
                $hasUnlockRemark = DB::table('hr.attendanceremarks')
                    ->where('atr_att_id', $row->att_id)
                    ->where('atr_attday', $day)
                    ->where('atr_remarks', 'like', '%[FINANCE_UNLOCKED]%')
                    ->exists();
            }

            if ($cellDate->equalTo($today)) {
                // Today: open during day, auto-lock after 17:00 (5:00 PM) unless Finance unlocked
                if ($isPast5pm) {
                    if (!$hasUnlockRemark) {
                        return true;
                    }
                } else {
                    return false; // Open today before 5:00 PM!
                }
            } elseif ($cellDate->lessThan($today)) {
                // ALL PREVIOUS DAYS ARE STRICTLY LOCKED!
                // Only specifically unlocked if Finance has unlocked this day
                if ($hasUnlockRemark) {
                    return false;
                }
                return true;
            } elseif ($cellDate->greaterThan($today)) {
                // Future days are locked
                return true;
            }
        }

        if ($day < $cutoff) {
            $currentMonth = $row->att_enddt ? Carbon::parse($row->att_enddt)->format('Y-m') : null;
            if ($currentMonth) {
                if (!in_array($currentMonth, $paidMonths, true)) {
                    return false;
                }
            }

            return (bool)($row->att_locked1 ?? true);
        } else {
            $nextMonth = $row->att_startdt 
                ? Carbon::parse($row->att_startdt)->addMonth()->format('Y-m') 
                : null;
            if ($nextMonth) {
                if (!in_array($nextMonth, $paidMonths, true)) {
                    return false;
                }
            }

            return (bool)($row->att_locked2 ?? true);
        }
    }

    /**
     * Check if an attendance record or specific day is locked against edits.
     */
    public function isRecordLocked(HrAttendance $row, ?int $day = null): bool
    {
        if ($day !== null) {
            return $this->isCellLocked($row, $day);
        }

        $daysInMonth = $row->att_enddt ? (int)Carbon::parse($row->att_enddt)->format('d') : 30;
        return $this->isCellLocked($row, 1) && $this->isCellLocked($row, $daysInMonth);
    }

    /**
     * Create attendance sheet for a given calendar month (YYYY-MM).
     * Mirrors legacy makeAttendanceSheet (Attendance.bas:5-53).
     */
    public function makeAttendanceSheet(string $month): int
    {
        $first = Carbon::parse($month . '-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $daysInMonth = (int) $first->daysInMonth;
        $currentMonth = now()->startOfMonth();

        // Lock status based on month age
        $diffMonths = $first->diffInMonths($currentMonth);
        $locked1 = $first < $currentMonth && $diffMonths >= 1;
        $locked2 = $first < $currentMonth && $diffMonths >= 2;

        // Build base day template: Z for weekends, X for invalid days
        $dayTemplate = [];
        for ($d = 1; $d <= 31; $d++) {
            $col = 'att_' . $d;
            if ($d > $daysInMonth) {
                $dayTemplate[$col] = 'X'; // Invalid day
            } else {
                $curDate = $first->copy()->addDays($d - 1);
                $dayTemplate[$col] = $curDate->isWeekend() ? 'Z' : null;
            }
        }

        // Active employees or employees with active contracts
        $activeEmployees = DB::table('hr.emps as e')
            ->where(function ($q) {
                $q->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
                  ->orWhereExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('hr.contracts as c')
                          ->whereColumn('c.ctr_num', 'e.emp_id')
                          ->whereRaw('c.ctr_enddt >= ?', [now()->toDateString()]);
                  });
            })
            ->select('e.emp_id', 'e.emp_name', 'e.emp_title', 'e.emp_rank', 'e.emp_unt_id')
            ->get();

        $createdCount = 0;
        foreach ($activeEmployees as $emp) {
            $exists = DB::table('hr.attendance')
                ->where('att_emp_id', (string)$emp->emp_id)
                ->where('att_startdt', $first->toDateString())
                ->exists();

            if (!$exists) {
                $compName = trim("{$emp->emp_name} {$emp->emp_title} {$emp->emp_rank}");
                $insertData = array_merge([
                    'att_emp_id'      => (string)$emp->emp_id,
                    'att_empnamecomp' => $compName ?: $emp->emp_name,
                    'att_unt_id'      => $emp->emp_unt_id,
                    'att_startdt'     => $first->toDateString(),
                    'att_enddt'       => $last->toDateString(),
                    'att_firstdt'     => now()->toDateString(),
                    'att_locked1'     => $locked1,
                    'att_locked2'     => $locked2,
                    'att_eahreplace'  => false,
                ], $dayTemplate);

                DB::table('hr.attendance')->insert($insertData);
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Retroactively backfill attendance for a single employee from join date through current month.
     * Mirrors legacy AddInAttendanceSheet (Attendance.bas:62-108).
     */
    public function addInAttendanceSheet(string $empId, string $empName, int $unitId, string $joinDate): int
    {
        $floorDate = Carbon::parse(self::FLOOR_DATE);
        $startDate = Carbon::parse($joinDate)->startOfMonth();
        if ($startDate < $floorDate) {
            $startDate = $floorDate->copy();
        }

        $currentMonth = now()->startOfMonth();
        $cursor = $startDate->copy();
        $backfilledCount = 0;

        while ($cursor <= $currentMonth) {
            $monthStartStr = $cursor->format('Y-m-d');
            $monthEndStr = $cursor->copy()->endOfMonth()->format('Y-m-d');

            // 1. Check if sheet exists for this month
            $templateRow = DB::table('hr.attendance')
                ->where('att_startdt', $monthStartStr)
                ->first();

            if (!$templateRow) {
                $cursor->addMonth();
                continue;
            }

            // 2. Check if employee already has row
            $alreadyExists = DB::table('hr.attendance')
                ->where('att_emp_id', $empId)
                ->where('att_startdt', $monthStartStr)
                ->exists();

            if (!$alreadyExists) {
                $dayData = [];
                $joinDt = Carbon::parse($joinDate);
                $isJoiningMonth = ($cursor->format('Y-m') === $joinDt->format('Y-m'));
                $joinDayOfMonth = (int) $joinDt->format('j');
                $daysInThisMonth = (int) $cursor->copy()->endOfMonth()->format('j');

                for ($d = 1; $d <= 31; $d++) {
                    $col = "att_{$d}";
                    $val = $templateRow->$col ?? null;

                    if (in_array($val, ['Z', 'X'], true)) {
                        $dayData[$col] = $val;
                    } elseif ($isJoiningMonth && $d < $joinDayOfMonth && $d <= $daysInThisMonth) {
                        // Pre-joining days: mark as 'N' (Not Applicable)
                        $dayDt = $cursor->copy()->addDays($d - 1);
                        $dayData[$col] = $dayDt->isWeekend() ? 'Z' : 'N';
                    } else {
                        $dayData[$col] = null;
                    }
                }

                // Dynamic logic: Pre-cutoff (Days 1..25) locked ONLY if current REGULAR salary fulfilled (gross > 0).
                // Standalone Arrears / Clearance payouts do NOT lock daily attendance!
                $hasFulfilledCurrent = DB::table('hr.salreqs')
                    ->where('srq_emp_id', $empId)
                    ->where('srq_month', $monthEndStr)
                    ->whereIn('srq_status', ['Fulfilled', 'Closed'])
                    ->where('srq_grosalary', '>', 0)
                    ->where(function($q) {
                        $q->whereNull('srq_remarks2')
                          ->orWhereNotIn('srq_remarks2', ['ARREARS_ONLY', 'CLEARANCE']);
                    })
                    ->exists();

                $nextMonthEndStr = $cursor->copy()->addMonth()->endOfMonth()->toDateString();
                $hasFulfilledNext = DB::table('hr.salreqs')
                    ->where('srq_emp_id', $empId)
                    ->where('srq_month', $nextMonthEndStr)
                    ->whereIn('srq_status', ['Fulfilled', 'Closed'])
                    ->where('srq_grosalary', '>', 0)
                    ->where(function($q) {
                        $q->whereNull('srq_remarks2')
                          ->orWhereNotIn('srq_remarks2', ['ARREARS_ONLY', 'CLEARANCE']);
                    })
                    ->exists();

                $locked1 = $hasFulfilledCurrent;
                $locked2 = $hasFulfilledNext;

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
     * Save attendance cell changes with strict unit-range and server-side lock checks.
     *
     * @param User $user
     * @param string $month YYYY-MM
     * @param array $payload Array of ['emp_id' => string, 'day' => int, 'val' => string]
     * @return int Number of updated rows
     * @throws HttpException On 403 authorization or 422 lock violations
     */
    public function saveAttendance(Authenticatable|User|CenAccount $user, string $month, array $payload): int
    {
        $first = Carbon::parse($month . '-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $daysInMonth = (int) $first->daysInMonth;

        // Group changes by employee
        $byEmp = [];
        foreach ($payload as $item) {
            $eid = (string) ($item['emp_id'] ?? '');
            $day = (int) ($item['day'] ?? 0);
            $val = strtoupper(trim((string) ($item['val'] ?? '')));

            if ($eid === '' || $day < 1 || $day > $daysInMonth) {
                continue;
            }

            // Reject invalid codes or system codes (Z, X) from manual entry
            if (!$this->isValidUserCode($val)) {
                throw new HttpException(422, "Invalid attendance code '{$val}'. Codes 'Z' and 'X' are system-assigned only.", null, [], 422);
            }

            if (!isset($byEmp[$eid])) {
                $byEmp[$eid] = [];
            }
            $byEmp[$eid][$day] = ($val === '' ? null : substr($val, 0, 1));
        }

        if (empty($byEmp)) {
            return 0;
        }

        $unitId = $user->acc_unt_id ?? ($user->unit->unt_id ?? null);
        $updatedRowsCount = 0;

        DB::transaction(function () use ($user, $first, $last, $byEmp, $unitId, &$updatedRowsCount) {
            foreach ($byEmp as $eid => $changes) {
                // 1. Authorize write access against user's unit bounds (Security Gap Fix)
                $this->authorizeEmployeeWrite($user, $eid);

                // 2. Fetch or create attendance row
                $row = HrAttendance::where('att_emp_id', (string)$eid)
                    ->where('att_startdt', $first->toDateString())
                    ->where('att_enddt', $last->toDateString())
                    ->first();

                if (!$row) {
                    $emp = DB::table('hr.emps')->where('emp_id', $eid)->first();
                    if (!$emp) {
                        continue;
                    }

                    $row = new HrAttendance();
                    $row->att_emp_id = (string)$eid;
                    $row->att_empnamecomp = $emp->emp_name;
                    $row->att_unt_id = $unitId ?? $emp->emp_unt_id;
                    $row->att_startdt = $first->toDateString();
                    $row->att_enddt = $last->toDateString();
                    $row->att_firstdt = now()->toDateString();
                    $row->att_locked1 = false;
                    $row->att_locked2 = false;
                    $row->att_eahreplace = false;
                }

                // 3. Server-side per-cell lock enforcement (hr_attendance_u.bas:67-74)
                $cutoff = $this->getPayrollCutoffDay();
                foreach ($changes as $d => $v) {
                    $col = 'att_' . $d;
                    $dayDt = $first->copy()->addDays((int)$d - 1);
                    $isWeekend = $dayDt->isWeekend();
                    $isHoliday = ($row->$col ?? null) === 'Z';

                    // Weekend or Holiday cells can never be modified by users
                    if ($isWeekend || $isHoliday) {
                        throw new HttpException(422, "Day {$d} ({$dayDt->format('D, d M')}) is a weekend or holiday and cannot be modified.", null, [], 422);
                    }

                    if ($this->isCellLocked($row, (int)$d)) {
                        $lockType = (int)$d < $cutoff ? 'locked1 (pre-cutoff)' : 'locked2 (post-cutoff)';
                        throw new HttpException(422, "Day {$d} for employee {$eid} ({$first->format('M Y')}) is locked by {$lockType} and cannot be modified.", null, [], 422);
                    }
                }

                // 4. Apply updates
                foreach ($changes as $d => $v) {
                    $col = 'att_' . $d;
                    $dayDt = $first->copy()->addDays((int)$d - 1);
                    // Do not allow overwriting 'X' (invalid date) or weekends / 'Z'
                    if (in_array($row->$col ?? null, ['X', 'Z'], true) || $dayDt->isWeekend()) {
                        continue;
                    }
                    $row->$col = $v;
                }

                $row->save();
                $updatedRowsCount++;
            }
        });

        return $updatedRowsCount;
    }

    /**
     * Get attendance grid data bounded by user's unit range and mode.
     */
    public function getAttendanceGrid(Authenticatable|User|CenAccount $user, string $month, string $mode = 'm'): array
    {
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isCentral = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs', 'it', 'is', 'adm', 'proc', 'prc'], true);

        if (method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode()) {
            $unitId = (int) ($user->acc_unt_id ?? 0);
            $lowers = (int) ($user->acc_lowers ?? 0);
            $uppers = (int) ($user->acc_uppers ?? 0);
            $lower = $lowers > 0 ? $lowers : $unitId;
            $upper = ($uppers > 0 && $uppers >= $lowers) ? $uppers : $unitId;
        } elseif ($mode === 's' || !$isCentral) {
            $lower = (int)$user->acc_lowers === 0 ? (int)$user->acc_lowerm : (int)$user->acc_lowers;
            $upper = (int)$user->acc_lowers === 0 ? (int)$user->acc_upperm : (int)$user->acc_uppers;
        } else {
            $lower = 0;
            $upper = 99999999;
        }

        $first = Carbon::parse($month . '-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $nextMonthEnd = $first->copy()->addMonth()->endOfMonth()->toDateString();
        $days = (int) $first->daysInMonth;

        $rows = collect(DB::select("
            SELECT e.emp_id, e.emp_name, e.emp_unt_id, e.emp_joindt,
                   a.att_id, a.att_emp_id, a.att_empnamecomp, a.att_unt_id,
                   a.att_startdt, a.att_enddt, a.att_locked1, a.att_locked2,
                   EXISTS (
                       SELECT 1 FROM hr.salreqs srq 
                       WHERE srq.srq_emp_id::text = e.emp_id::text 
                         AND srq.srq_month = ?::date
                         AND srq.srq_status IN ('Fulfilled', 'Closed')
                         AND COALESCE(srq.srq_grosalary, srq.srq_salary, 0) > 0
                         AND (srq.srq_remarks2 IS NULL OR srq.srq_remarks2 NOT IN ('ARREARS_ONLY', 'CLEARANCE'))
                   ) AS is_salary_fulfilled,
                   EXISTS (
                       SELECT 1 FROM hr.salreqs srq2 
                       WHERE srq2.srq_emp_id::text = e.emp_id::text 
                         AND srq2.srq_month = ?::date
                         AND srq2.srq_status IN ('Fulfilled', 'Closed')
                         AND COALESCE(srq2.srq_grosalary, srq2.srq_salary, 0) > 0
                         AND (srq2.srq_remarks2 IS NULL OR srq2.srq_remarks2 NOT IN ('ARREARS_ONLY', 'CLEARANCE'))
                   ) AS is_next_salary_fulfilled,
                   a.att_1, a.att_2, a.att_3, a.att_4, a.att_5, a.att_6, a.att_7, a.att_8, a.att_9, a.att_10,
                   a.att_11, a.att_12, a.att_13, a.att_14, a.att_15, a.att_16, a.att_17, a.att_18, a.att_19, a.att_20,
                   a.att_21, a.att_22, a.att_23, a.att_24, a.att_25, a.att_26, a.att_27, a.att_28, a.att_29, a.att_30, a.att_31
            FROM hr.emps e
            LEFT JOIN LATERAL (
                SELECT *
                FROM hr.attendance a
                WHERE a.att_emp_id = e.emp_id::text
                  AND a.att_startdt <= ?::date
                  AND a.att_enddt >= ?::date
                ORDER BY a.att_startdt DESC, a.att_id DESC
                LIMIT 1
            ) a ON TRUE
            WHERE e.emp_unt_id BETWEEN ? AND ?
              AND LOWER(e.emp_status) IN ('active', 'current')
            ORDER BY e.emp_name ASC
        ", [$last->toDateString(), $nextMonthEnd, $last->toDateString(), $first->toDateString(), $lower, $upper]));

        // Filter: only show employees whose joining date falls within or before this month
        $rows = $rows->filter(function ($r) use ($last) {
            if (empty($r->emp_joindt)) {
                return true; // No join date = always show
            }
            $joinMonth = Carbon::parse($r->emp_joindt)->startOfMonth();
            $gridMonth = Carbon::parse($last)->startOfMonth();
            return $joinMonth->lte($gridMonth);
        });

        $cutoffDay = $this->getPayrollCutoffDay();

        $attIds = $rows->pluck('att_id')->filter()->unique()->toArray();
        $financeUnlockedDays = [];
        if (!empty($attIds)) {
            // 1. Dedicated hr.attendance_unlocks records
            $unlockRows = DB::table('hr.attendance_unlocks')
                ->whereIn('aul_att_id', $attIds)
                ->where('aul_is_active', true)
                ->get(['aul_att_id', 'aul_day']);
            foreach ($unlockRows as $uRow) {
                $financeUnlockedDays[$uRow->aul_att_id][$uRow->aul_day] = true;
            }

            // 2. Legacy fallback from hr.attendanceremarks
            $legacyUnlockRows = DB::table('hr.attendanceremarks')
                ->whereIn('atr_att_id', $attIds)
                ->where('atr_remarks', 'like', '%[FINANCE_UNLOCKED]%')
                ->get(['atr_att_id', 'atr_attday']);
            foreach ($legacyUnlockRows as $uRow) {
                $financeUnlockedDays[$uRow->atr_att_id][$uRow->atr_attday] = true;
            }
        }

        $data = $rows->map(function ($r) use ($days, $cutoffDay, $first, $user, $financeUnlockedDays) {
            $vals = [];
            $lockedDays = [];
            $isCurrentPaid = (bool)($r->is_salary_fulfilled ?? false);
            $isNextPaid = (bool)($r->is_next_salary_fulfilled ?? false);

            // Determine joining day within this month (for pre-joining 'N' marking)
            $joinDayInMonth = null;
            if (!empty($r->emp_joindt)) {
                $joinDt = Carbon::parse($r->emp_joindt);
                $monthStart = $first->copy()->startOfMonth();
                $monthEnd = $first->copy()->endOfMonth();
                // Only apply if joining date falls within this specific month
                if ($joinDt->between($monthStart, $monthEnd)) {
                    $joinDayInMonth = (int) $joinDt->format('j'); // day of month (1-31)
                }
            }

            for ($d = 1; $d <= 31; $d++) {
                $col = 'att_' . $d;
                $rawVal = isset($r->$col) && $r->$col !== null ? strtoupper(trim($r->$col)) : null;
                $isWeekend = false;
                $cellDate = null;
                if ($d <= $days) {
                    $cellDate = $first->copy()->addDays($d - 1);
                    $isWeekend = $cellDate->isWeekend(); // Saturday or Sunday
                }

                // Pre-joining days: mark as 'N' (Not Applicable) and lock them
                $isPreJoining = ($joinDayInMonth !== null && $d < $joinDayInMonth && $d <= $days);

                if ($isPreJoining && !$isWeekend) {
                    $val = 'N';
                } elseif ($isWeekend) {
                    // If Saturday or Sunday, ALWAYS treat as 'Z' (weekend)
                    $val = 'Z';
                } else {
                    $val = $rawVal;
                }
                $vals[$d] = $val;

                // Per-cell locking: Cutoff locking applies per payroll cycle!
                // Pre-cutoff (Days 1..Cutoff-1): Locked if CURRENT month's salary is fulfilled AND att_locked1 = true
                // Post-cutoff (Days Cutoff..End): Locked if NEXT month's salary is fulfilled AND att_locked2 = true
                $isPreCutoffLocked = ($d < $cutoffDay) && $isCurrentPaid && (bool)($r->att_locked1 ?? true);
                $isPostCutoffLocked = ($d >= $cutoffDay) && $isNextPaid && (bool)($r->att_locked2 ?? true);
                $isCutoffLocked = $isPreCutoffLocked || $isPostCutoffLocked;

                // Date-Time & Finance Unlock Rules:
                $today = Carbon::today();
                $isPast5pm = Carbon::now()->format('H:i') >= '17:00';
                $isFinUnlocked = !empty($r->att_id) && !empty($financeUnlockedDays[$r->att_id][$d]);
                $isBasicLocked = $isWeekend || $val === 'Z' || $val === 'X' || $isPreJoining;

                if ($isBasicLocked || $cellDate === null) {
                    $cellLocked = true;
                } elseif ($cellDate->lt($today)) {
                    // ALL PREVIOUS DAYS ARE STRICTLY LOCKED!
                    // Only specifically unlocked if Finance has unlocked this day
                    $cellLocked = !$isFinUnlocked;
                } elseif ($cellDate->eq($today)) {
                    // TODAY: Open during daytime, auto-locks at 5:00 PM (17:00)
                    if ($isPast5pm) {
                        $cellLocked = !$isFinUnlocked;
                    } else {
                        $cellLocked = false; // Open today before 5:00 PM!
                    }
                } else {
                    // FUTURE DAYS: Locked (cannot mark future attendance)
                    $cellLocked = true;
                }

                if ($isCutoffLocked) {
                    $cellLocked = true;
                }

                $lockedDays[$d] = $cellLocked;
            }

            // Count present days: ONLY explicit 'P' counts as present (empty/null does NOT count)
            $present = 0;
            $unmarked = 0;
            for ($d = 1; $d <= $days; $d++) {
                $v = $vals[$d] ?? '';
                if ($v === 'P') {
                    $present++;
                }
                // Count unmarked working days (not weekend, not holiday, not X, not N, but empty)
                if (($v === '' || $v === null) && !($lockedDays[$d] ?? false)) {
                    $dayDt = $first->copy()->addDays($d - 1);
                    if (!$dayDt->isWeekend()) {
                        $unmarked++;
                    }
                }
            }

            return [
                'emp_id'      => $r->emp_id,
                'name'        => $r->emp_name,
                'att_id'      => $r->att_id,
                'unit_id'     => $r->emp_unt_id,
                'can_edit'    => $this->canUserEditEmployeeAttendance($user, $r->emp_id, (int)$r->emp_unt_id),
                'locked'      => ($isCurrentPaid && (bool)($r->att_locked1 ?? false)) && ($isNextPaid && (bool)($r->att_locked2 ?? false)),
                'locked1'     => $isCurrentPaid && (bool) ($r->att_locked1 ?? false),
                'locked2'     => $isNextPaid && (bool) ($r->att_locked2 ?? false),
                'locked_days' => $lockedDays,
                'vals'        => $vals,
                'present'     => $present,
                'unmarked'    => $unmarked,
                'percent'     => $days > 0 ? round($present * 100 / $days) : 0,
            ];
        });

        $weekdays = [];
        for ($d = 1; $d <= $days; $d++) {
            $weekdays[$d] = $first->copy()->addDays($d - 1)->format('D');
        }

        return [
            'mode'       => $mode,
            'month'      => $first->format('Y-m'),
            'first'      => $first->toDateString(),
            'last'       => $last->toDateString(),
            'days'       => $days,
            'list'       => $data,
            'weekdays'   => $weekdays,
            'cutoff_day' => $cutoffDay,
            'is_locked'  => $rows->isNotEmpty() && (bool)($rows->first()->att_locked1 && $rows->first()->att_locked2),
        ];
    }

    /**
     * Get attendance details for a single day across authorized employees.
     * Mirrors legacy hr_attendance_u_oneday (Attendance.bas:218-320).
     */
    public function getAttendanceDayDetails(Authenticatable|User|CenAccount $user, string $date): array
    {
        $dt = Carbon::parse($date);
        $day = (int) $dt->format('j');
        $monthStart = $dt->copy()->startOfMonth()->toDateString();
        $monthEnd = $dt->copy()->endOfMonth()->toDateString();
        $grid = $this->getAttendanceGrid($user, $dt->format('Y-m'), 's');

        $attIds = array_filter(array_column($grid['list']->toArray(), 'att_id'));
        $remarks = [];
        if (!empty($attIds)) {
            $remarkRows = DB::table('hr.attendanceremarks')
                ->whereIn('atr_att_id', $attIds)
                ->where('atr_attday', $day)
                ->get();
            foreach ($remarkRows as $rm) {
                $remarks[$rm->atr_att_id] = $rm->atr_remarks;
            }
        }

        $list = [];
        foreach ($grid['list'] as $emp) {
            $code = $emp['vals'][$day] ?? '';
            $attId = $emp['att_id'];
            $list[] = [
                'emp_id'    => $emp['emp_id'],
                'name'      => $emp['name'],
                'att_id'    => $attId,
                'code'      => $code,
                'remarks'   => $attId ? ($remarks[$attId] ?? '') : '',
                'is_locked' => $emp['locked'],
            ];
        }

        return [
            'date'      => $dt->toDateString(),
            'day'       => $day,
            'weekday'   => $dt->format('l'),
            'month'     => $dt->format('Y-m'),
            'list'      => $list,
        ];
    }

    /**
     * Save daily remark into hr.attendanceremarks.
     */
    public function saveDayRemark(Authenticatable|User|CenAccount $user, int $attId, int $day, ?string $remark): void
    {
        $att = HrAttendance::findOrFail($attId);
        $this->authorizeEmployeeWrite($user, $att->att_emp_id);

        if ($this->isCellLocked($att, $day)) {
            throw new HttpException(422, "Cannot edit remarks on day {$day} of a locked attendance period.", null, [], 422);
        }

        $remark = trim((string)$remark);
        if ($remark === '') {
            DB::table('hr.attendanceremarks')
                ->where('atr_att_id', $attId)
                ->where('atr_attday', $day)
                ->delete();
        } else {
            DB::table('hr.attendanceremarks')->updateOrInsert(
                ['atr_att_id' => $attId, 'atr_attday' => $day],
                ['atr_remarks' => $remark]
            );
        }
    }

    /**
     * Bulk-fill attendance code across date range or toggle holiday across employees in unit.
     */
    public function applyBulkAction(Authenticatable|User|CenAccount $user, string $month, array $params): int
    {
        $action = $params['action'] ?? 'fill';
        $startDay = (int) ($params['start_day'] ?? 1);
        $endDay = (int) ($params['end_day'] ?? $startDay);
        $code = strtoupper(trim((string) ($params['code'] ?? '')));
        $first = Carbon::parse($month . '-01')->startOfMonth();
        $daysInMonth = (int) $first->daysInMonth;

        if ($startDay < 1 || $endDay > $daysInMonth || $startDay > $endDay) {
            throw new HttpException(422, 'Invalid day range.', null, [], 422);
        }

        if ($action !== 'toggle_holiday') {
            if (!$this->isValidUserCode($code)) {
                throw new HttpException(422, "Invalid attendance code '{$code}'. Codes 'Z' and 'X' are system-assigned only.", null, [], 422);
            }
        }

        $grid = $this->getAttendanceGrid($user, $month, 's');
        $payload = [];

        $cutoff = $this->getPayrollCutoffDay();
        foreach ($grid['list'] as $emp) {
            if (empty($emp['can_edit'])) {
                continue;
            }
            for ($d = $startDay; $d <= $endDay; $d++) {
                $dayDt = $first->copy()->addDays($d - 1);
                $isWeekend = $dayDt->isWeekend();

                // Saturday and Sunday are permanently weekends and cannot be modified or toggled
                if ($isWeekend) {
                    continue;
                }

                if ($action === 'toggle_holiday') {
                    // Check cutoff lock for holiday toggle
                    $isCutoffLocked = ($d < $cutoff && !empty($emp['locked1'])) ||
                                      ($d >= $cutoff && !empty($emp['locked2']));
                    if ($isCutoffLocked) {
                        continue;
                    }

                    $isHol = !empty($params['is_holiday']);
                    $curCode = $emp['vals'][$d] ?? '';
                    $newVal = $isHol ? 'Z' : ($curCode === 'Z' ? '' : $curCode);
                    $payload[] = ['emp_id' => $emp['emp_id'], 'day' => $d, 'val' => $newVal];
                } else {
                    // Skip if locked or already a public holiday Z
                    if (!empty($emp['locked_days'][$d]) || ($emp['vals'][$d] ?? '') === 'Z') {
                        continue;
                    }
                    // Bulk fill user code
                    $payload[] = ['emp_id' => $emp['emp_id'], 'day' => $d, 'val' => $code];
                }
            }
        }

        if (empty($payload)) {
            return 0;
        }

        // Internal handler allowing 'Z' if toggle_holiday
        $firstDate = $first->toDateString();
        $lastDate = $first->copy()->endOfMonth()->toDateString();
        $updated = 0;

        DB::transaction(function () use ($payload, $firstDate, $lastDate, $cutoff, &$updated) {
            foreach ($payload as $item) {
                $eid = $item['emp_id'];
                $day = (int)$item['day'];
                $val = $item['val'] === '' ? null : $item['val'];

                $col = 'att_' . $day;
                $affected = DB::table('hr.attendance')
                    ->where('att_emp_id', $eid)
                    ->where('att_startdt', $firstDate)
                    ->where('att_enddt', $lastDate)
                    ->where(function ($q) use ($day, $cutoff) {
                        if ($day < $cutoff) {
                            $q->where('att_locked1', false);
                        } else {
                            $q->where('att_locked2', false);
                        }
                    })
                    ->where(function ($q) use ($col) {
                        $q->whereNull($col)->orWhere($col, '<>', 'X');
                    })
                    ->update([$col => $val]);
                $updated += $affected;
            }
        });

        return $updated;
    }

    /**
     * Get attendance summary with full legacy cross-month boundary stitching.
     * Ports Attendance.bas:400-478 faithfully.
     *
     * @param string $empId
     * @param string $startDate YYYY-MM-DD
     * @param string $endDate   YYYY-MM-DD
     * @return array
     */
    public function getEmpAttendanceSummary(string $empId, string $startDate, string $endDate): array
    {
        $dStart = new \DateTime($startDate);
        $dEnd = new \DateTime($endDate);

        // Fetch attendance records covering [startDate, endDate]
        $records = DB::table('hr.attendance')
            ->where('att_emp_id', $empId)
            ->where('att_startdt', '<=', $endDate)
            ->where('att_enddt', '>=', $startDate)
            ->get();

        $daysMap = [];
        foreach ($records as $r) {
            $rStart = new \DateTime($r->att_startdt);
            $rEnd = new \DateTime($r->att_enddt);

            $cur = clone $rStart;
            while ($cur <= $rEnd) {
                $dayNum = (int)$cur->format('j');
                $col = 'att_' . $dayNum;
                $daysMap[$cur->format('Y-m-d')] = $r->$col ?? 'Q';
                $cur->modify('+1 day');
            }
        }

        // Apply leaves override from hr.leaves
        $leaves = DB::table('hr.leaves')
            ->where('lve_emp_id', $empId)
            ->where('lve_from', '<=', $endDate)
            ->where('lve_to', '>=', $startDate)
            ->get();

        foreach ($leaves as $l) {
            $lFrom = new \DateTime($l->lve_from);
            $lTo = new \DateTime($l->lve_to);
            $cur = clone $lFrom;
            while ($cur <= $lTo) {
                $daysMap[$cur->format('Y-m-d')] = $l->lve_type;
                $cur->modify('+1 day');
            }
        }

        // Main target window array
        $arr = [];
        $cur = clone $dStart;
        while ($cur <= $dEnd) {
            $dayStr = $cur->format('Y-m-d');
            $arr[] = [
                'date' => $dayStr,
                'char' => $daysMap[$dayStr] ?? 'Q',
            ];
            $cur->modify('+1 day');
        }

        // ====================================================================
        // LEGACY CROSS-MONTH BOUNDARY STITCHING (Attendance.bas:400-478)
        // ====================================================================
        $prefixArr = [];
        $suffixArr = [];

        // Check if window starts with one or more 'Z' days followed by active char
        $len = count($arr);
        $needsPrefix = false;
        if ($len > 0 && $arr[0]['char'] === 'Z') {
            $k = 0;
            while ($k < $len && $arr[$k]['char'] === 'Z') {
                $k++;
            }
            if ($k < $len && in_array($arr[$k]['char'], self::ABSORPTION_CODES, true)) {
                $needsPrefix = true;
            }
        }

        // Check if window ends with active char followed by one or more 'Z' days
        $needsSuffix = false;
        if ($len > 0 && $arr[$len - 1]['char'] === 'Z') {
            $k = $len - 1;
            while ($k >= 0 && $arr[$k]['char'] === 'Z') {
                $k--;
            }
            if ($k >= 0 && in_array($arr[$k]['char'], self::ABSORPTION_CODES, true)) {
                $needsSuffix = true;
            }
        }

        // Stitch preceding month row if required
        if ($needsPrefix) {
            $prevMonthEnd = (clone $dStart)->modify('-1 day')->format('Y-m-d');
            $prevMonthStart = (new \DateTime($prevMonthEnd))->format('Y-m-01');

            $prevRow = DB::table('hr.attendance')
                ->where('att_emp_id', $empId)
                ->where('att_startdt', $prevMonthStart)
                ->first();

            if ($prevRow) {
                $prevStart = new \DateTime($prevRow->att_startdt);
                $prevEnd = new \DateTime($prevRow->att_enddt);
                $pCur = clone $prevStart;
                while ($pCur <= $prevEnd) {
                    $dayNum = (int)$pCur->format('j');
                    $col = 'att_' . $dayNum;
                    $prefixArr[] = [
                        'date' => $pCur->format('Y-m-d'),
                        'char' => $prevRow->$col ?? 'Q',
                    ];
                    $pCur->modify('+1 day');
                }

                // Apply leaves to prefix
                $prevLeaves = DB::table('hr.leaves')
                    ->where('lve_emp_id', $empId)
                    ->where('lve_from', '<=', $prevMonthEnd)
                    ->where('lve_to', '>=', $prevMonthStart)
                    ->get();

                foreach ($prevLeaves as $pl) {
                    $plFrom = new \DateTime($pl->lve_from);
                    $plTo = new \DateTime($pl->lve_to);
                    foreach ($prefixArr as &$pItem) {
                        $itemDt = new \DateTime($pItem['date']);
                        if ($itemDt >= $plFrom && $itemDt <= $plTo) {
                            $pItem['char'] = $pl->lve_type;
                        }
                    }
                }
            }
        }

        // Stitch succeeding month row if required
        if ($needsSuffix) {
            $nextMonthStart = (clone $dEnd)->modify('+1 day')->format('Y-m-d');
            $nextMonthEnd = (new \DateTime($nextMonthStart))->format('Y-m-t');

            $nextRow = DB::table('hr.attendance')
                ->where('att_emp_id', $empId)
                ->where('att_startdt', $nextMonthStart)
                ->first();

            if ($nextRow) {
                $nextStart = new \DateTime($nextRow->att_startdt);
                $nextEnd = new \DateTime($nextRow->att_enddt);
                $nCur = clone $nextStart;
                while ($nCur <= $nextEnd) {
                    $dayNum = (int)$nCur->format('j');
                    $col = 'att_' . $dayNum;
                    $suffixArr[] = [
                        'date' => $nCur->format('Y-m-d'),
                        'char' => $nextRow->$col ?? 'Q',
                    ];
                    $nCur->modify('+1 day');
                }

                // Apply leaves to suffix
                $nextLeaves = DB::table('hr.leaves')
                    ->where('lve_emp_id', $empId)
                    ->where('lve_from', '<=', $nextMonthEnd)
                    ->where('lve_to', '>=', $nextMonthStart)
                    ->get();

                foreach ($nextLeaves as $nl) {
                    $nlFrom = new \DateTime($nl->lve_from);
                    $nlTo = new \DateTime($nl->lve_to);
                    foreach ($suffixArr as &$sItem) {
                        $itemDt = new \DateTime($sItem['date']);
                        if ($itemDt >= $nlFrom && $itemDt <= $nlTo) {
                            $sItem['char'] = $nl->lve_type;
                        }
                    }
                }
            }
        }

        // Assemble super array
        $superArr = array_merge($prefixArr, $arr, $suffixArr);
        $superLen = count($superArr);

        // Run absorption: x Z+ x -> x x ... x
        foreach (self::ABSORPTION_CODES as $x) {
            $i = 0;
            while ($i < $superLen) {
                if ($superArr[$i]['char'] === $x) {
                    $j = $i + 1;
                    while ($j < $superLen && $superArr[$j]['char'] === 'Z') {
                        $j++;
                    }
                    if ($j < $superLen && $superArr[$j]['char'] === $x) {
                        for ($k = $i + 1; $k < $j; $k++) {
                            $superArr[$k]['char'] = $x;
                        }
                        $i = $j - 1;
                    }
                }
                $i++;
            }
        }

        // Slice back target window
        $prefixCount = count($prefixArr);
        $arr = array_slice($superArr, $prefixCount, count($arr));

        // Compile counts
        $counts = [
            'P' => 0, 'W' => 0, 'T' => 0, 'A' => 0, 'L' => 0,
            'U' => 0, 'N' => 0, 'Z' => 0, 'Q' => 0, 'X' => 0,
            'E' => 0, // Empty/unmarked working days
        ];

        foreach ($arr as $item) {
            $c = $item['char'];
            if ($c === null || $c === '' || $c === 'Q') {
                // Empty/unmarked cell on a working day
                $counts['E']++;
                $counts['Q']++;
            } elseif (!isset($counts[$c])) {
                $counts[$c] = 0;
                $counts[$c]++;
            } else {
                $counts[$c]++;
            }
        }

        $counts['total_days'] = count($arr);
        $counts['working_days'] = $counts['total_days'] - $counts['Z'] - $counts['X'] - ($counts['N'] ?? 0);

        return $counts;
    }
}
