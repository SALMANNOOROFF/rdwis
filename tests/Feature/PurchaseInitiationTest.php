<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseInitiationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_division_user_can_view_draft_case()
    {
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first();
        $head = DB::table('cen.heads')->first();

        if (!$user || !$head) {
            $this->markTestSkipped('No prj user or head found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_unt_id = $user->acc_unt_id;
        $purchase->pcs_intunt_id = $user->acc_unt_id;
        $purchase->pcs_effunt_id = $user->acc_unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_title = 'Test Purchase Case';
        $purchase->pcs_date = now()->toDateString();
        $purchase->save();

        $response = $this->actingAs($user)->get(route('purchase.initiation.show', $purchase->pcs_id));

        $response->assertStatus(200);
        $response->assertSee('Test Purchase Case');
        $response->assertSee('EDITING MODE');
    }

    public function test_can_add_item_to_draft_case()
    {
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first();
        $head = DB::table('cen.heads')->first();

        if (!$user || !$head) {
            $this->markTestSkipped('No prj user or head found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_title = 'Draft Case for Item Add';
        $purchase->pcs_unt_id = $user->acc_unt_id;
        $purchase->pcs_intunt_id = $user->acc_unt_id;
        $purchase->pcs_effunt_id = $user->acc_unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_date = now()->toDateString();
        $purchase->save();

        $response = $this->actingAs($user)->post(route('purchase.initiation.save', $purchase->pcs_id), [
            'op' => 'add_item',
            'item_desc' => 'New Test Item',
            'item_qty' => 5
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pur.purcaseitems', [
            'pci_pcs_id' => $purchase->pcs_id,
            'pci_desc' => 'New Test Item',
            'pci_qty' => 5
        ]);
    }

    public function test_can_save_remarks_inline()
    {
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first();
        $head = DB::table('cen.heads')->first();

        if (!$user || !$head) {
            $this->markTestSkipped('No prj user or head found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_title = 'Draft Case for Remarks';
        $purchase->pcs_unt_id = $user->acc_unt_id;
        $purchase->pcs_intunt_id = $user->acc_unt_id;
        $purchase->pcs_effunt_id = $user->acc_unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_date = now()->toDateString();
        $purchase->save();

        $response = $this->actingAs($user)->post(route('purchase.initiation.save', $purchase->pcs_id), [
            'op' => 'save_remarks',
            'pcs_remarks' => 'Updated inline remarks'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pur.purcases', [
            'pcs_id' => $purchase->pcs_id,
            'pcs_remarks' => 'Updated inline remarks'
        ]);
    }
}
