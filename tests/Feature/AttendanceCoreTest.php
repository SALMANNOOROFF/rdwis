<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\HrAttendance;
use App\Models\HrAttendanceRemark;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AttendanceCoreTest extends TestCase
{
    use DatabaseTransactions;

    protected AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttendanceService::class);
    }

    /**
     * 1. Test all 9 codes: user-enterable codes are accepted, Z and X are rejected from user entry.
     */
    public function test_attendance_codes_validation()
    {
        foreach (['P', 'W', 'T', 'A', 'L', 'U', 'N', '', null] as $valid) {
            $this->assertTrue($this->service->isValidUserCode($valid), "Code '{$valid}' should be valid for user entry.");
        }

        foreach (['Z', 'X', 'INVALID', '1', 'Q'] as $invalid) {
            $this->assertFalse($this->service->isValidUserCode($invalid), "Code '{$invalid}' must not be user-enterable.");
        }
    }

    /**
     * 2. Security Gap Fix: Unit-range scoping enforced on write (HTTP 403 on violation).
     */
    public function test_unit_range_security_blocks_unauthorized_write()
    {
        // Find a restricted user (acc_lowers > 0)
        $user = CenAccount::where('acc_lowers', '>', 0)
            ->whereNotNull('acc_uppers')
            ->first();

        if (!$user) {
            $this->markTestSkipped('No unit-restricted user found in database.');
        }

        // Find an employee strictly outside user's unit range
        $outsideEmp = DB::table('hr.emps')
            ->where(function ($q) use ($user) {
                $q->where('emp_unt_id', '<', $user->acc_lowers)
                  ->orWhere('emp_unt_id', '>', $user->acc_uppers);
            })
            ->first();

        if (!$outsideEmp) {
            $this->markTestSkipped('No employee outside user range found.');
        }

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $this->service->saveAttendance($user, now()->format('Y-m'), [
            ['emp_id' => $outsideEmp->emp_id, 'day' => 5, 'val' => 'P'],
        ]);
    }

    /**
     * 3. Server-side lock enforcement: writes to locked records are rejected with 422.
     */
    public function test_locked_records_rejected_on_write()
    {
        $user = CenAccount::first();
        if (!$user) {
            $this->markTestSkipped('No user found.');
        }

        // Create a locked attendance row in transaction
        $emp = DB::table('hr.emps')->where('emp_status', 'ILIKE', 'active%')->first();
        if (!$emp) {
            $this->markTestSkipped('No active employee found.');
        }

        // Give user access to this employee's unit for test isolation
        $user->acc_lowers = 0;
        $user->acc_uppers = 99999999;
        $user->acc_untarea = 'hr';

        $monthStr = '2025-01';
        $lockedRow = HrAttendance::updateOrCreate(
            ['att_emp_id' => $emp->emp_id, 'att_startdt' => '2025-01-01'],
            [
                'att_empnamecomp' => $emp->emp_name,
                'att_unt_id'      => $emp->emp_unt_id,
                'att_enddt'       => '2025-01-31',
                'att_locked1'     => true,
                'att_locked2'     => true,
            ]
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(422);

        $this->service->saveAttendance($user, $monthStr, [
            ['emp_id' => $emp->emp_id, 'day' => 10, 'val' => 'P'],
        ]);
    }

    /**
     * 4. Holiday-absorption cross-month stitching test (May 31 'A' -> Jun 1-2 'Z' -> Jun 3 'A').
     */
    public function test_holiday_absorption_cross_month_stitching()
    {
        $testEmpId = '99-26-01-9999';
        $testUnit = 110000;

        DB::table('hr.emps')->insert([
            'emp_id'     => $testEmpId,
            'emp_cnic'   => '12345-1234567-1',
            'emp_name'   => 'Stitching Test Subject',
            'emp_unt_id' => $testUnit,
            'emp_status' => 'Active',
            'emp_joindt' => '2026-01-01',
        ]);

        // Insert May attendance: day 31 = 'A', rest = 'P'
        $mayCols = [];
        for ($d = 1; $d <= 31; $d++) {
            $mayCols['att_' . $d] = ($d === 31) ? 'A' : 'P';
        }
        DB::table('hr.attendance')->insert(array_merge([
            'att_emp_id'      => $testEmpId,
            'att_empnamecomp' => 'Stitching Test Subject',
            'att_unt_id'      => $testUnit,
            'att_startdt'     => '2026-05-01',
            'att_enddt'       => '2026-05-31',
            'att_locked1'     => false,
            'att_locked2'     => false,
        ], $mayCols));

        // Insert June attendance: day 1 = 'Z', day 2 = 'Z', day 3 = 'A', day 31 = 'X', rest = 'P'
        $juneCols = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($d === 1 || $d === 2) {
                $juneCols['att_' . $d] = 'Z';
            } elseif ($d === 3) {
                $juneCols['att_' . $d] = 'A';
            } elseif ($d === 31) {
                $juneCols['att_' . $d] = 'X';
            } else {
                $juneCols['att_' . $d] = 'P';
            }
        }
        DB::table('hr.attendance')->insert(array_merge([
            'att_emp_id'      => $testEmpId,
            'att_empnamecomp' => 'Stitching Test Subject',
            'att_unt_id'      => $testUnit,
            'att_startdt'     => '2026-06-01',
            'att_enddt'       => '2026-06-30',
            'att_locked1'     => false,
            'att_locked2'     => false,
        ], $juneCols));

        // Compute June summary with stitched absorption
        $summary = $this->service->getEmpAttendanceSummary($testEmpId, '2026-06-01', '2026-06-30');

        // June 1 and June 2 ('Z') must be absorbed into 'A' due to May 31 'A' and June 3 'A'
        $this->assertEquals(3, $summary['A'], "Absent count must be 3 (June 1, 2, 3 absorbed into 'A').");
        $this->assertEquals(0, $summary['Z'], "Holiday count must be 0 (both weekend days converted).");
        $this->assertEquals(27, $summary['P'], "Present count must be 27.");
    }

    /**
     * 5. Test sheet creation and invalid date marking ('X' on day 31 in 30-day month).
     */
    public function test_sheet_generation_marks_weekends_and_invalid_dates()
    {
        $month = '2026-11'; // 30-day month, Nov 1 is Sunday (Z)
        $created = $this->service->makeAttendanceSheet($month);

        $this->assertGreaterThan(0, $created, "Must create sheet rows for active employees.");

        $row = HrAttendance::where('att_startdt', '2026-11-01')->first();
        $this->assertNotNull($row);

        // Day 31 must be 'X' in November
        $this->assertEquals('X', $row->att_31, "Day 31 in November must be 'X'.");

        // Nov 1, 2026 is a Sunday, so att_1 must be 'Z'
        $this->assertEquals('Z', $row->att_1, "Day 1 (Sunday) in Nov 2026 must be 'Z'.");
    }

    /**
     * 6. Test remarks persistence and day drill-down.
     */
    public function test_remarks_persistence_and_day_drilldown()
    {
        $user = CenAccount::first();
        $user->acc_lowers = 0;
        $user->acc_uppers = 99999999;
        $user->acc_untarea = 'hr';

        $att = HrAttendance::first();
        if (!$att) {
            $this->markTestSkipped('No attendance row found.');
        }
        $att->att_locked1 = false;
        $att->att_locked2 = false;
        $att->save();

        // Save remark
        $this->service->saveDayRemark($user, $att->att_id, 14, 'Medical appointment approved by DG');

        $remark = HrAttendanceRemark::where('atr_att_id', $att->att_id)
            ->where('atr_attday', 14)
            ->first();

        $this->assertNotNull($remark);
        $this->assertEquals('Medical appointment approved by DG', $remark->atr_remarks);

        // Clear remark
        $this->service->saveDayRemark($user, $att->att_id, 14, '');
        $cleared = HrAttendanceRemark::where('atr_att_id', $att->att_id)
            ->where('atr_attday', 14)
            ->first();
        $this->assertNull($cleared);
    }

    /**
     * 7. Suffix-direction holiday absorption stitching test.
     * May 29 = 'A', May 30-31 = 'Z', June 1 = 'A' -> May 30 and 31 absorbed into 'A' when querying May.
     */
    public function test_holiday_absorption_suffix_direction_stitching()
    {
        $testEmpId = '99-26-02-8888';
        $testUnit = 110000;

        DB::table('hr.emps')->insert([
            'emp_id'     => $testEmpId,
            'emp_cnic'   => '12345-1234567-2',
            'emp_name'   => 'Suffix Test Subject',
            'emp_unt_id' => $testUnit,
            'emp_status' => 'Active',
            'emp_joindt' => '2026-01-01',
        ]);

        // Insert May attendance: day 29 = 'A', day 30 = 'Z', day 31 = 'Z', rest = 'P'
        $mayCols = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($d === 29) {
                $mayCols['att_' . $d] = 'A';
            } elseif ($d === 30 || $d === 31) {
                $mayCols['att_' . $d] = 'Z';
            } else {
                $mayCols['att_' . $d] = 'P';
            }
        }
        DB::table('hr.attendance')->insert(array_merge([
            'att_emp_id'      => $testEmpId,
            'att_empnamecomp' => 'Suffix Test Subject',
            'att_unt_id'      => $testUnit,
            'att_startdt'     => '2026-05-01',
            'att_enddt'       => '2026-05-31',
            'att_locked1'     => false,
            'att_locked2'     => false,
        ], $mayCols));

        // Insert June attendance: day 1 = 'A', rest = 'P', day 31 = 'X'
        $juneCols = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($d === 1) {
                $juneCols['att_' . $d] = 'A';
            } elseif ($d === 31) {
                $juneCols['att_' . $d] = 'X';
            } else {
                $juneCols['att_' . $d] = 'P';
            }
        }
        DB::table('hr.attendance')->insert(array_merge([
            'att_emp_id'      => $testEmpId,
            'att_empnamecomp' => 'Suffix Test Subject',
            'att_unt_id'      => $testUnit,
            'att_startdt'     => '2026-06-01',
            'att_enddt'       => '2026-06-30',
            'att_locked1'     => false,
            'att_locked2'     => false,
        ], $juneCols));

        // Compute May summary with stitched suffix absorption
        $summary = $this->service->getEmpAttendanceSummary($testEmpId, '2026-05-01', '2026-05-31');

        // May 30 and 31 ('Z') must be absorbed into 'A' due to May 29 'A' and June 1 'A'
        $this->assertEquals(3, $summary['A'], "May absent count must be 3 (May 29, 30, 31 absorbed into 'A').");
        $this->assertEquals(28, $summary['P'], "May present count must be 28.");
    }

    /**
     * 8. Bulk Action: respects unit-scope (cannot modify employees outside unit range).
     */
    public function test_apply_bulk_action_respects_unit_scope()
    {
        $user = new CenAccount();
        $user->acc_lowers = 200000;
        $user->acc_uppers = 249999;
        $user->acc_lowerm = 200000;
        $user->acc_upperm = 249999;
        $user->acc_untarea = 'prj';

        $empInside = '99-26-03-1111';
        $empOutside = '99-26-03-2222';
        $month = '2026-07';

        DB::table('hr.emps')->insert([
            ['emp_id' => $empInside, 'emp_cnic' => '12345-0000001-1', 'emp_name' => 'Inside Subject', 'emp_unt_id' => 210000, 'emp_status' => 'Active', 'emp_joindt' => '2026-01-01'],
            ['emp_id' => $empOutside, 'emp_cnic' => '12345-0000002-1', 'emp_name' => 'Outside Subject', 'emp_unt_id' => 300000, 'emp_status' => 'Active', 'emp_joindt' => '2026-01-01'],
        ]);

        $baseRow = [
            'att_startdt' => '2026-07-01',
            'att_enddt'   => '2026-07-31',
            'att_locked1' => false,
            'att_locked2' => false,
            'att_10'      => null,
        ];
        DB::table('hr.attendance')->insert([
            array_merge($baseRow, ['att_emp_id' => $empInside, 'att_empnamecomp' => 'Inside Subject', 'att_unt_id' => 210000]),
            array_merge($baseRow, ['att_emp_id' => $empOutside, 'att_empnamecomp' => 'Outside Subject', 'att_unt_id' => 300000]),
        ]);

        $this->service->applyBulkAction($user, $month, [
            'action'    => 'fill',
            'start_day' => 10,
            'end_day'   => 10,
            'code'      => 'P',
        ]);

        $insideVal = DB::table('hr.attendance')->where('att_emp_id', $empInside)->where('att_startdt', '2026-07-01')->value('att_10');
        $outsideVal = DB::table('hr.attendance')->where('att_emp_id', $empOutside)->where('att_startdt', '2026-07-01')->value('att_10');

        $this->assertEquals('P', $insideVal, "Inside employee must be updated by bulk fill.");
        $this->assertNull($outsideVal, "Outside employee must NOT be updated by bulk fill.");
    }

    /**
     * 9. Bulk Action: respects lock state (locked records are skipped and not overwritten).
     */
    public function test_apply_bulk_action_respects_lock_state()
    {
        $user = new CenAccount();
        $user->acc_lowers = 899000;
        $user->acc_uppers = 899000;
        $user->acc_lowerm = 899000;
        $user->acc_upperm = 899000;
        $user->acc_untarea = 'prj';

        $empLocked = '99-26-04-3333';
        $month = '2026-08';

        DB::table('hr.emps')->insert([
            'emp_id' => $empLocked, 'emp_cnic' => '12345-0000003-1', 'emp_name' => 'Locked Subject', 'emp_unt_id' => 899000, 'emp_status' => 'Active', 'emp_joindt' => '2026-01-01',
        ]);

        DB::table('hr.attendance')->insert([
            'att_emp_id'      => $empLocked,
            'att_empnamecomp' => 'Locked Subject',
            'att_unt_id'      => 899000,
            'att_startdt'     => '2026-08-01',
            'att_enddt'       => '2026-08-31',
            'att_locked1'     => true, // LOCKED!
            'att_locked2'     => false,
            'att_12'          => 'L',
        ]);

        // Attempt to bulk fill with 'P'
        $updated = $this->service->applyBulkAction($user, $month, [
            'action'    => 'fill',
            'start_day' => 12,
            'end_day'   => 12,
            'code'      => 'P',
        ]);

        $this->assertEquals(0, $updated, "Locked employee must result in 0 updated rows.");
        $val = DB::table('hr.attendance')->where('att_emp_id', $empLocked)->where('att_startdt', '2026-08-01')->value('att_12');
        $this->assertEquals('L', $val, "Locked employee's original attendance code must remain unchanged.");
    }

    /**
     * 10. Bulk Action: holiday-toggle correctly flips 'Z' on and off.
     */
    public function test_apply_bulk_action_holiday_toggle()
    {
        $user = new CenAccount();
        $user->acc_lowers = 200000;
        $user->acc_uppers = 249999;
        $user->acc_lowerm = 200000;
        $user->acc_upperm = 249999;
        $user->acc_untarea = 'prj';

        $emp = '99-26-05-4444';
        $month = '2026-09';

        DB::table('hr.emps')->insert([
            'emp_id' => $emp, 'emp_cnic' => '12345-0000004-1', 'emp_name' => 'Holiday Subject', 'emp_unt_id' => 210000, 'emp_status' => 'Active', 'emp_joindt' => '2026-01-01',
        ]);

        DB::table('hr.attendance')->insert([
            'att_emp_id'      => $emp,
            'att_empnamecomp' => 'Holiday Subject',
            'att_unt_id'      => 210000,
            'att_startdt'     => '2026-09-01',
            'att_enddt'       => '2026-09-30',
            'att_locked1'     => false,
            'att_locked2'     => false,
            'att_15'          => 'P',
        ]);

        // Flip holiday ON for Day 15
        $this->service->applyBulkAction($user, $month, [
            'action'     => 'toggle_holiday',
            'start_day'  => 15,
            'end_day'    => 15,
            'is_holiday' => true,
        ]);
        $valOn = DB::table('hr.attendance')->where('att_emp_id', $emp)->where('att_startdt', '2026-09-01')->value('att_15');
        $this->assertEquals('Z', $valOn, "Day 15 must be set to 'Z' when holiday is toggled ON.");

        // Flip holiday OFF for Day 15
        $this->service->applyBulkAction($user, $month, [
            'action'     => 'toggle_holiday',
            'start_day'  => 15,
            'end_day'    => 15,
            'is_holiday' => false,
        ]);
        $valOff = DB::table('hr.attendance')->where('att_emp_id', $emp)->where('att_startdt', '2026-09-01')->value('att_15');
        $this->assertNull($valOff, "Day 15 'Z' code must be cleared when holiday is toggled OFF.");
    }

    /**
     * 11. Per-cell payroll cutoff locking test using a real production record (locked1=true, locked2=false).
     * Tests that pre-cutoff days (< cutoff, e.g. Day 21) are rejected with HTTP 422,
     * while post-cutoff days (>= cutoff, e.g. Day 27) are accepted and updated.
     */
    public function test_per_cell_payroll_cutoff_locking_with_real_production_record()
    {
        // Find one of the 75 real production records with locked1=true, locked2=false
        $prodRow = HrAttendance::where('att_locked1', true)
            ->where('att_locked2', false)
            ->first();

        if (!$prodRow) {
            $this->markTestSkipped('No production record with locked1=true and locked2=false found.');
        }

        $empId = $prodRow->att_emp_id;
        $unitId = (int)$prodRow->att_unt_id;
        $month = Carbon::parse($prodRow->att_startdt)->format('Y-m');
        $cutoff = $this->service->getPayrollCutoffDay(); // e.g. 26

        $preCutoffDay = $cutoff - 5; // e.g. day 21 (< 26)
        $postCutoffDay = $cutoff + 1; // e.g. day 27 (>= 26)

        // Set up user authorized for this employee's unit
        $user = new CenAccount();
        $user->acc_lowers = $unitId;
        $user->acc_uppers = $unitId;
        $user->acc_lowerm = $unitId;
        $user->acc_upperm = $unitId;
        $user->acc_untarea = 'hr';

        // 1. Pre-cutoff day edit must be rejected with 422 (locked by locked1)
        $preCutoffRejected = false;
        try {
            $this->service->saveAttendance($user, $month, [
                ['emp_id' => $empId, 'day' => $preCutoffDay, 'val' => 'P'],
            ]);
        } catch (HttpException $e) {
            $preCutoffRejected = true;
            $this->assertEquals(422, $e->getStatusCode(), "Pre-cutoff edit must be rejected with HTTP 422.");
            $this->assertStringContainsString('locked1', $e->getMessage(), "Error message must indicate locked1.");
        }
        $this->assertTrue($preCutoffRejected, "Pre-cutoff day edit must throw HttpException 422.");

        // 2. Post-cutoff day edit must be ACCEPTED (locked2 is false)
        $updatedCount = $this->service->saveAttendance($user, $month, [
            ['emp_id' => $empId, 'day' => $postCutoffDay, 'val' => 'P'],
        ]);

        $this->assertEquals(1, $updatedCount, "Post-cutoff day edit must be successfully saved.");
        
        $col = 'att_' . $postCutoffDay;
        $savedVal = DB::table('hr.attendance')
            ->where('att_emp_id', $empId)
            ->where('att_startdt', $prodRow->att_startdt)
            ->value($col);

        $this->assertEquals('P', $savedVal, "Post-cutoff day {$postCutoffDay} cell must be updated to 'P'.");
    }
}
