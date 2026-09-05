<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\HrAttendance;
use App\Models\HrAttendanceRemark;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceUiTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected AttendanceService $service;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AttendanceService::class);

        // Find test HR / Admin user and ensure wide unit scope for feature testing
        $this->adminUser = CenAccount::where('acc_untarea', 'ILIKE', 'hr')->first()
            ?? CenAccount::where('acc_untarea', 'ILIKE', 'nrdi')->first()
            ?? CenAccount::first();

        $this->adminUser->acc_lowers = 100000;
        $this->adminUser->acc_uppers = 999999;
        $this->adminUser->acc_lowerm = 100000;
        $this->adminUser->acc_upperm = 999999;
        $this->adminUser->acc_auth = 'approver';
        $this->adminUser->save();
    }

    /**
     * 1. Test Monthly Grid View loads correctly with required view data and legacy floor date.
     */
    public function test_monthly_grid_view_loads_for_authorized_user(): void
    {
        $month = '2024-11';
        $response = $this->actingAs($this->adminUser)->get(route('divhr.attendance', ['month' => $month]));

        $response->assertStatus(200);
        $response->assertViewIs('hr.attendance.index');
        $response->assertViewHasAll(['list', 'days', 'weekdays', 'cutoff_day', 'floorDate', 'currentMonth']);

        $viewFloorDate = $response->viewData('floorDate');
        $this->assertEquals(AttendanceService::FLOOR_DATE, $viewFloorDate, 'Floor date must match AttendanceService::FLOOR_DATE (2021-01-01)');

        // Assert Division Salary Generation button is rendered on toolbar
        $response->assertSee('Generate Salary');
        $response->assertSee('id="btn-generate-salary-from-att"', false);
    }

    /**
     * 2. Test Fast Keyboard Save Endpoint handles batch payload.
     */
    public function test_keyboard_save_batch_endpoint(): void
    {
        // Pick an unlocked editable record in production or create one
        $record = HrAttendance::where('att_locked1', false)
            ->where('att_locked2', false)
            ->where('att_startdt', '>=', '2024-01-01')
            ->first();

        if (!$record) {
            $this->markTestSkipped('No unlocked attendance record found for save test.');
        }

        $empId = $record->att_emp_id;
        $month = Carbon::parse($record->att_startdt)->format('Y-m');

        $startDt = Carbon::parse($record->att_startdt);
        $testDay = 1;
        $weekendDay = null;
        for ($d = 1; $d <= 25; $d++) {
            $date = $startDt->copy()->addDays($d - 1);
            $col = 'att_' . $d;
            if (!$date->isWeekend() && $record->$col !== 'Z' && $testDay === 1) {
                $testDay = $d;
            }
            if ($date->isWeekend() && $weekendDay === null) {
                $weekendDay = $d;
            }
        }

        // 1. Verify user cannot modify weekend day (strict legacy weekend protection)
        if ($weekendDay !== null) {
            $badWeekendPayload = [
                [
                    'emp_id' => $empId,
                    'day'    => $weekendDay,
                    'val'    => 'P',
                ]
            ];
            $weekendResponse = $this->actingAs($this->adminUser)->post(route('divhr.attendance.save'), [
                'month'        => $month,
                'payload_json' => json_encode($badWeekendPayload),
            ]);
            $weekendResponse->assertStatus(422);
        }

        // 2. Valid batch change on weekday
        $payload = [
            [
                'emp_id' => $empId,
                'day'    => $testDay,
                'val'    => 'P',
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('divhr.attendance.save'), [
            'month'        => $month,
            'payload_json' => json_encode($payload),
        ]);

        $response->assertRedirect(route('divhr.attendance', ['month' => $month]));
        $response->assertSessionHas('success');

        $record->refresh();
        $targetCol = 'att_' . $testDay;
        $this->assertEquals('P', $record->$targetCol);
    }

    /**
     * 3. Test One-Day Drilldown View and Inline Remark Save.
     */
    public function test_one_day_drilldown_view_and_remark_save(): void
    {
        $date = '2024-11-15';
        $response = $this->actingAs($this->adminUser)->get(route('divhr.attendance.oneday', ['date' => $date]));

        $response->assertStatus(200);
        $response->assertViewIs('hr.attendance.oneday');
        $response->assertViewHasAll(['date', 'day', 'weekday', 'list']);

        // Test saving remark for day 15 on an unlocked record
        $record = HrAttendance::where('att_startdt', '2024-11-01')
            ->where('att_locked1', false)
            ->first() ?? HrAttendance::where('att_startdt', '2024-11-01')->first();

        if ($record && !$record->att_locked1) {
            $testRemark = 'Test One-Day Inline Remark ' . uniqid();
            $remarkRes = $this->actingAs($this->adminUser)->postJson(route('divhr.attendance.save_remark'), [
                'att_id'  => $record->att_id,
                'day'     => 15,
                'remarks' => $testRemark,
            ]);

            $remarkRes->assertStatus(200);
            $remarkRes->assertJson(['success' => true]);

            $savedRemark = HrAttendanceRemark::where('atr_att_id', $record->att_id)
                ->where('atr_attday', 15)
                ->value('atr_remarks');
            $this->assertEquals($testRemark, $savedRemark);
        }
    }

    /**
     * 4. Test Summary Modal Endpoint matches verified counts from Phase 1 spot-check data.
     * Verified employee: Tayyeba Rafaqat (11-19-08-8746) for Aug/Sep 2024.
     */
    public function test_summary_modal_endpoint_matches_verified_counts(): void
    {
        $empId = '11-19-08-8746'; // Real production employee
        $month = '2024-08';

        $response = $this->actingAs($this->adminUser)->getJson(route('divhr.attendance.summary', [
            'emp_id' => $empId,
            'month'  => $month,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'employee' => ['emp_id', 'emp_name', 'emp_cnic'],
            'month',
            'counts'   => ['P', 'W', 'T', 'A', 'L', 'U', 'N', 'Z', 'working_days', 'total_days'],
        ]);

        $data = $response->json();
        $counts = $data['counts'];

        // Directly assert against verified getEmpAttendanceSummary
        $expected = $this->service->getEmpAttendanceSummary($empId, '2024-08-01', '2024-08-31');
        $this->assertEquals($expected['total_days'], $counts['total_days']);
        $this->assertEquals($expected['working_days'], $counts['working_days']);
        $this->assertEquals($expected['P'], $counts['P']);
        $this->assertEquals($expected['Z'], $counts['Z']);
    }

    /**
     * 5. Test Bulk Action skips locked cells server-side even if UI was bypassed.
     */
    public function test_bulk_action_skips_locked_cells_server_side_even_if_bypassed(): void
    {
        // Real production record with locked1=true, locked2=false (att_id: 5004, emp_id: 17-22-07-7289, month: 2024-11)
        $targetAttId = 5004;
        $row = HrAttendance::find($targetAttId);

        if (!$row || !$row->att_locked1 || $row->att_locked2) {
            $this->markTestSkipped('Production test row 5004 not in expected state (locked1=true, locked2=false).');
        }

        // Ensure employee is Active in hr.emps for attendance grid selection
        DB::table('hr.emps')->where('emp_id', $row->att_emp_id)->update(['emp_status' => 'Active']);

        $origValDay20 = $row->att_20; // Pre-cutoff day (< 26) -> locked by locked1

        // Attempt raw bulk action over days 20 to 28 (bypassing any UI check)
        $this->actingAs($this->adminUser)->post(route('divhr.attendance.bulk_action'), [
            'month'      => '2024-11',
            'action'     => 'fill',
            'start_day'  => 20,
            'end_day'    => 28,
            'code'       => 'P',
        ]);

        $row->refresh();

        // 1. Pre-cutoff day 20 (locked) MUST remain unchanged
        $this->assertEquals($origValDay20, $row->att_20, 'Locked pre-cutoff day 20 must NOT be modified by bulk action');

        // 2. Post-cutoff day 27 (unlocked) MUST be updated to 'P'
        $this->assertEquals('P', $row->att_27, 'Unlocked post-cutoff day 27 MUST be updated by bulk action');
    }

    /**
     * 6. Test Sheet Generation Endpoint.
     */
    public function test_generate_sheet_endpoint_creates_or_syncs_records(): void
    {
        $month = '2024-12';

        $response = $this->actingAs($this->adminUser)->post(route('divhr.attendance.generate_sheet'), [
            'month' => $month,
        ]);

        $response->assertRedirect(route('divhr.attendance', ['month' => $month]));
        $response->assertSessionHas('success');
    }

    /**
     * 7. Test invalid dates (e.g. days 30 and 31 in Feb) are omitted from the grid DOM.
     */
    public function test_invalid_dates_omitted_from_grid(): void
    {
        // 2024 is a leap year; Feb 2024 has 29 days. Days 30 and 31 do not exist.
        $response = $this->actingAs($this->adminUser)->get(route('divhr.attendance', ['month' => '2024-02']));
        $response->assertStatus(200);

        $days = $response->viewData('days');
        $this->assertEquals(29, $days, 'February 2024 must have exactly 29 days');

        $content = $response->getContent();

        // Day 29 header and cells exist
        $this->assertStringContainsString('Day 29 remarks', $content);
        $this->assertStringContainsString('data-day="29"', $content);

        // Days 30 and 31 headers and cells MUST NOT exist anywhere in the DOM
        $this->assertStringNotContainsString('Day 30 remarks', $content);
        $this->assertStringNotContainsString('Day 31 remarks', $content);
        $this->assertStringNotContainsString('data-day="30"', $content);
        $this->assertStringNotContainsString('data-day="31"', $content);
    }

    /**
     * 8. Test future dates are disabled (readonly, tabindex -1) and have distinct styling.
     */
    public function test_future_dates_disabled_and_distinct(): void
    {
        // Current test environment month: September 2026.
        // Today is 2026-09-05. Days 20..30 are strictly future dates.
        $response = $this->actingAs($this->adminUser)->get(route('divhr.attendance', ['month' => '2026-09']));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Must contain cell-future class
        $this->assertStringContainsString('cell-future', $content);

        // Must contain title="Future date"
        $this->assertStringContainsString('title="Future date"', $content);

        // Future day cell has data-readonly="1" and tabindex="-1"
        $this->assertMatchesRegularExpression(
            '/class="att-cell\s+cell-future[^"]*"\s+tabindex="-1"[^>]*data-day="(?:20|21|22|23|24|25)"[^>]*data-readonly="1"/',
            $content
        );
    }
}
