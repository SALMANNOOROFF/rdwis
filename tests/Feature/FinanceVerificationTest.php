<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckArea;
use App\Models\CenAccount;
use App\Models\CenHead;
use App\Models\HrContract;
use App\Models\HrEmployee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinanceVerificationTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $finUser;
    protected $hrUser;
    protected $prjUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finUser = CenAccount::where('acc_untarea', 'fin')->where('acc_status', 'Active')->first();
        if (!$this->finUser) {
            $this->finUser = CenAccount::first();
            $this->finUser->acc_untarea = 'fin';
            $this->finUser->save();
        }

        $this->hrUser = CenAccount::where('acc_untarea', 'hr')->where('acc_status', 'Active')->first();
        if (!$this->hrUser) {
            $this->hrUser = CenAccount::where('acc_id', '!=', $this->finUser->acc_id)->first();
            $this->hrUser->acc_untarea = 'hr';
            $this->hrUser->save();
        }

        $this->prjUser = CenAccount::where('acc_untarea', 'prj')->where('acc_status', 'Active')->first();
        if (!$this->prjUser) {
            $this->prjUser = CenAccount::where('acc_id', '!=', $this->finUser->acc_id)->first();
            $this->prjUser->acc_untarea = 'prj';
            $this->prjUser->save();
        }
    }

    public function test_non_finance_users_are_denied_access(): void
    {
        $middleware = [CheckArea::class];

        $responseHr = $this->withMiddleware($middleware)->actingAs($this->hrUser)->get(route('fin.verification.contracts.index'));
        $responseHr->assertStatus(403);

        $responsePrj = $this->withMiddleware($middleware)->actingAs($this->prjUser)->get(route('fin.verification.salary-heads.index'));
        $responsePrj->assertStatus(403);

        $responseFin = $this->withMiddleware($middleware)->actingAs($this->finUser)->get(route('fin.verification.contracts.index'));
        $responseFin->assertStatus(200);
    }

    public function test_finance_user_can_access_salary_verification_screens(): void
    {
        $responseUnverified = $this->actingAs($this->finUser)->get(route('fin.verification.contracts.index', ['tab' => 'unverified']));
        $responseUnverified->assertStatus(200);
        $responseUnverified->assertSee('Salary Verification');
        $responseUnverified->assertSee('Verified Contract Salaries');

        $responseVerified = $this->actingAs($this->finUser)->get(route('fin.verification.contracts.index', ['tab' => 'verified']));
        $responseVerified->assertStatus(200);
        $responseVerified->assertSee('Salary Verification');
        $responseVerified->assertSee('Unverified Contract Salaries');
    }

    public function test_finance_user_can_verify_contract(): void
    {
        // Find or create test contract
        $contract = HrContract::first();
        $this->assertNotNull($contract);

        // Ensure initially unverified
        DB::table('fin.contractsverif')->updateOrInsert(
            ['cvf_ctr_id' => $contract->ctr_id],
            ['cvf_verif' => false, 'cvf_dtg' => null]
        );

        $response = $this->actingAs($this->finUser)->postJson(route('fin.verification.contracts.verify', $contract->ctr_id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $verifiedRow = DB::table('fin.contractsverif')->where('cvf_ctr_id', $contract->ctr_id)->first();
        $this->assertTrue((bool)$verifiedRow->cvf_verif);
        $this->assertNotNull($verifiedRow->cvf_dtg);
    }

    public function test_finance_user_can_access_salary_heads_screens(): void
    {
        $responseOpen = $this->actingAs($this->finUser)->get(route('fin.verification.salary-heads.index', ['tab' => 'open']));
        $responseOpen->assertStatus(200);
        $responseOpen->assertSee('Salary Heads Assignment');

        $responseClosed = $this->actingAs($this->finUser)->get(route('fin.verification.salary-heads.index', ['tab' => 'closed']));
        $responseClosed->assertStatus(200);
        $responseClosed->assertSee('Salary Heads Assignment');
    }

    public function test_finance_user_can_update_salary_head_and_autostamp_sudohead(): void
    {
        $emp = HrEmployee::first();
        $this->assertNotNull($emp);

        $head = CenHead::first();
        $this->assertNotNull($head);

        DB::table('fin.empeffheads')->updateOrInsert(
            ['eeh_emp_id' => $emp->emp_id],
            ['eeh_status' => 'Open', 'eeh_emphed_id' => null, 'eeh_sudohed' => null]
        );

        $response = $this->actingAs($this->finUser)->postJson(route('fin.verification.salary-heads.update', $emp->emp_id), [
            'eeh_emphed_id' => $head->hed_id,
            'eeh_remarks'   => 'Automated test assignment'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $updated = DB::table('fin.empeffheads')->where('eeh_emp_id', $emp->emp_id)->first();
        $this->assertEquals($head->hed_id, $updated->eeh_emphed_id);
        $this->assertEquals('CHRF', $updated->eeh_sudohed);
        $this->assertEquals('Automated test assignment', $updated->eeh_remarks);
        $this->assertNotNull($updated->eeh_dtg);
    }

    public function test_finance_user_can_close_salary_head_via_dedicated_endpoint(): void
    {
        $emp = HrEmployee::first();
        $this->assertNotNull($emp);

        // Ensure record is Open
        DB::table('fin.empeffheads')->updateOrInsert(
            ['eeh_emp_id' => $emp->emp_id],
            ['eeh_status' => 'Open', 'eeh_emphed_id' => 100000]
        );

        $response = $this->actingAs($this->finUser)->postJson(route('fin.verification.salary-heads.close', $emp->emp_id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $closed = DB::table('fin.empeffheads')->where('eeh_emp_id', $emp->emp_id)->first();
        $this->assertEquals('Closed', $closed->eeh_status);
        $this->assertNotNull($closed->eeh_dtg);
    }

    public function test_finance_user_can_initiate_reverse_for_closed_head(): void
    {
        $emp = HrEmployee::first();
        $this->assertNotNull($emp);

        // Transition record to Closed via close endpoint (UI flow)
        DB::table('fin.empeffheads')->updateOrInsert(
            ['eeh_emp_id' => $emp->emp_id],
            ['eeh_status' => 'Open', 'eeh_emphed_id' => 100000]
        );
        $this->actingAs($this->finUser)->postJson(route('fin.verification.salary-heads.close', $emp->emp_id));

        $response = $this->actingAs($this->finUser)->postJson(route('fin.verification.salary-heads.reverse', $emp->emp_id), [
            'rev_reason' => 'Audit reversal test'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertStringContainsString('/admin/reversals/', $response->json('redirect_url'));

        // Check aud.revs draft created
        $rev = DB::table('aud.revs')->where('rev_obj', 'Salary Head')->where('rev_objid', (string)$emp->emp_id)->latest('rev_id')->first();
        $this->assertNotNull($rev);
        $this->assertEquals('Draft', $rev->rev_status);
    }
}
