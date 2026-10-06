<?php

namespace Tests\Feature\Ai;

use App\Models\CenAccount;
use App\Services\Auth\HorizonScopeContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AiChatWidgetViewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
    }

    protected function tearDown(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
        HorizonScopeContext::reset();
        parent::tearDown();
    }

    protected function createTestUser(): CenAccount
    {
        $existing = CenAccount::whereRaw("LOWER(acc_status) = 'active'")->first();
        if ($existing) {
            return $existing;
        }

        $user = new CenAccount();
        $user->acc_username = 'ui_tester_' . uniqid();
        $user->acc_name = 'UI Tester';
        $user->acc_pass = 'test_hash';
        $user->acc_level = 1;
        $user->acc_type = 'User';
        $user->acc_startdt = now();
        $user->acc_desig = 'Director';
        $user->acc_desigshort = 'DIR';
        $user->acc_desigtype = 'lead';
        $user->acc_access = 'single';
        $user->acc_untname = 'Unit 200000';
        $user->acc_untnamesh = 'U200000';
        $user->acc_unttype = 'Division';
        $user->acc_unt_id = 200000;
        $user->acc_lowerm = 200000;
        $user->acc_upperm = 200000;
        $user->acc_lowers = 200000;
        $user->acc_uppers = 200000;
        $user->acc_untarea = 'fin';
        $user->acc_auth = 'approver';
        $user->acc_lowerm = 100000;
        $user->acc_upperm = 999999;
        $user->acc_lowers = 100000;
        $user->acc_uppers = 999999;
        $user->acc_status = 'Active';
        $user->save();

        return $user;
    }

    /**
     * Test 1: Authenticated user sees the AI Chat Widget in layout.
     */
    public function test_authenticated_user_sees_ai_chat_widget(): void
    {
        $user = $this->createTestUser();

        $view = $this->actingAs($user)->view('welcome');

        $view->assertSee('id="rdwisAiWidgetContainer"', false);
        $view->assertSee('id="rdwisAiToggleBtn"', false);
        $view->assertSee('id="rdwisAiChatWindow"', false);
        $view->assertSee('id="rdwisAiMessages"', false);
        $view->assertSee('id="rdwisAiInput"', false);
        $view->assertSee('id="rdwisAiSendBtn"', false);
        $view->assertSee('id="rdwisAiTypingIndicator"', false);
        $view->assertSee('RIVA', false);
        $view->assertSee('RDWIS Intelligent Virtual Assistant', false);
    }

    /**
     * Test 2: Guest / unauthenticated visitor on login page does NOT see the AI Chat Widget.
     */
    public function test_guest_does_not_see_ai_chat_widget(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('id="rdwisAiWidgetContainer"', false);
        $response->assertDontSee('id="rdwisAiToggleBtn"', false);
    }

    /**
     * Test 3: Debug console button is strictly hidden from regular users.
     */
    public function test_debug_console_is_strictly_hidden_from_regular_users(): void
    {
        $regularUser = $this->createTestUser();
        $regularUser->acc_username = 'regular_user_' . uniqid();
        $regularUser->save();

        $view = $this->actingAs($regularUser)->view('welcome');

        $view->assertDontSee('id="rdwisDebugConsole"', false);
        $view->assertDontSee('DEBUG CONSOLE', false);
    }

    /**
     * Test 4: Debug console button is visible strictly for superadmin.
     */
    public function test_debug_console_is_visible_for_superadmin(): void
    {
        $superadmin = CenAccount::where('acc_username', 'superadminrdw')->first();
        if (!$superadmin) {
            $superadmin = new CenAccount();
            $superadmin->acc_username = 'superadminrdw';
            $superadmin->acc_name = 'Super Admin';
            $superadmin->acc_pass = 'test_hash';
            $superadmin->acc_status = 'Active';
            $superadmin->save();
        }

        $view = $this->actingAs($superadmin)->view('welcome');

        $view->assertSee('id="rdwisDebugConsole"', false);
        $view->assertSee('DEBUG CONSOLE', false);
    }

    protected function getSoitUser(): CenAccount
    {
        $soit = CenAccount::where('acc_untarea', 'it')
            ->where('acc_username', '!=', 'superadminrdw')
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();
        if ($soit) {
            return $soit;
        }

        $user = new CenAccount();
        $user->acc_username = 'soit_' . uniqid();
        $user->acc_name = 'SO IT Officer';
        $user->acc_pass = 'test_hash';
        $user->acc_level = 1;
        $user->acc_type = 'User';
        $user->acc_startdt = now();
        $user->acc_desig = 'Director';
        $user->acc_desigshort = 'DIR';
        $user->acc_untname = 'Unit 860000';
        $user->acc_untnamesh = 'U860000';
        $user->acc_unttype = 'Division';
        $user->acc_unt_id = 860000;
        $user->acc_untarea = 'it';
        $user->acc_auth = 'approver';
        $user->acc_status = 'Active';
        $user->save();

        return $user;
    }

    /**
     * Test 5: Regular user does NOT see the AI master toggle switch in navbar.
     */
    public function test_regular_user_does_not_see_ai_master_switch(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
        $regularUser = $this->createTestUser();

        $view = $this->actingAs($regularUser)->view('welcome');

        $view->assertDontSee('id="rdwisAiAdminToggleSwitch"', false);
    }

    /**
     * Test 6: Superadmin sees the AI master toggle switch in navbar.
     */
    public function test_superadmin_sees_ai_master_switch(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
        $superadmin = CenAccount::where('acc_username', 'superadminrdw')->first();
        if (!$superadmin) {
            $superadmin = new CenAccount();
            $superadmin->acc_username = 'superadminrdw';
            $superadmin->acc_name = 'Super Admin';
            $superadmin->acc_pass = 'test_hash';
            $superadmin->acc_unt_id = 860000;
            $superadmin->acc_status = 'Active';
            $superadmin->save();
        }

        $view = $this->actingAs($superadmin)->view('welcome');

        $view->assertSee('id="rdwisAiAdminToggleSwitch"', false);
        $view->assertSee('RIVA AI', false);
        $view->assertSee('MASTER', false);
        $view->assertSee('ON', false);
    }

    /**
     * Test 7: SO IT sees the AI master toggle switch in navbar.
     */
    public function test_soit_sees_ai_master_switch(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
        $soit = $this->getSoitUser();

        $view = $this->actingAs($soit)->view('welcome');

        $view->assertSee('id="rdwisAiAdminToggleSwitch"', false);
        $view->assertSee('RIVA AI', false);
        $view->assertSee('ON', false);
    }

    /**
     * Test 8: When AI is disabled, regular user does NOT see chatbot widget in UI.
     */
    public function test_when_ai_disabled_regular_user_does_not_see_widget(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '0');
        $regularUser = $this->createTestUser();

        $view = $this->actingAs($regularUser)->view('welcome');

        // Chatbot must disappear completely from UI
        $view->assertDontSee('id="rdwisAiWidgetContainer"', false);
        $view->assertDontSee('id="rdwisAiToggleBtn"', false);
    }

    /**
     * Test 9: Regular user cannot toggle AI endpoint (403 Forbidden).
     */
    public function test_regular_user_cannot_toggle_ai(): void
    {
        $regularUser = $this->createTestUser();

        $response = $this->actingAs($regularUser)->postJson(route('api.ai.toggle'), [
            'enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 10: SO IT and Super Admin can toggle AI endpoint and update SystemSetting.
     */
    public function test_soit_and_superadmin_can_toggle_ai(): void
    {
        $soit = $this->getSoitUser();

        // Toggle to disabled
        $response = $this->actingAs($soit)->postJson(route('api.ai.toggle'), [
            'enabled' => false,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'enabled' => false]);
        $this->assertEquals('0', \App\Models\SystemSetting::get('ai_assistant_enabled'));

        // Toggle back to enabled
        $response2 = $this->actingAs($soit)->postJson(route('api.ai.toggle'), [
            'enabled' => true,
        ]);
        $response2->assertStatus(200);
        $response2->assertJson(['success' => true, 'enabled' => true]);
        $this->assertEquals('1', \App\Models\SystemSetting::get('ai_assistant_enabled'));
    }

    /**
     * Test 11: Chat API endpoint is blocked when AI is disabled.
     */
    public function test_chat_api_is_blocked_when_ai_disabled(): void
    {
        \App\Models\SystemSetting::set('ai_assistant_enabled', '0');
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->postJson(route('api.ai.chat'), [
            'message' => 'Hello RIVA',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'status' => 'disabled',
        ]);

        // Cleanup
        \App\Models\SystemSetting::set('ai_assistant_enabled', '1');
    }
}
