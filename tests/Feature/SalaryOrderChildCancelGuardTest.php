<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\FinSalOrder;
use App\Services\SalaryGenerationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SalaryOrderChildCancelGuardTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected function createOrderHierarchy(): array
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();

        $baseData = [
            'sor_month' => '2026-09-01',
            'sor_unt_id' => $unit->unt_id,
            'sor_hed_id' => $head->hed_id,
            'sor_effhed_id' => $head->hed_id,
            'sor_effunt_id' => $unit->unt_id,
            'sor_srq_id' => 0,
            'sor_status' => 'Pending',
            'sor_type' => 'Sa',
            'sor_transtype' => 1,
            'sor_netsalary' => 50000,
            'sor_salary' => 50000,
            'sor_emp_id' => '00-00-00-0000',
            'sor_empnamecomp' => 'Test Employee',
            'sor_bnkacctitle' => 'Test Title',
            'sor_bnkaccdetail' => '12345678',
            'sor_ctrsalary' => 0,
            'sor_checked' => false,
            'sor_contracts' => 'NK',
            'sor_noloan' => false,
            'sor_grosalary' => 50000,
            'sor_arrears' => 0,
            'sor_dues' => 0,
            'sor_overwork' => 0,
            'sor_underwork' => 0,
            'sor_loaned' => 0,
            'sor_withheld' => 0,
            'sor_award' => 0,
            'sor_penalty' => 0,
            'sor_paidalready' => 0,
        ];

        $parent = FinSalOrder::create(array_merge($baseData, [
            'sor_parent' => null,
        ]));

        $child = FinSalOrder::create(array_merge($baseData, [
            'sor_parent' => $parent->sor_id,
        ]));

        return [$parent, $child];
    }

    public function test_cancelling_child_order_directly_is_rejected_with_422_and_leaves_state_unchanged()
    {
        [$parent, $child] = $this->createOrderHierarchy();
        $service = app(SalaryGenerationService::class);

        $caughtException = null;
        try {
            $service->cancelOrder($child->sor_id);
        } catch (HttpException $e) {
            $caughtException = $e;
        }

        $this->assertNotNull($caughtException, 'Expected HttpException was not thrown.');
        $this->assertEquals(422, $caughtException->getStatusCode());
        $this->assertEquals(
            'This requisition cannot be cancelled directly. Please cancel the parent requisition.',
            $caughtException->getMessage()
        );

        // Assert state is untouched
        $freshChild = FinSalOrder::findOrFail($child->sor_id);
        $this->assertEquals('Pending', $freshChild->sor_status);
        $this->assertNull($freshChild->sor_closedtg);

        $freshParent = FinSalOrder::findOrFail($parent->sor_id);
        $this->assertEquals('Pending', $freshParent->sor_status);
    }

    public function test_cancelling_child_order_via_http_endpoint_returns_422()
    {
        [$parent, $child] = $this->createOrderHierarchy();

        $user = CenAccount::first();
        $user->acc_desigtype = 'superadmin';
        $user->acc_unt_id = $child->sor_unt_id;
        $user->acc_lowers = 0;
        $user->acc_uppers = 999999;

        $response = $this->actingAs($user)
            ->postJson(route('divhr.salary.orders.cancel', $child->sor_id), [
                'reason' => 'Testing child order direct cancellation rejection',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'This requisition cannot be cancelled directly. Please cancel the parent requisition.',
        ]);

        // Assert no state changes
        $freshChild = FinSalOrder::findOrFail($child->sor_id);
        $this->assertEquals('Pending', $freshChild->sor_status);
    }

    public function test_cancelling_parent_order_succeeds_and_cancels_full_group()
    {
        [$parent, $child] = $this->createOrderHierarchy();
        $service = app(SalaryGenerationService::class);

        $result = $service->cancelOrder($parent->sor_id);

        $this->assertIsArray($result);

        $freshParent = FinSalOrder::findOrFail($parent->sor_id);
        $this->assertEquals('Cancelled', $freshParent->sor_status);
        $this->assertNotNull($freshParent->sor_closedtg);

        $freshChild = FinSalOrder::findOrFail($child->sor_id);
        $this->assertEquals('Cancelled', $freshChild->sor_status);
        $this->assertNotNull($freshChild->sor_closedtg);
    }
}
