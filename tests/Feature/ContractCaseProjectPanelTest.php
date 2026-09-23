<?php

namespace Tests\Feature;

use App\Http\Middleware\ForcePasswordChange;
use App\Models\CenAccount;
use App\Models\HrCtrCase;
use App\Services\ContractCaseProjectService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContractCaseProjectPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Existing legacy schema is required, but these checks must never modify business data.
        DB::beginTransaction();
        DB::statement('SET TRANSACTION READ ONLY');
        $this->withoutMiddleware(ForcePasswordChange::class);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function caseWithProjects(): HrCtrCase
    {
        $id = DB::table('hr.ctrcaseplans')->select('ccp_ctc_id')
            ->whereNotNull('ccp_hed_id')->groupBy('ccp_ctc_id')
            ->orderByRaw('count(distinct ccp_hed_id) desc')->value('ccp_ctc_id');
        $this->assertNotNull($id, 'A contract case with allocations is required.');
        return HrCtrCase::with('casePlans')->findOrFail($id);
    }

    private function signIn(): void
    {
        $user = CenAccount::where('acc_status', 'Active')->where('acc_untarea', 'nrdi')->firstOrFail();
        $this->actingAs($user);
    }

    public function test_each_allocated_project_has_its_own_ajax_panels(): void
    {
        $this->signIn();
        $case = $this->caseWithProjects();
        $allocations = app(ContractCaseProjectService::class)->allocations($case);
        $this->assertGreaterThan(1, $allocations->count());
        foreach ($allocations as $allocation) {
            foreach (['financial', 'attachments', 'milestones'] as $section) {
                $response = $this->get(route('contract-cases.project-panel', [$case->ctc_id, $allocation->hed_id]).'?section='.$section);
                $response->assertOk();
                $response->assertJsonStructure(['allocation', 'section', 'financial', 'attachments', 'milestones']);
            }
        }
    }

    public function test_unallocated_project_and_invalid_section_are_rejected(): void
    {
        $this->signIn();
        $case = $this->caseWithProjects();
        $allocation = app(ContractCaseProjectService::class)->allocations($case)->first();
        $this->get(route('contract-cases.project-panel', [$case->ctc_id, 2147483647]))->assertNotFound();
        $this->get(route('contract-cases.project-panel', [$case->ctc_id, $allocation->hed_id]).'?section=invalid')->assertStatus(422);
    }

    public function test_guest_cannot_read_project_financial_data(): void
    {
        $this->get(route('contract-cases.project-panel', [1, 1]))->assertRedirect(route('login'));
    }

    public function test_shared_contract_view_renders_all_projects_and_hired_counts(): void
    {
        $this->signIn();
        $case = $this->caseWithProjects();
        $response = $this->get(route('dg.contract-cases.show', $case->ctc_id));
        $response->assertOk()
            ->assertSee('ALLOCATED PROJECTS')
            ->assertSee('FINANCIAL REVIEW')
            ->assertSee('ALREADY HIRED STAFF')
            ->assertSee('selectProject');
        $allocations = app(ContractCaseProjectService::class)->allocations($case);
        foreach ($allocations as $allocation) {
            $response->assertSee($allocation->prj_code ?: $allocation->hed_code);
        }
    }
}
