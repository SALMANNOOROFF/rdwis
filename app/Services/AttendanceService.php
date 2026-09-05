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
    public const ABSORPTION_CODES = ['A', 'L', 'U', 'T'];

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
     * Verify unit scoping on write, abort with 403 if unauthorized.
     */
    public function authorizeEmployeeWrite(Authenticatable|User|CenAccount $user, string $empId): void
    {
        if (!$this->canUserAccessEmployee($user, $empId)) {
            throw new HttpException(403, "Access denied. Employee {$empId} is outside your unit scope.", null, [], 403);
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
     * Check if a specific cell/day is locked split by payroll cutoff day.
     * Mirrors legacy PrepareAttendanceSheet (hr_attendance_u.bas:67-74):
     *   - If day < cutoff AND att_locked1 = 1 -> locked
     *   - If day >= cutoff AND att_locked2 = 1 -> locked
     */
    public function isCellLocked(HrAttendance $row, int $day): bool
    {
        $cutoff = $this->getPayrollCutoffDay();

        if ($day < $cutoff && (bool)$row->att_locked1) {
            return true;
        }

        if ($day >= $cutoff && (bool)$row->att_locked2) {
            return true;
        }

        return false;
    }

    /**
     * Check if an attendance record or specific day is locked against edits.
     */
    public function isRecordLocked(HrAttendance $row, ?int $day = null): bool
    {
        if ($day !== null) {
            return $this->isCellLocked($row, $day);
        }

        return (bool)($row->att_locked1 && $row->att_locked2);
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
                for ($d = 1; $d <= 31; $d++) {
                    $col = "att_{$d}";
                    $val = $templateRow->$col ?? null;
                    $dayData[$col] = in_array($val, ['Z', 'X'], true) ? $val : null;
                }

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
        $isCentral = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs'], true);

        if ($mode === 's' || !$isCentral) {
            $lower = (int)$user->acc_lowers === 0 ? (int)$user->acc_lowerm : (int)$user->acc_lowers;
            $upper = (int)$user->acc_lowers === 0 ? (int)$user->acc_upperm : (int)$user->acc_uppers;
        } else {
            $lower = 0;
            $upper = 99999999;
        }

        $first = Carbon::parse($month . '-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $days = (int) $first->daysInMonth;

        $rows = collect(DB::select("
            SELECT e.emp_id, e.emp_name, e.emp_unt_id,
                   a.att_id, a.att_emp_id, a.att_empnamecomp, a.att_unt_id,
                   a.att_startdt, a.att_enddt, a.att_locked1, a.att_locked2,
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
        ", [$last->toDateString(), $first->toDateString(), $lower, $upper]));

        $cutoffDay = $this->getPayrollCutoffDay();

        $data = $rows->map(function ($r) use ($days, $cutoffDay, $first) {
            $vals = [];
            $lockedDays = [];
            for ($d = 1; $d <= 31; $d++) {
                $col = 'att_' . $d;
                $rawVal = isset($r->$col) && $r->$col !== null ? strtoupper(trim($r->$col)) : null;
                $isWeekend = false;
                if ($d <= $days) {
                    $dayDt = $first->copy()->addDays($d - 1);
                    $isWeekend = $dayDt->isWeekend(); // Saturday or Sunday
                }

                // If Saturday or Sunday, ALWAYS treat as 'Z' (weekend)
                $val = $isWeekend ? 'Z' : $rawVal;
                $vals[$d] = $val;

                // Per-cell locking: Cutoff locking OR weekend OR existing 'Z' public holiday OR 'X'
                $isCutoffLocked = ($d < $cutoffDay && (bool)($r->att_locked1 ?? false)) ||
                                  ($d >= $cutoffDay && (bool)($r->att_locked2 ?? false));
                $lockedDays[$d] = $isCutoffLocked || $isWeekend || $val === 'Z' || $val === 'X';
            }

            $present = 0;
            for ($d = 1; $d <= $days; $d++) {
                if (($vals[$d] ?? '') === 'P') {
                    $present++;
                }
            }

            return [
                'emp_id'      => $r->emp_id,
                'name'        => $r->emp_name,
                'att_id'      => $r->att_id,
                'unit_id'     => $r->emp_unt_id,
                'locked'      => (bool) ($r->att_locked1 && $r->att_locked2),
                'locked1'     => (bool) ($r->att_locked1 ?? false),
                'locked2'     => (bool) ($r->att_locked2 ?? false),
                'locked_days' => $lockedDays,
                'vals'        => $vals,
                'present'     => $present,
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
        ];

        foreach ($arr as $item) {
            $c = $item['char'];
            if (!isset($counts[$c])) {
                $counts[$c] = 0;
            }
            $counts[$c]++;
        }

        $counts['total_days'] = count($arr);
        $counts['working_days'] = $counts['total_days'] - $counts['Z'] - $counts['X'];

        return $counts;
    }
}
