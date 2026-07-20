<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseCaseFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_commitment_creation_on_approval()
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'nrdi')->first();

        if (!$unit || !$head || !$user) {
            $this->markTestSkipped('Required database records not found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_title = 'Test Purchase Case';
        $purchase->pcs_unt_id = $unit->unt_id;
        $purchase->pcs_intunt_id = $unit->unt_id;
        $purchase->pcs_status = 'With DG';
        $purchase->pcs_price = 150000;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_effunt_id = $unit->unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_date = now()->toDateString();
        $purchase->pcs_sudohed = 'SUDO';
        $purchase->save();

        $service = app(PurchaseApprovalService::class);
        $this->actingAs($user);
        $service->processDecision($purchase, 'approve', 'Approved by DG');

        $this->assertDatabaseHas('fin.commitments', [
            'cmt_docid' => $purchase->pcs_id,
            'cmt_type' => 'Ps',
            'cmt_status' => 'Awaited',
            'cmt_amount' => -150000
        ]);
    }

    public function test_payment_settlement_flow()
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first();

        if (!$unit || !$head || !$user) {
            $this->markTestSkipped('Required database records not found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_title = 'Test Payment Case';
        $purchase->pcs_unt_id = $unit->unt_id;
        $purchase->pcs_intunt_id = $unit->unt_id;
        $purchase->pcs_status = 'Approved';
        $purchase->pcs_price = 150000;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_effunt_id = $unit->unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_date = now()->toDateString();
        $purchase->save();

        $cmt_id = DB::table('fin.commitments')->insertGetId([
            'cmt_docid' => $purchase->pcs_id,
            'cmt_type' => 'Ps',
            'cmt_amount' => -150000,
            'cmt_status' => 'Awaited',
            'cmt_effhed_id' => $head->hed_id,
            'cmt_effunt_id' => $unit->unt_id,
            'cmt_hed_id' => $head->hed_id,
            'cmt_unt_id' => $unit->unt_id,
            'cmt_date' => now()->toDateString()
        ], 'cmt_id');

        $response = $this->actingAs($user)->post(route('fin.payments.store_transaction', $cmt_id), [
            'trn_date' => now()->toDateString(),
            'amount' => 100000,
            'tax' => 15000,
            'is_complete' => 0
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('fin.commitments', [
            'cmt_id' => $cmt_id,
            'cmt_status' => 'Awaited'
        ]);

        $this->assertDatabaseHas('fin.transactions', [
            'trn_cmt_id' => $cmt_id,
            'trn_amount1' => -100000,
            'trn_tax1' => -15000,
            'trn_amount2' => -115000,
            'trn_balance' => 0,
            'trn_seq' => 1
        ]);

        // Complete the payment
        $response2 = $this->actingAs($user)->post(route('fin.payments.store_transaction', $cmt_id), [
            'trn_date' => now()->toDateString(),
            'amount' => 50000,
            'tax' => 0,
            'is_complete' => 1
        ]);

        $this->assertDatabaseHas('fin.commitments', [
            'cmt_id' => $cmt_id,
            'cmt_status' => 'Paid'
        ]);

        $this->assertDatabaseHas('fin.transactions', [
            'trn_cmt_id' => $cmt_id,
            'trn_amount1' => -50000,
            'trn_seq' => 2
        ]);
    }

    public function test_goods_receipt_inventory_flow()
    {
        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();
        $user = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first();

        if (!$unit || !$head || !$user) {
            $this->markTestSkipped('Required database records not found.');
        }

        $purchase = new Purchase();
        $purchase->pcs_title = 'Hardware Purchase Case';
        $purchase->pcs_unt_id = $unit->unt_id;
        $purchase->pcs_intunt_id = $unit->unt_id;
        $purchase->pcs_status = 'Approved';
        $purchase->pcs_price = 50000;
        $purchase->pcs_type = 'mat';
        $purchase->pcs_transtype = 2;
        $purchase->pcs_effhed_id = $head->hed_id;
        $purchase->pcs_effunt_id = $unit->unt_id;
        $purchase->pcs_hed_id = $head->hed_id;
        $purchase->pcs_date = now()->toDateString();
        $purchase->pcs_fulfillment_status = 'Pending Receipt';
        $purchase->save();

        $pci_id = DB::table('pur.purcaseitems')->insertGetId([
            'pci_pcs_id' => $purchase->pcs_id,
            'pci_desc' => 'Test Server',
            'pci_qty' => 2,
            'pci_qtyunit' => 'num',
            'pci_price' => 25000,
            'pci_serial' => 1,
            'pci_fulfilment' => 0,
            'pci_type' => '1',
            'pci_subtype' => 'General'
        ], 'pci_id');

        $response = $this->actingAs($user)->post(route('purchase.receipts.store', $purchase->pcs_id), [
            'items' => [
                $pci_id => [
                    'received_qty' => 1
                ]
            ]
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pur.purcases', [
            'pcs_id' => $purchase->pcs_id,
            'pcs_fulfillment_status' => 'Partially Received'
        ]);

        $this->assertDatabaseHas('pur.purcaseitems', [
            'pci_id' => $pci_id,
            'pci_fulfilment' => 1
        ]);

        $this->assertDatabaseHas('pur.purreceipts', [
            'prt_pcs_id' => $purchase->pcs_id,
            'prt_status' => 'Finalized'
        ]);

        $this->assertDatabaseHas('ina.invats', [
            'ias_pcs_id' => $purchase->pcs_id,
            'ias_pci_id' => $pci_id,
            'ias_desc' => 'Test Server',
            'ias_qty' => 1
        ]);

        $this->assertDatabaseHas('ina.invatcomps', [
            'iac_qty' => 1,
            'iac_status' => 'Held'
        ]);
    }
}
