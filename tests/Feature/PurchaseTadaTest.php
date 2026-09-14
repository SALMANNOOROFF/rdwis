<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\Employee;
use App\Models\HrContract;
use App\Models\Purchase;
use App\Services\PurchasePricingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseTadaTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $adminUser;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PurchasePricingService::class);
        $this->adminUser = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first()
            ?? CenAccount::first();
    }

    private function createTestEmployee(float $salary = 45000, string $grade = 'RO', string $name = 'Test Employee', ?int $untId = null): string
    {
        $empId = 'TADA-' . rand(1000, 9999);
        $cnic = '42000-' . rand(1000000, 9999999) . '-' . rand(1, 9);
        $unitId = $untId ?? 1;

        DB::table('hr.emps')->insert([
            'emp_id'     => $empId,
            'emp_cnic'   => $cnic,
            'emp_name'   => $name,
            'emp_title'  => 'Mr.',
            'emp_rank'   => 'Officer',
            'emp_status' => 'Active',
            'emp_unt_id' => $unitId,
            'emp_joindt' => '2023-01-01',
        ]);

        DB::table('hr.contracts')->insert([
            'ctr_num'      => $empId,
            'ctr_grade'    => $grade,
            'ctr_salary'   => $salary,
            'ctr_startdt'  => '2024-01-01',
            'ctr_enddt'    => '2024-12-31',
            'ctr_date'     => '2024-01-01',
            'ctr_jobtitle' => 'Research Officer',
            'ctr_unt_id'   => $unitId,
            'ctr_type'     => 1,
        ]);

        return $empId;
    }

    /**
     * 1. Test Salary Bracket: Salary < 50,000 -> 2,000
     */
    public function test_salary_bracket_under_50k_yields_2000(): void
    {
        $this->assertEquals(2000, PurchasePricingService::getTadaForSalary(0));
        $this->assertEquals(2000, PurchasePricingService::getTadaForSalary(35000));
        $this->assertEquals(2000, PurchasePricingService::getTadaForSalary(49999));

        $empId = $this->createTestEmployee(42000, 'JRA');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'emp_id'      => $empId,
                'grade'       => 'JRA',
                'salary'      => 42000,
                'tada_amount' => 2000,
            ]);
    }

    /**
     * 2. Test Salary Bracket: 50,000 <= Salary <= 100,000 -> 3,500
     */
    public function test_salary_bracket_50k_to_100k_yields_3500(): void
    {
        $this->assertEquals(3500, PurchasePricingService::getTadaForSalary(50000));
        $this->assertEquals(3500, PurchasePricingService::getTadaForSalary(75000));
        $this->assertEquals(3500, PurchasePricingService::getTadaForSalary(100000));

        $empId = $this->createTestEmployee(75000, 'RO');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'emp_id'      => $empId,
                'grade'       => 'RO',
                'salary'      => 75000,
                'tada_amount' => 3500,
            ]);
    }

    /**
     * 3. Test Salary Bracket: Salary > 100,000 -> 5,000
     */
    public function test_salary_bracket_over_100k_yields_5000(): void
    {
        $this->assertEquals(5000, PurchasePricingService::getTadaForSalary(100001));
        $this->assertEquals(5000, PurchasePricingService::getTadaForSalary(150000));

        $empId = $this->createTestEmployee(125000, 'Director');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'emp_id'      => $empId,
                'grade'       => 'Director',
                'salary'      => 125000,
                'tada_amount' => 5000,
            ]);
    }

    /**
     * 4. Test Meezan Bank Exact Match -> [bac_accnum] ([bac_bchcode])
     */
    public function test_meezan_bank_exact_match(): void
    {
        $empId = $this->createTestEmployee(60000, 'SRO', 'Tada Candidate');

        DB::table('hr.bnkaccounts')->insert([
            'bac_emp_id'    => $empId,
            'bac_bnkname'   => 'Meezan Bank Ltd',
            'bac_bchname'   => 'Main Branch',
            'bac_bchcode'   => '0129',
            'bac_accnum'    => '0105931783',
            'bac_acctitle'  => 'Tada Candidate',
            'bac_bchcity'   => 'Islamabad',
            'bac_selforpay' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('0105931783 (0129)', $data['bank_detail']);
        $this->assertStringContainsString("Meezan Account: 0105931783 (0129)", $data['description']);
        $this->assertStringContainsString("TA/DA for Mr. Officer Tada Candidate", $data['description']);
        $this->assertStringContainsString("ID: {$empId}", $data['description']);
        $this->assertStringContainsString("Grade: SRO", $data['description']);
    }

    /**
     * 5. Test Non-Meezan Bank -> (Pay by Cheque)
     */
    public function test_non_meezan_bank_falls_back_to_cheque(): void
    {
        $empId = $this->createTestEmployee(60000, 'RO');

        DB::table('hr.bnkaccounts')->insert([
            'bac_emp_id'    => $empId,
            'bac_bnkname'   => 'Habib Bank Ltd',
            'bac_bchname'   => 'Main Branch',
            'bac_bchcode'   => '0456',
            'bac_accnum'    => '9876543210',
            'bac_acctitle'  => 'Test Account',
            'bac_bchcity'   => 'Islamabad',
            'bac_selforpay' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('(Pay by Cheque)', $data['bank_detail']);
        $this->assertStringContainsString("Meezan Account: (Pay by Cheque)", $data['description']);
    }

    /**
     * 6. Test No Bank Account -> (Pay by Cheque)
     */
    public function test_no_bank_account_falls_back_to_cheque(): void
    {
        $empId = $this->createTestEmployee(60000, 'RO');

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('(Pay by Cheque)', $data['bank_detail']);
        $this->assertStringContainsString("Meezan Account: (Pay by Cheque)", $data['description']);
    }

    /**
     * 7. Test Multiple Bank Accounts -> Block with error:
     * "Multiple bank accounts are marked for salary of [empId]. Please correct bank account data."
     */
    public function test_multiple_bank_accounts_blocks_with_error(): void
    {
        $empId = $this->createTestEmployee(60000, 'RO');

        DB::table('hr.bnkaccounts')->insert([
            [
                'bac_emp_id'    => $empId,
                'bac_bnkname'   => 'Meezan Bank Ltd',
                'bac_bchname'   => 'Branch 1',
                'bac_bchcode'   => '0101',
                'bac_accnum'    => '1111111111',
                'bac_acctitle'  => 'Account 1',
                'bac_bchcity'   => 'Islamabad',
                'bac_selforpay' => true,
            ],
            [
                'bac_emp_id'    => $empId,
                'bac_bnkname'   => 'Habib Bank Ltd',
                'bac_bchname'   => 'Branch 2',
                'bac_bchcode'   => '0202',
                'bac_accnum'    => '2222222222',
                'bac_acctitle'  => 'Account 2',
                'bac_bchcity'   => 'Islamabad',
                'bac_selforpay' => true,
            ]
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('purchase.tada.employee_details', $empId));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error'   => "Multiple bank accounts are marked for salary of {$empId}. Please correct bank account data.",
            ]);
    }

    /**
     * 8. Test Conditional Rendering Isolation:
     * - Ps: employee selector must NOT render
     * - Pt: employee selector must NOT render
     * - Rb: employee selector MUST render
     */
    public function test_conditional_rendering_isolation(): void
    {
        $responsePs = $this->actingAs($this->adminUser)->get(route('purchase.unified.create', 'Ps'));
        $responsePs->assertStatus(200);
        $responsePs->assertDontSee('tada-emp-select');
        $responsePs->assertDontSee('Add Emp Details');
        $responsePs->assertDontSee('Vendor / Firm Name');

        $responsePt = $this->actingAs($this->adminUser)->get(route('purchase.unified.create', 'Pt'));
        $responsePt->assertStatus(200);
        $responsePt->assertDontSee('tada-emp-select');
        $responsePt->assertDontSee('Add Emp Details');
        $responsePt->assertSee('Vendor / Firm Name');

        $responseRb = $this->actingAs($this->adminUser)->get(route('purchase.unified.create', 'Rb'));
        $responseRb->assertStatus(200);
        $responseRb->assertSee('tada-emp-select');
        $responseRb->assertSee('Add Emp Details');
        $responseRb->assertDontSee('Vendor / Firm Name');
    }

    /**
     * 9. Test Store Rb Case with Item: Persists pci_emp_id and calculated TA/DA price
     */
    public function test_store_rb_case_persists_item_and_pci_emp_id(): void
    {
        $head = DB::table('cen.heads')->first();
        if (!$head) {
            $this->markTestSkipped('No budget head found');
        }

        $empId = $this->createTestEmployee(80000, 'Manager', 'Tada Traveler');

        $response = $this->actingAs($this->adminUser)->post(route('purchase.store'), [
            'pcs_title'  => 'Official Trip TA/DA Case',
            'pcs_hed_id' => $head->hed_id,
            'pcs_date'   => now()->toDateString(),
            'pcs_minute' => 1,
            'pcs_type'   => 'Rb',
            'items'      => [
                [
                    'emp_id' => $empId,
                    'desc'   => 'Placeholder description',
                    'qty'    => 1,
                    'unit'   => 'num',
                    'price'  => 0, // Backend must enforce calculated price (3500 for 80k salary)
                ]
            ]
        ]);

        $response->assertStatus(302);

        $savedCase = Purchase::where('pcs_title', 'Official Trip TA/DA Case')->first();
        $this->assertNotNull($savedCase);
        $this->assertEquals('Rb', $savedCase->pcs_type);

        $savedItem = DB::table('pur.purcaseitems')->where('pci_pcs_id', $savedCase->pcs_id)->first();
        $this->assertNotNull($savedItem);
        $this->assertEquals($empId, $savedItem->pci_emp_id);
        $this->assertEquals(3500, (float)$savedItem->pci_price);
        $this->assertStringContainsString("TA/DA for Mr. Officer Tada Traveler", $savedItem->pci_desc);
        $this->assertStringContainsString("ID: {$empId}", $savedItem->pci_desc);
    }

    /**
     * 10. Test Pt Store requires and persists pcs_frm_id
     */
    public function test_store_pt_case_persists_pcs_frm_id(): void
    {
        $head = DB::table('cen.heads')->first();
        $firm = DB::table('frm.firmz')->first();
        if (!$head || !$firm) {
            $this->markTestSkipped('No budget head or firm found');
        }

        $response = $this->actingAs($this->adminUser)->post(route('purchase.store'), [
            'pcs_title'   => 'Office Supplies Pt Case',
            'pcs_hed_id'  => $head->hed_id,
            'pcs_date'    => now()->toDateString(),
            'pcs_minute'  => 2,
            'pcs_type'    => 'Pt',
            'pcs_frm_id'  => $firm->frm_id,
            'pcs_remarks' => '100% payment upon delivery',
            'items'       => [
                [
                    'desc'  => 'Paper and Toner',
                    'qty'   => 5,
                    'unit'  => 'pack',
                    'price' => 1500,
                ]
            ]
        ]);

        $response->assertStatus(302);

        $savedCase = Purchase::where('pcs_title', 'Office Supplies Pt Case')->first();
        $this->assertNotNull($savedCase);
        $this->assertEquals('Pt', $savedCase->pcs_type);
        $this->assertEquals($firm->frm_id, $savedCase->pcs_frm_id);
        $this->assertEquals('100% payment upon delivery', $savedCase->pcs_remarks);
        $this->assertEquals(7500, (float)$savedCase->pcs_price);
    }

    /**
     * 11. Test Employee List in Rb is Filtered to Division's Active Contracted Employees
     */
    public function test_rb_employee_dropdown_filtered_to_division_active_contracted_employees(): void
    {
        // Create an employee in another division (unit 999999)
        $outsideEmpId = 'OUT-' . rand(1000, 9999);
        DB::table('hr.emps')->insert([
            'emp_id'     => $outsideEmpId,
            'emp_cnic'   => '42000-' . rand(1000000, 9999999) . '-1',
            'emp_name'   => 'Outside Division Person',
            'emp_title'  => 'Mr.',
            'emp_rank'   => 'Officer',
            'emp_status' => 'Active',
            'emp_unt_id' => 999999, // Outside unit
            'emp_joindt' => '2023-01-01',
        ]);

        $divUser = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first() ?? $this->adminUser;
        $divUntId = (int) ($divUser->acc_lowers != 0 ? $divUser->acc_lowers : ($divUser->acc_lowerm != 0 ? $divUser->acc_lowerm : ($divUser->acc_unt_id ?? 1)));

        // Create a division employee
        $divEmpId = $this->createTestEmployee(65000, 'RO', 'Division Insider Person', $divUntId);

        $response = $this->actingAs($divUser)->get(route('purchase.unified.create', 'Rb'));
        $response->assertStatus(200);

        // Division employee should be visible
        $response->assertSee($divEmpId);
        $response->assertSee('Division Insider Person');

        // Outside employee should NOT be visible for division user
        if ($divUser->acc_unt_id && $divUser->acc_untarea === 'prj') {
            $response->assertDontSee($outsideEmpId);
            $response->assertDontSee('Outside Division Person');
        }
    }
}
