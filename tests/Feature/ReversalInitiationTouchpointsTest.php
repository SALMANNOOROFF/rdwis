<?php

namespace Tests\Feature;

use App\Enums\RevType;
use App\Models\AudRev;
use App\Models\CenAccount;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReversalInitiationTouchpointsTest extends TestCase
{
    use DatabaseTransactions;

    protected CenAccount $approverUser;
    protected CenAccount $viewerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\ForcePasswordChange::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        // 1. Approver / Admin user with full initiation privileges
        $this->approverUser = CenAccount::where('acc_username', 'superadminrdw')->first()
            ?? CenAccount::where('acc_auth', 'approver')->first();

        // 2. Readonly Viewer user with no initiate permission (Policy Denial)
        $this->viewerUser = CenAccount::where('acc_username', 'mtanveer')->first()
            ?? CenAccount::where('acc_auth', 'viewer')->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 1 & 2: Procurement - Scope 1 & 2 (Purchase Case)
    | Route: POST /purchase/case/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_01_purchase_case_scope_1_reversal_success(): void
    {
        $case = Purchase::whereIn('pcs_status', ['Approved', 'Fulfilled', 'Partially Fulfilled'])->first();
        if (!$case) {
            $case = Purchase::create([
                'pcs_title'  => 'Test Purchase Case',
                'pcs_type'   => 'Ps',
                'pcs_status' => 'Fulfilled',
                'pcs_unt_id' => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_date'   => now()->toDateString(),
            ]);
        }

        $response = $this->actingAs($this->approverUser)->post(route('purchase.case.reverse', $case->pcs_id), [
            'rev_type'   => 1,
            'rev_reason' => 'Scope 1 Full Cascade Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Purchase Case')
            ->where('rev_objid', (string) $case->pcs_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_01_purchase_case_scope_1_reversal_denied_for_viewer(): void
    {
        $case = Purchase::first() ?? Purchase::create([
            'pcs_title' => 'Test Case', 'pcs_type' => 'Ps', 'pcs_status' => 'Fulfilled', 'pcs_unt_id' => 860000,
        ]);

        $response = $this->actingAs($this->viewerUser)->post(route('purchase.case.reverse', $case->pcs_id), [
            'rev_type'   => 1,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_02_purchase_case_scope_2_field_reversal_success(): void
    {
        $case = Purchase::whereIn('pcs_status', ['Approved', 'Fulfilled', 'Partially Fulfilled'])->first();
        if (!$case) {
            $case = Purchase::create([
                'pcs_title'  => 'Test Purchase Case 2',
                'pcs_type'   => 'Ps',
                'pcs_status' => 'Fulfilled',
                'pcs_unt_id' => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_date'   => now()->toDateString(),
            ]);
        }

        $response = $this->actingAs($this->approverUser)->post(route('purchase.case.reverse', $case->pcs_id), [
            'rev_type'    => 2,
            'rev_reason'  => 'Scope 2 Field Revision Test',
            'field_diffs' => [
                [
                    'table'    => 'pur_purcases',
                    'rowid'    => (string) $case->pcs_id,
                    'attrib'   => 'pcs_remarks',
                    'colname'  => 'pcs_remarks',
                    'oldvalue' => 'Old Remarks',
                    'newvalue' => 'New Remarks',
                    'datatype' => 'Text',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Purchase Case')
            ->where('rev_objid', (string) $case->pcs_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_02_purchase_case_scope_2_field_reversal_denied_for_viewer(): void
    {
        $case = Purchase::first();
        $response = $this->actingAs($this->viewerUser)->post(route('purchase.case.reverse', $case->pcs_id), [
            'rev_type'   => 2,
            'rev_reason' => 'Unauthorized viewer attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 3: Procurement - Petty Cash Case
    | Route: POST /purchase/petty/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_03_purchase_petty_cash_reversal_success(): void
    {
        $petty = Purchase::where('pcs_type', 'Pt')->first();
        if (!$petty) {
            $petty = Purchase::create([
                'pcs_title'  => 'Test Petty Cash Case',
                'pcs_type'   => 'Pt',
                'pcs_status' => 'Fulfilled',
                'pcs_unt_id' => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_date'   => now()->toDateString(),
            ]);
        }

        $response = $this->actingAs($this->approverUser)->post(route('purchase.petty.reverse', $petty->pcs_id), [
            'rev_reason' => 'Petty Cash Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Purchase Case')
            ->where('rev_objid', (string) $petty->pcs_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_03_purchase_petty_cash_reversal_denied_for_viewer(): void
    {
        $petty = Purchase::where('pcs_type', 'Pt')->first() ?? Purchase::create([
            'pcs_title' => 'Petty Case', 'pcs_type' => 'Pt', 'pcs_status' => 'Fulfilled', 'pcs_unt_id' => 860000,
        ]);

        $response = $this->actingAs($this->viewerUser)->post(route('purchase.petty.reverse', $petty->pcs_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 4: Procurement - TA/DA Case
    | Route: POST /purchase/tada/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_04_purchase_tada_reversal_success(): void
    {
        $head = DB::table('cen.heads')->first();
        $tada = Purchase::where('pcs_type', 'Td')->first();
        if (!$tada) {
            $tada = Purchase::create([
                'pcs_title'     => 'Test TA/DA Case',
                'pcs_type'      => 'Td',
                'pcs_status'    => 'Fulfilled',
                'pcs_unt_id'    => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_effunt_id' => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_intunt_id' => $this->approverUser->acc_unt_id ?? 860000,
                'pcs_transtype' => 1,
                'pcs_date'      => now()->toDateString(),
                'pcs_effhed_id' => $head->hed_id ?? 100000,
                'pcs_hed_id'    => $head->hed_id ?? 100000,
            ]);
        }

        $response = $this->actingAs($this->approverUser)->post(route('purchase.tada.reverse', $tada->pcs_id), [
            'rev_reason' => 'TA/DA Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Purchase Case')
            ->where('rev_objid', (string) $tada->pcs_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_04_purchase_tada_reversal_denied_for_viewer(): void
    {
        $head = DB::table('cen.heads')->first();
        $tada = Purchase::where('pcs_type', 'Td')->first() ?? Purchase::create([
            'pcs_title'     => 'TADA Case',
            'pcs_type'      => 'Td',
            'pcs_status'    => 'Fulfilled',
            'pcs_unt_id'    => 860000,
            'pcs_effunt_id' => 860000,
            'pcs_intunt_id' => 860000,
            'pcs_transtype' => 1,
            'pcs_date'      => now()->toDateString(),
            'pcs_effhed_id' => $head->hed_id ?? 100000,
            'pcs_hed_id'    => $head->hed_id ?? 100000,
        ]);

        $response = $this->actingAs($this->viewerUser)->post(route('purchase.tada.reverse', $tada->pcs_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 5: Finance - Commitment
    | Route: POST /finance/payments/commitments/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_05_finance_commitment_reversal_success(): void
    {
        $cmt = DB::table('fin.commitments')->first();
        $this->assertNotNull($cmt, 'fin.commitments table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('finance.payments.commitments.reverse', $cmt->cmt_id), [
            'rev_reason' => 'Commitment Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Commitment')
            ->where('rev_objid', (string) $cmt->cmt_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_05_finance_commitment_reversal_denied_for_viewer(): void
    {
        $cmt = DB::table('fin.commitments')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance.payments.commitments.reverse', $cmt->cmt_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 6: Finance - Payment Transaction (RevType 3 / LINKED_CASCADE)
    | Route: POST /finance/payments/transactions/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_06_finance_payment_reversal_success(): void
    {
        $trn = DB::table('fin.transactions')->first();
        $this->assertNotNull($trn, 'fin.transactions table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('finance.payments.transactions.reverse', $trn->trn_id), [
            'rev_reason' => 'Payment Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Payment')
            ->where('rev_objid', (string) $trn->trn_id)
            ->where('rev_type', 3) // RevType 3
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_06_finance_payment_reversal_denied_for_viewer(): void
    {
        $trn = DB::table('fin.transactions')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance.payments.transactions.reverse', $trn->trn_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 7: Finance - Salary Order
    | Route: POST /salary/orders/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_07_finance_salary_order_reversal_success(): void
    {
        $sor = DB::table('fin.salorders')->first();
        $this->assertNotNull($sor, 'fin.salorders table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('salary.orders.reverse', $sor->sor_id), [
            'rev_reason' => 'Salary Order Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Salary Order')
            ->where('rev_objid', (string) $sor->sor_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_07_finance_salary_order_reversal_denied_for_viewer(): void
    {
        $sor = DB::table('fin.salorders')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('salary.orders.reverse', $sor->sor_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 8: Finance - Salary Requisition (via linked Salary Order)
    | Route: POST /salary/requisitions/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_08_finance_salary_requisition_reversal_success(): void
    {
        // Find or link a requisition to a salary order
        $srq = DB::table('hr.salreqs')->first();
        $this->assertNotNull($srq, 'hr.salreqs table must have at least one record.');

        // Ensure a linked fin.salorders record exists
        $order = DB::table('fin.salorders')->where('sor_srq_id', $srq->srq_id)->first();
        if (!$order) {
            DB::table('fin.salorders')->where('sor_id', 191)->update(['sor_srq_id' => $srq->srq_id]);
        }

        $response = $this->actingAs($this->approverUser)->post(route('salary.requisitions.reverse', $srq->srq_id), [
            'rev_reason' => 'Salary Requisition Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Salary Order')
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_08_finance_salary_requisition_reversal_denied_for_viewer(): void
    {
        $srq = DB::table('hr.salreqs')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('salary.requisitions.reverse', $srq->srq_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 9: Finance - Allocation (fin_sharesalloc, RevType 3)
    | Route: POST /finance/allocations/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_09_finance_allocation_reversal_success(): void
    {
        $alloc = DB::table('fin.sharesalloc')->first();
        $this->assertNotNull($alloc, 'fin.sharesalloc table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('finance.allocations.reverse', $alloc->sha_id), [
            'rev_reason' => 'Allocation Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Allocation')
            ->where('rev_objid', (string) $alloc->sha_id)
            ->where('rev_type', 3) // RevType 3
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_09_finance_allocation_reversal_denied_for_viewer(): void
    {
        $alloc = DB::table('fin.sharesalloc')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance.allocations.reverse', $alloc->sha_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 10: Finance - Funding (fin_sharesinstall, RevType 3)
    | Route: POST /finance-of-project/funding/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_10_finance_funding_reversal_success(): void
    {
        $fund = DB::table('fin.sharesinstall')->first();
        $this->assertNotNull($fund, 'fin.sharesinstall table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('finance_of_project.funding.reverse', $fund->shi_id), [
            'rev_reason' => 'Funding Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Funding')
            ->where('rev_objid', (string) $fund->shi_id)
            ->where('rev_type', 3) // RevType 3
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_10_finance_funding_reversal_denied_for_viewer(): void
    {
        $fund = DB::table('fin.sharesinstall')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance_of_project.funding.reverse', $fund->shi_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 11: Finance - Milestone Cost (fin_msncosts, RevType 2)
    | Route: POST /finance/account-opening/milestones/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_11_finance_milestone_cost_reversal_success(): void
    {
        $cost = DB::table('fin.msncosts')->first();
        $this->assertNotNull($cost, 'fin.msncosts table must have at least one record.');
        $costId = $cost->mct_msn_idd ?? $cost->mct_id ?? 312;

        $response = $this->actingAs($this->approverUser)->post(route('finance.account_opening.milestones.reverse', $costId), [
            'rev_reason'  => 'Milestone Cost Field Revision Test',
            'field_diffs' => [
                [
                    'table'    => 'fin_msncosts',
                    'rowid'    => (string) $costId,
                    'attrib'   => 'mct_cost',
                    'colname'  => 'mct_cost',
                    'oldvalue' => '4000000',
                    'newvalue' => '4500000',
                    'datatype' => 'Currency',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Milestone Cost')
            ->where('rev_objid', (string) $costId)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_11_finance_milestone_cost_reversal_denied_for_viewer(): void
    {
        $cost = DB::table('fin.msncosts')->first();
        $costId = $cost->mct_msn_idd ?? $cost->mct_id ?? 312;

        $response = $this->actingAs($this->viewerUser)->post(route('finance.account_opening.milestones.reverse', $costId), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_11_finance_milestone_cost_reversal_denied_for_division_user_outside_unit(): void
    {
        $cost = DB::table('fin.msncosts as mc')
            ->leftJoin('prj.milestones as m', 'mc.mct_msn_idd', '=', 'm.msn_idd')
            ->leftJoin('prj.projects as p', 'm.msn_xprj_id', '=', 'p.prj_id')
            ->select('mc.*', 'p.prj_unt_id')
            ->first();
        $this->assertNotNull($cost, 'fin.msncosts table must have at least one record.');
        $costId = $cost->mct_msn_idd ?? $cost->mct_id;

        $divisionUser = clone $this->approverUser;
        $divisionUser->acc_username = 'div_out_of_scope_user';
        $divisionUser->acc_untarea = 'prj';
        $divisionUser->acc_access = 'single';
        $divisionUser->acc_unt_id = 999999;
        $divisionUser->acc_lowers = 999999;
        $divisionUser->acc_uppers = 999999;
        $divisionUser->acc_lowerm = 0;
        $divisionUser->acc_upperm = 0;

        $response = $this->actingAs($divisionUser)->post(route('finance.account_opening.milestones.reverse', $costId), [
            'rev_reason' => 'Division user out of unit scope attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 12 & 13: Finance - Account Head (Open & Closed)
    | Route: POST /finance/account-opening/heads/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_12_finance_open_account_head_reversal_success(): void
    {
        $head = DB::table('cen.heads')->whereNull('hed_closedt')->first()
            ?? DB::table('cen.heads')->first();

        $response = $this->actingAs($this->approverUser)->post(route('finance.account_opening.heads.reverse', $head->hed_id), [
            'rev_type'   => 2,
            'rev_reason' => 'Open Head Field Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Account')
            ->where('rev_objid', (string) $head->hed_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_12_finance_open_account_head_reversal_denied_for_viewer(): void
    {
        $head = DB::table('cen.heads')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance.account_opening.heads.reverse', $head->hed_id), [
            'rev_type'   => 2,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_13_finance_closed_account_head_reversal_success(): void
    {
        $head = DB::table('cen.heads')->first();

        $response = $this->actingAs($this->approverUser)->post(route('finance.account_opening.heads.reverse', $head->hed_id), [
            'rev_type'    => 2,
            'rev_reason'  => 'Closed Head Field Revision Test',
            'field_diffs' => [
                [
                    'table'    => 'cen_heads',
                    'rowid'    => (string) $head->hed_id,
                    'attrib'   => 'hed_name',
                    'colname'  => 'hed_name',
                    'oldvalue' => $head->hed_name,
                    'newvalue' => $head->hed_name . ' (Revised)',
                    'datatype' => 'Text',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Account')
            ->where('rev_objid', (string) $head->hed_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_13_finance_closed_account_head_reversal_denied_for_viewer(): void
    {
        $head = DB::table('cen.heads')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('finance.account_opening.heads.reverse', $head->hed_id), [
            'rev_type'   => 2,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 14 & 15: HR - Contract Full & Field
    | Route: POST /contract-cases/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_14_hr_contract_full_reversal_success(): void
    {
        $contract = DB::table('hr.contracts')->first();
        $this->assertNotNull($contract, 'hr.contracts table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('contract_cases.reverse', $contract->ctr_id), [
            'rev_type'   => 1,
            'rev_reason' => 'Contract Full Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Contract')
            ->where('rev_objid', (string) $contract->ctr_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_14_hr_contract_full_reversal_denied_for_viewer(): void
    {
        $contract = DB::table('hr.contracts')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('contract_cases.reverse', $contract->ctr_id), [
            'rev_type'   => 1,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_15_hr_contract_field_reversal_success(): void
    {
        $contract = DB::table('hr.contracts')->first();

        $response = $this->actingAs($this->approverUser)->post(route('contract_cases.reverse', $contract->ctr_id), [
            'rev_type'    => 2,
            'rev_reason'  => 'Contract Field Revision Test',
            'field_diffs' => [
                [
                    'table'    => 'hr_contracts',
                    'rowid'    => (string) $contract->ctr_id,
                    'attrib'   => 'ctr_salary',
                    'colname'  => 'ctr_salary',
                    'oldvalue' => '50000',
                    'newvalue' => '55000',
                    'datatype' => 'Currency',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Contract')
            ->where('rev_objid', (string) $contract->ctr_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_15_hr_contract_field_reversal_denied_for_viewer(): void
    {
        $contract = DB::table('hr.contracts')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('contract_cases.reverse', $contract->ctr_id), [
            'rev_type'   => 2,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 16: HR - Contract Plan (RevType 2)
    | Route: POST /contract-cases/plans/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_16_hr_contract_plan_reversal_success(): void
    {
        $plan = DB::table('hr.contractplans')->first();
        $this->assertNotNull($plan, 'hr.contractplans table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('contract_cases.plans.reverse', $plan->cpn_id), [
            'rev_reason'  => 'Contract Plan Revision Test',
            'field_diffs' => [
                [
                    'table'    => 'hr_contractplans',
                    'rowid'    => (string) $plan->cpn_id,
                    'attrib'   => 'cpn_rate',
                    'colname'  => 'cpn_rate',
                    'oldvalue' => '2000',
                    'newvalue' => '2200',
                    'datatype' => 'Currency',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Contract Plan')
            ->where('rev_objid', (string) $plan->cpn_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_16_hr_contract_plan_reversal_denied_for_viewer(): void
    {
        $plan = DB::table('hr.contractplans')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('contract_cases.plans.reverse', $plan->cpn_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 17 & 18: HR - Employee Full & Field
    | Route: POST /divhr/employees/{id}/reverse-full
    | Route: POST /divhr/employees/{id}/reverse-field
    |--------------------------------------------------------------------------
    */

    public function test_17_hr_employee_full_reversal_success(): void
    {
        $emp = DB::table('hr.emps')->first();
        $this->assertNotNull($emp, 'hr.emps table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('divhr.employees.reverse-full', $emp->emp_id), [
            'rev_reason' => 'Employee Full Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Employee')
            ->where('rev_objid', (string) $emp->emp_id)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_17_hr_employee_full_reversal_denied_for_viewer(): void
    {
        $emp = DB::table('hr.emps')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('divhr.employees.reverse-full', $emp->emp_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_18_hr_employee_field_reversal_success(): void
    {
        $emp = DB::table('hr.emps')->first();

        $response = $this->actingAs($this->approverUser)->post(route('divhr.employees.reverse-field', $emp->emp_id), [
            'field_name' => 'emp_title',
            'old_value'  => $emp->emp_title ?? 'Senior Dev',
            'new_value'  => 'Lead Engineer',
            'rev_reason' => 'Employee Title Correction Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Employee')
            ->where('rev_objid', (string) $emp->emp_id)
            ->where('rev_type', 2)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_18_hr_employee_field_reversal_denied_for_viewer(): void
    {
        $emp = DB::table('hr.emps')->first();
        $response = $this->actingAs($this->viewerUser)->post(route('divhr.employees.reverse-field', $emp->emp_id), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 19 & 20: HR - Monthly & Daily Attendance (RevType 2)
    | Route: POST /attendance/monthly/{id}/reverse
    | Route: POST /attendance/daily/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_19_hr_monthly_attendance_reversal_success(): void
    {
        $att = DB::table('hr.attendance')->first();
        $this->assertNotNull($att, 'hr.attendance table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('attendance.monthly.reverse', $att->att_id), [
            'rev_reason'  => 'Monthly Attendance Reversal Test',
            'month'       => '2021-07',
            'field_diffs' => [
                [
                    'table'    => 'cen_attendance',
                    'rowid'    => (string) $att->att_id,
                    'attrib'   => 'att_present',
                    'colname'  => 'att_present',
                    'oldvalue' => '20',
                    'newvalue' => '22',
                    'datatype' => 'Integer',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Attendance')
            ->where('rev_type', 2)
            ->where('rev_objid', (string) $att->att_id)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_19_hr_monthly_attendance_reversal_denied_for_viewer(): void
    {
        $att = DB::table('hr.attendance')->first();
        $attId = $att->att_id ?? 1363;

        $response = $this->actingAs($this->viewerUser)->post(route('attendance.monthly.reverse', $attId), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_19_hr_monthly_attendance_reversal_missing_att_id_returns_422(): void
    {
        $response = $this->actingAs($this->approverUser)->post(route('attendance.reverse_monthly'), [
            'rev_reason' => 'Missing att_id test',
        ]);

        $response->assertStatus(422);
    }

    public function test_19_hr_attendance_cannot_bypass_unit_scope_by_spoofing_unit_id(): void
    {
        $att = DB::table('hr.attendance')->where('att_unt_id', '!=', 350000)->first();
        $this->assertNotNull($att, 'hr.attendance must have a record outside unit 350000.');

        $divisionUser = clone $this->approverUser;
        $divisionUser->acc_username = 'div_scope_test_user';
        $divisionUser->acc_untarea = 'prj';
        $divisionUser->acc_access = 'single';
        $divisionUser->acc_unt_id = 350000;
        $divisionUser->acc_lowers = 350000;
        $divisionUser->acc_uppers = 350000;
        $divisionUser->acc_lowerm = 0;
        $divisionUser->acc_upperm = 0;

        // Caller attempts to spoof unit_id = 350000 in the payload
        $response = $this->actingAs($divisionUser)->post(route('attendance.monthly.reverse', $att->att_id), [
            'unit_id'    => 350000,
            'rev_reason' => 'Attempting to reverse another unit attendance with spoofed unit_id',
        ]);

        $response->assertStatus(403);
    }

    public function test_20_hr_daily_attendance_reversal_success(): void
    {
        $att = DB::table('hr.attendance')->first();
        $this->assertNotNull($att, 'hr.attendance table must have at least one record.');

        $response = $this->actingAs($this->approverUser)->post(route('attendance.daily.reverse', $att->att_id), [
            'rev_reason'  => 'Daily Attendance Reversal Test',
            'date'        => '2021-07-15',
            'field_diffs' => [
                [
                    'table'    => 'cen_attendance',
                    'rowid'    => (string) $att->att_id,
                    'attrib'   => 'att_status',
                    'colname'  => 'att_status',
                    'oldvalue' => 'Absent',
                    'newvalue' => 'Present',
                    'datatype' => 'Text',
                    'type'     => 1,
                ],
            ],
        ]);

        $rev = AudRev::where('rev_obj', 'Attendance')
            ->where('rev_type', 2)
            ->where('rev_objid', (string) $att->att_id)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_20_hr_daily_attendance_reversal_denied_for_viewer(): void
    {
        $att = DB::table('hr.attendance')->first();
        $attId = $att->att_id ?? 1363;

        $response = $this->actingAs($this->viewerUser)->post(route('attendance.daily.reverse', $attId), [
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Touchpoint 21: Projects - Milestone / Task (msn_idd, RevType 1)
    | Route: POST /projects/milestones/{id}/reverse
    |--------------------------------------------------------------------------
    */

    public function test_21_projects_milestone_reversal_success(): void
    {
        $msn = DB::table('prj.milestones')->first();
        $this->assertNotNull($msn, 'prj.milestones table must have at least one record.');
        $msnId = $msn->msn_idd ?? $msn->msn_id;

        $response = $this->actingAs($this->approverUser)->post(route('projects.milestones.reverse', $msnId), [
            'rev_type'   => 1,
            'rev_reason' => 'Milestone Task Cascade Reversal Test',
        ]);

        $rev = AudRev::where('rev_obj', 'Task')
            ->where('rev_objid', (string) $msnId)
            ->where('rev_type', 1)
            ->orderBy('rev_id', 'desc')
            ->first();

        $this->assertNotNull($rev);
        $this->assertSame('Draft', $rev->rev_status);
        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
    }

    public function test_21_projects_milestone_reversal_denied_for_viewer(): void
    {
        $msn = DB::table('prj.milestones')->first();
        $msnId = $msn->msn_idd ?? $msn->msn_id;

        $response = $this->actingAs($this->viewerUser)->post(route('projects.milestones.reverse', $msnId), [
            'rev_type'   => 1,
            'rev_reason' => 'Unauthorized attempt',
        ]);

        $response->assertStatus(403);
    }
}
