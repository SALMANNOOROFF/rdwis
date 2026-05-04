<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseInitiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_division_user_can_view_draft_case()
    {
        $user = new CenAccount();
        $user->acc_name = 'Test User';
        $user->acc_untarea = 'prj';
        $user->acc_unt_id = 1;
        $user->save();

        $purchase = new Purchase();
        $purchase->pcs_unt_id = 1;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_title = 'Test Purchase Case';
        $purchase->pcs_date = now();
        $purchase->save();

        $response = $this->actingAs($user)->get(route('purchase.initiation.show', $purchase->pcs_id));

        $response->assertStatus(200);
        $response->assertSee('Test Purchase Case');
        $response->assertSee('EDITING MODE');
    }

    public function test_can_add_item_to_draft_case()
    {
        $user = new CenAccount();
        $user->acc_name = 'Test User';
        $user->acc_untarea = 'prj';
        $user->acc_unt_id = 1;
        $user->save();

        $purchase = new Purchase();
        $purchase->pcs_unt_id = 1;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_date = now();
        $purchase->save();

        $response = $this->actingAs($user)->post(route('purchase.initiation.save', $purchase->pcs_id), [
            'op' => 'add_item',
            'item_desc' => 'New Test Item',
            'item_qty' => 5
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pur.items', [
            'pci_pcs_id' => $purchase->pcs_id,
            'pci_desc' => 'New Test Item',
            'pci_qty' => 5
        ]);
    }

    public function test_can_save_remarks_inline()
    {
        $user = new CenAccount();
        $user->acc_name = 'Test User';
        $user->acc_untarea = 'prj';
        $user->acc_unt_id = 1;
        $user->save();

        $purchase = new Purchase();
        $purchase->pcs_unt_id = 1;
        $purchase->pcs_status = 'Draft';
        $purchase->pcs_date = now();
        $purchase->save();

        $response = $this->actingAs($user)->post(route('purchase.initiation.save', $purchase->pcs_id), [
            'op' => 'save_remarks',
            'pcs_remarks' => 'Updated inline remarks'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pur.purcases', [
            'pcs_id' => $purchase->pcs_id,
            'pcs_remarks' => 'Updated inline remarks'
        ]);
    }
}
