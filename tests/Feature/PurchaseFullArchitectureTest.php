<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\Purchase;
use App\Services\FinancialIntelligenceService;
use App\Http\Controllers\Division\FinanceOfProjectController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseFullArchitectureTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected CenAccount $user;
    protected object $head;
    protected object $unit;
    protected object $firm1;
    protected object $firm2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = DB::table('cen.units')->first();
        $this->head = DB::table('cen.heads')->first();
        $this->user = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first()
            ?? CenAccount::first();

        // Retrieve two active firms
        $firms = DB::table('frm.firmz')->where('frm_id', '>', 0)->limit(2)->get();
        $this->firm1 = $firms[0];
        $this->firm2 = $firms[1] ?? $firms[0];
    }

    public function test_ps_case_creation_enforces_equipment_subhead_and_saves_access_fields()
    {
        $this->actingAs($this->user);

        $payload = [
            'pcs_type' => 'Ps',
            'pcs_title' => 'Laboratory Oscilloscope Purchase',
            'pcs_hed_id' => $this->head->hed_id,
            'pcs_date' => now()->toDateString(),
            'pcs_minute' => 101,
            'pcs_quotetype' => 2, // With Tax
            'tax_type' => 'GST',
            'tax_percent' => 18,
            'subhead' => 'Misc', // Attempt to pass Misc, but Ps MUST enforce Equipment
            'items' => [
                [
                    'desc' => 'Digital Storage Oscilloscope 100MHz',
                    'qty' => 2,
                    'unit' => 'num',
                    'type' => 7, // Permanent
                    'subtype' => 'Test / Measuring Equipment',
                    'inv_asst' => 6, // Asset
                    'subhead' => 'Equipment'
                ]
            ],
            'quotations' => [
                $this->firm1->frm_id => [
                    0 => 50000 // Firm 1: 50,000 * 2 = 100,000 + 18% GST = 118,000 (Winner)
                ],
                $this->firm2->frm_id => [
                    0 => 60000 // Firm 2: 60,000 * 2 = 120,000 + 18% GST = 141,600
                ]
            ],
            'not_received_firms' => [$this->firm2->frm_id]
        ];

        $response = $this->post(route('purchase.store'), $payload);
        $response->assertRedirect(route('purchase.initiation.index'));

        // Verify case created
        $case = Purchase::where('pcs_title', 'Laboratory Oscilloscope Purchase')->latest('pcs_id')->first();
        $this->assertNotNull($case);
        $this->assertEquals('Ps', $case->pcs_type);
        $this->assertEquals(2, $case->pcs_quotetype);
        $this->assertEquals(118000, (float)$case->pcs_price);
        $this->assertEquals($this->firm1->frm_id, $case->pcs_frm_id);

        // Verify pur.purcases_shd strictly locked to Equipment
        $shdRecord = DB::table('pur.purcases_shd')->where('pcd_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($shdRecord);
        $this->assertEquals('Equipment', $shdRecord->pcd_subhead);
        $this->assertEquals('Equipment', $case->subhead_display);

        // Verify pur.purcaseitems Access fields
        $item = DB::table('pur.purcaseitems')->where('pci_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($item);
        $this->assertEquals(7, $item->pci_type);
        $this->assertEquals('Test / Measuring Equipment', $item->pci_subtype);
        $this->assertEquals(6, $item->pci_type2);
        $this->assertEquals('Equipment', $item->pci_subhead);
        $this->assertEquals('num', $item->pci_qtyunit);

        // Verify pur.noquotes
        $this->assertDatabaseHas('pur.noquotes', [
            'nqt_pcs_id' => $case->pcs_id,
            'nqt_frm_id' => $this->firm2->frm_id
        ]);
    }

    public function test_pt_case_creation_supports_custom_subhead_and_single_vendor()
    {
        $this->actingAs($this->user);

        // Test with Equipment subhead selected for Pt
        $payload = [
            'pcs_type' => 'Pt',
            'pcs_title' => 'Emergency Tool Kit Replacement',
            'pcs_hed_id' => $this->head->hed_id,
            'pcs_frm_id' => $this->firm1->frm_id,
            'pcs_date' => now()->toDateString(),
            'pcs_minute' => 102,
            'subhead' => 'Equipment',
            'items' => [
                [
                    'desc' => 'Industrial Tool Box & Wrench Set',
                    'qty' => 3,
                    'unit' => 'set',
                    'price' => 15000,
                    'type' => 2, // Consumable
                    'subtype' => 'Parts',
                    'inv_asst' => 5, // Inventory
                    'subhead' => 'Equipment'
                ]
            ]
        ];

        $response = $this->post(route('purchase.store'), $payload);
        $response->assertRedirect(route('purchase.initiation.index'));

        $case = Purchase::where('pcs_title', 'Emergency Tool Kit Replacement')->latest('pcs_id')->first();
        $this->assertNotNull($case);
        $this->assertEquals('Pt', $case->pcs_type);
        $this->assertEquals(45000, (float)$case->pcs_price);
        $this->assertEquals($this->firm1->frm_id, $case->pcs_frm_id);

        // Verify pur.purcases_shd holds the user-chosen subhead (Equipment)
        $shdRecord = DB::table('pur.purcases_shd')->where('pcd_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($shdRecord);
        $this->assertEquals('Equipment', $shdRecord->pcd_subhead);
        $this->assertEquals('Equipment', $case->subhead_display);

        // Verify item fields
        $item = DB::table('pur.purcaseitems')->where('pci_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($item);
        $this->assertEquals(2, $item->pci_type);
        $this->assertEquals('Parts', $item->pci_subtype);
        $this->assertEquals(5, $item->pci_type2);
        $this->assertEquals('Equipment', $item->pci_subhead);
        $this->assertEquals('set', $item->pci_qtyunit);
    }

    public function test_rb_case_creation_supports_employee_tada_and_subhead()
    {
        $this->actingAs($this->user);

        // Create test employee for TA/DA
        $empId = 'TADA-' . rand(1000, 9999);
        DB::table('hr.emps')->insert([
            'emp_id'     => $empId,
            'emp_cnic'   => '42000-' . rand(1000000, 9999999) . '-1',
            'emp_name'   => 'Engineer Ali Khan',
            'emp_title'  => 'Engr.',
            'emp_rank'   => 'Field Officer',
            'emp_status' => 'Active',
            'emp_unt_id' => $this->unit->unt_id,
            'emp_joindt' => '2023-01-01',
        ]);
        DB::table('hr.contracts')->insert([
            'ctr_num'      => $empId,
            'ctr_grade'    => 'RO',
            'ctr_salary'   => 45000, // < 50k => rate 2000
            'ctr_startdt'  => '2024-01-01',
            'ctr_enddt'    => '2024-12-31',
            'ctr_date'     => '2024-01-01',
            'ctr_jobtitle' => 'Field Officer',
            'ctr_unt_id'   => $this->unit->unt_id,
            'ctr_type'     => 1,
        ]);

        $payload = [
            'pcs_type' => 'Rb',
            'pcs_title' => 'Official Travel TA/DA Reimbursement',
            'pcs_hed_id' => $this->head->hed_id,
            'pcs_date' => now()->toDateString(),
            'pcs_minute' => 103,
            'subhead' => 'Misc',
            'items' => [
                [
                    'emp_id' => $empId,
                    'desc' => 'TA/DA Allowance',
                    'qty' => 1,
                    'unit' => 'num',
                    'price' => 2000,
                    'type' => 3, // Service
                    'subtype' => 'Travelling/Boarding/Lodging',
                    'subhead' => 'Misc'
                ]
            ]
        ];

        $response = $this->post(route('purchase.store'), $payload);
        $response->assertRedirect(route('purchase.initiation.index'));

        $case = Purchase::where('pcs_title', 'Official Travel TA/DA Reimbursement')->latest('pcs_id')->first();
        $this->assertNotNull($case);
        $this->assertEquals('Rb', $case->pcs_type);
        $this->assertEquals(2000, (float)$case->pcs_price);

        // Subhead in pur.purcases_shd
        $shdRecord = DB::table('pur.purcases_shd')->where('pcd_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($shdRecord);
        $this->assertEquals('Misc', $shdRecord->pcd_subhead);
        $this->assertEquals('Misc', $case->subhead_display);

        // Item fields
        $item = DB::table('pur.purcaseitems')->where('pci_pcs_id', $case->pcs_id)->first();
        $this->assertNotNull($item);
        $this->assertEquals($empId, $item->pci_emp_id);
        $this->assertEquals(3, $item->pci_type);
        $this->assertEquals('Travelling/Boarding/Lodging', $item->pci_subtype);
        $this->assertNull($item->pci_type2); // Service has no inventory/asset
        $this->assertEquals(2000, (float)$item->pci_price);
    }

    public function test_in_process_drilldown_never_returns_general_and_isolates_equipment()
    {
        $head = DB::table('cen.heads')->where('hed_id', 200018)->first();
        $this->assertNotNull($head);

        // Authenticate with an account authorized for this head's unit
        $authorizedUser = CenAccount::where('acc_unt_id', $head->hed_unt_id)->first() ?? $this->user;
        $authorizedUser->acc_lowerm = 0;
        $authorizedUser->acc_upperm = 999999;
        $this->actingAs($authorizedUser);

        $controller = app(FinanceOfProjectController::class);

        // Call drilldown for subhead Equipment on project head 200018
        $view = $controller->drillDown(200018, 'subhead', 'in-process', 'Equipment');

        $this->assertEquals('division.finance-of-project.drilldown', $view->name());
        $viewData = $view->getData();

        $this->assertEquals('Equipment', $viewData['subhead']);
        $this->assertEquals('in-process', $viewData['figure']);
        $this->assertNotEmpty($viewData['items']);

        foreach ($viewData['items'] as $row) {
            $this->assertNotEquals('General', $row->subhead, 'Drilldown subhead must never be General');
            $this->assertEquals('Equipment', $row->subhead);
        }
    }
}
