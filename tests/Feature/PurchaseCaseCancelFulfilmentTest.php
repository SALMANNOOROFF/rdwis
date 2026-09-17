<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseCaseCancelFulfilmentTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    public function test_cancelling_purchase_case_reverses_requisition_item_fulfilment_and_unchecks_requisition_fulfilled_flag()
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();

        // 1. Create source Requisition (initially marked fulfilled)
        $prqId = DB::table('pur.purreqs')->insertGetId([
            'prq_date' => now()->toDateString(),
            'prq_unt_id' => $unit->unt_id,
            'prq_hed_id' => $head->hed_id,
            'prq_status' => 'Approved',
            'prq_fulfilled' => true,
            'prq_effhed_id' => $head->hed_id,
            'prq_intunt_id' => $unit->unt_id,
            'prq_desc' => 'Test Source Requisition',
            'prq_minute' => 1,
        ], 'prq_id');

        // 2. Create Requisition Item (fulfilled counter = 10)
        $priId = DB::table('pur.purreqitems')->insertGetId([
            'pri_prq_id' => $prqId,
            'pri_serial' => 1,
            'pri_desc' => 'Test Hardware Item',
            'pri_price' => 1500,
            'pri_qty' => 10,
            'pri_qtyunit' => 'Nos',
            'pri_type' => 1,
            'pri_category' => 1,
            'pri_fulfilment' => 10,
            'pri_subtype' => 'General',
        ], 'pri_id');

        // 3. Create Purchase Case
        $pcsId = DB::table('pur.purcases')->insertGetId([
            'pcs_date' => now()->toDateString(),
            'pcs_title' => 'Test Case for Cancellation',
            'pcs_unt_id' => $unit->unt_id,
            'pcs_intunt_id' => $unit->unt_id,
            'pcs_status' => 'Approved',
            'pcs_price' => 15000,
            'pcs_type' => 'Ps',
            'pcs_effhed_id' => $head->hed_id,
            'pcs_effunt_id' => $unit->unt_id,
            'pcs_hed_id' => $head->hed_id,
            'pcs_minute' => 1,
            'pcs_transtype' => 1,
        ], 'pcs_id');

        // 4. Create Purchase Case Item linked to the Requisition Item
        $pciId = DB::table('pur.purcaseitems')->insertGetId([
            'pci_pcs_id' => $pcsId,
            'pci_pri_id' => $priId,
            'pci_serial' => 1,
            'pci_desc' => 'Test Hardware Item',
            'pci_qty' => 10,
            'pci_qtyunit' => 'Nos',
            'pci_type' => 1,
            'pci_subtype' => 'General',
            'pci_fulfilment' => 0,
        ], 'pci_id');

        // Set up authorized division user
        $user = CenAccount::first();
        $user->acc_unt_id = $unit->unt_id;
        $user->acc_untarea = 'division';

        $response = $this->actingAs($user)->post(route('purchase.receipts.cancel', $pcsId));

        $response->assertRedirect(route('purchase.receipts.index'));
        $response->assertSessionHas('success');

        // Assert purchase case cancelled
        $updatedPcs = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        $this->assertEquals('Cancelled', $updatedPcs->pcs_status);
        $this->assertNotNull($updatedPcs->pcs_closedtg);

        // Assert requisition item fulfilment reduced from 10 to 0
        $updatedPri = DB::table('pur.purreqitems')->where('pri_id', $priId)->first();
        $this->assertEquals(0, (float) $updatedPri->pri_fulfilment);

        // Assert parent requisition fulfilled flag reset to false
        $updatedPrq = DB::table('pur.purreqs')->where('prq_id', $prqId)->first();
        $this->assertFalse((bool) $updatedPrq->prq_fulfilled);
    }

    public function test_cancelling_partially_fulfilled_case_adjusts_remaining_balance_and_unchecks_fulfilled()
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();

        $prqId = DB::table('pur.purreqs')->insertGetId([
            'prq_date' => now()->toDateString(),
            'prq_unt_id' => $unit->unt_id,
            'prq_hed_id' => $head->hed_id,
            'prq_status' => 'Approved',
            'prq_fulfilled' => true,
            'prq_effhed_id' => $head->hed_id,
            'prq_intunt_id' => $unit->unt_id,
            'prq_desc' => 'Partial Fulfilment Requisition',
            'prq_minute' => 1,
        ], 'prq_id');

        $priId = DB::table('pur.purreqitems')->insertGetId([
            'pri_prq_id' => $prqId,
            'pri_serial' => 1,
            'pri_desc' => 'Consumable Item',
            'pri_price' => 200,
            'pri_qty' => 10,
            'pri_qtyunit' => 'Nos',
            'pri_type' => 1,
            'pri_category' => 1,
            'pri_fulfilment' => 10,
            'pri_subtype' => 'General',
        ], 'pri_id');

        $pcsId = DB::table('pur.purcases')->insertGetId([
            'pcs_date' => now()->toDateString(),
            'pcs_title' => 'Partially Received Case',
            'pcs_unt_id' => $unit->unt_id,
            'pcs_intunt_id' => $unit->unt_id,
            'pcs_status' => 'Approved',
            'pcs_price' => 2000,
            'pcs_type' => 'Ps',
            'pcs_effhed_id' => $head->hed_id,
            'pcs_effunt_id' => $unit->unt_id,
            'pcs_hed_id' => $head->hed_id,
            'pcs_minute' => 1,
            'pcs_transtype' => 1,
        ], 'pcs_id');

        // Case item: 10 ordered, 4 fulfilled via receipt
        $pciId = DB::table('pur.purcaseitems')->insertGetId([
            'pci_pcs_id' => $pcsId,
            'pci_pri_id' => $priId,
            'pci_serial' => 1,
            'pci_desc' => 'Consumable Item',
            'pci_qty' => 10,
            'pci_qtyunit' => 'Nos',
            'pci_type' => 1,
            'pci_subtype' => 'General',
            'pci_fulfilment' => 4,
        ], 'pci_id');

        // Finalized receipt for 4 items
        DB::table('pur.purreceipts')->insert([
            'prt_pcs_id' => $pcsId,
            'prt_status' => 'Finalized',
            'prt_date' => now()->toDateString(),
            'prt_unt_id' => $unit->unt_id,
            'prt_prj_id' => $head->hed_id,
        ]);

        $user = CenAccount::first();
        $user->acc_unt_id = $unit->unt_id;
        $user->acc_untarea = 'division';

        $response = $this->actingAs($user)->post(route('purchase.receipts.cancel', $pcsId));

        $response->assertRedirect(route('purchase.receipts.index'));

        // Case status becomes Partially Fulfilled because a finalized receipt exists
        $updatedPcs = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        $this->assertEquals('Partially Fulfilled', $updatedPcs->pcs_status);

        // Requisition fulfilment decreased by delta (10 - 4 = 6), leaving 4
        $updatedPri = DB::table('pur.purreqitems')->where('pri_id', $priId)->first();
        $this->assertEquals(4, (float) $updatedPri->pri_fulfilment);

        // Requisition fulfilled flag reset to false
        $updatedPrq = DB::table('pur.purreqs')->where('prq_id', $prqId)->first();
        $this->assertFalse((bool) $updatedPrq->prq_fulfilled);
    }
}
