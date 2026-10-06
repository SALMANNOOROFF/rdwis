<?php

namespace Tests\Feature;

use App\Http\Middleware\ForcePasswordChange;
use App\Models\CenAccount;
use App\Models\HrCtrCase;
use App\Models\HrForms\CaseForm;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HrFormsShowViewsAndRoutesTest extends TestCase
{
    use DatabaseTransactions;

    protected CenAccount $divUser;
    protected CenAccount $mdUser;
    protected HrCtrCase $testCase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hrforms.enabled', true);
        $this->withoutMiddleware([
            ForcePasswordChange::class,
            ValidateCsrfToken::class,
        ]);

        $this->divUser = CenAccount::where('acc_untarea', 'like', '%prj%')->firstOrFail();
        $this->mdUser = CenAccount::where('acc_untarea', 'rdw')->firstOrFail();

        // Use case 376 (has synchronized HR forms)
        $this->testCase = HrCtrCase::findOrFail(376);
    }

    /**
     * Test: Division and MD case show pages render HR forms section
     * and every form PDF link resolves to hrforms.forms.pdf with %PDF- content.
     */
    public function test_division_and_md_case_show_renders_hrforms_with_valid_pdf_routes(): void
    {
        // 1. Division show page
        $divResp = $this->actingAs($this->divUser)->get(route('division.contract-cases.show', $this->testCase->ctc_id));
        $divResp->assertStatus(200);
        $divResp->assertSee('HR Policy Forms (Policy 2026)');

        // Retrieve active forms for the case
        $forms = CaseForm::where('case_id', $this->testCase->ctc_id)->get();
        $this->assertGreaterThan(0, $forms->count(), 'Test case must contain HR forms.');

        foreach ($forms as $form) {
            $expectedPdfUrl = route('hrforms.forms.pdf', $form->id);
            $divResp->assertSee($expectedPdfUrl, false);

            // Fetch the PDF endpoint directly
            $pdfResp = $this->actingAs($this->divUser)->get($expectedPdfUrl);
            $pdfResp->assertStatus(200);
            $this->assertStringStartsWith('%PDF-', $pdfResp->getContent(), "PDF output for form {$form->form_code} must start with %PDF- header.");
        }

        // 2. MD show page
        $mdResp = $this->actingAs($this->mdUser)->get(route('md.contract-cases.show', $this->testCase->ctc_id));
        $mdResp->assertStatus(200);
        $mdResp->assertSee('HR Policy Forms (Policy 2026)');

        foreach ($forms as $form) {
            $expectedPdfUrl = route('hrforms.forms.pdf', $form->id);
            $mdResp->assertSee($expectedPdfUrl, false);

            $pdfResp = $this->actingAs($this->mdUser)->get($expectedPdfUrl);
            $pdfResp->assertStatus(200);
            $this->assertStringStartsWith('%PDF-', $pdfResp->getContent(), "MD view PDF output for form {$form->form_code} must start with %PDF- header.");
        }
    }

    /**
     * Test: All named route() references in resources/views/hrforms exist.
     */
    public function test_all_named_routes_used_in_hrforms_views_exist(): void
    {
        $namedRoutes = [
            'hrforms.forms.pdf',
            'hrforms.cases.pdf-dossier',
            'hrforms.settings.update',
            'hrforms.settings',
            'hrforms.forms.show',
            'hrforms.forms.update',
            'hrforms.forms.refresh',
            'hrforms.forms.submit',
            'hrforms.forms.pending-action',
            'hrforms.project-extras.update',
            'hrforms.milestones.update',
        ];

        foreach ($namedRoutes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Named route '{$routeName}' used in hrforms views must exist in RouteCollection.");
        }
    }
}
