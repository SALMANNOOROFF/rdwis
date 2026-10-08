<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserCaseDraftRemark;

class UserCaseDraftRemarksTest extends TestCase
{
    public function test_unauthenticated_user_cannot_save_draft()
    {
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson(route('draft-remarks.save'), [
                'case_type' => 'purchase',
                'case_id'   => 123,
                'remarks'   => 'Draft test remarks',
            ]);

        // Expect 401 unauthenticated
        $this->assertTrue(in_array($response->status(), [401, 302]));
    }

    public function test_authenticated_user_can_save_and_retrieve_private_draft()
    {
        $user1 = User::first();
        if (!$user1) {
            $this->markTestSkipped('No user found in database.');
        }

        $this->actingAs($user1);

        // Save Draft
        $saveResp = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson(route('draft-remarks.save'), [
                'case_type' => 'purchase',
                'case_id'   => 999999,
                'remarks'   => 'Private scrutiny note for case 999999',
            ]);

        $saveResp->assertStatus(200);
        $saveResp->assertJson(['status' => 'success']);

        // Verify stored in DB
        $this->assertDatabaseHas('cen.user_case_draft_remarks', [
            'acc_id'        => $user1->acc_id,
            'case_type'     => 'purchase',
            'case_id'       => 999999,
            'draft_remarks' => 'Private scrutiny note for case 999999',
        ]);

        // Retrieve Draft
        $getResp = $this->getJson(route('draft-remarks.get', [
            'case_type' => 'purchase',
            'case_id'   => 999999,
        ]));

        $getResp->assertStatus(200);
        $getResp->assertJson([
            'status'     => 'success',
            'has_draft'  => true,
            'remarks'    => 'Private scrutiny note for case 999999',
        ]);

        // Privacy Check: User 2 cannot see User 1's draft
        $user2 = User::where('acc_id', '!=', $user1->acc_id)->first();
        if ($user2) {
            $this->actingAs($user2);
            $user2GetResp = $this->getJson(route('draft-remarks.get', [
                'case_type' => 'purchase',
                'case_id'   => 999999,
            ]));
            $user2GetResp->assertStatus(200);
            $user2GetResp->assertJson([
                'status'    => 'success',
                'has_draft' => false,
                'remarks'   => '',
            ]);
        }

        // Clean up
        UserCaseDraftRemark::clearDraft($user1->acc_id, 'purchase', 999999);
        $this->assertDatabaseMissing('cen.user_case_draft_remarks', [
            'acc_id'    => $user1->acc_id,
            'case_type' => 'purchase',
            'case_id'   => 999999,
        ]);
    }
}
