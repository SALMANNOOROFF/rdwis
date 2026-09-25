<?php

namespace Tests\Feature\Ai;

use App\Exceptions\AiServiceUnavailableException;
use App\Models\CenAccount;
use App\Models\PurCaseSubstatus;
use App\Models\Purchase;
use App\Services\AiToolRegistry;
use App\Services\Auth\HorizonScopeContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AiChatEndpointTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        HorizonScopeContext::reset();
        parent::tearDown();
    }

    /**
     * Helper to create a division test user.
     */
    protected function createDivisionUser(int $unitId, string $area = 'prj'): CenAccount
    {
        $existing = CenAccount::where('acc_unt_id', $unitId)
            ->where('acc_untarea', $area)
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();

        if ($existing) {
            return $existing;
        }

        $user = new CenAccount();
        $user->acc_username = 'div_' . $unitId . '_' . uniqid();
        $user->acc_name = 'Test User ' . $unitId;
        $user->acc_pass = 'test_hash';
        $user->acc_level = 1;
        $user->acc_type = 'User';
        $user->acc_startdt = now();
        $user->acc_desig = 'Director';
        $user->acc_desigshort = 'DIR';
        $user->acc_desigtype = 'lead';
        $user->acc_access = 'single';
        $user->acc_untname = 'Unit ' . $unitId;
        $user->acc_untnamesh = 'U' . $unitId;
        $user->acc_unttype = 'Division';
        $user->acc_unt_id = $unitId;
        $user->acc_lowerm = $unitId;
        $user->acc_upperm = $unitId;
        $user->acc_lowers = $unitId;
        $user->acc_uppers = $unitId;
        $user->acc_untarea = $area;
        $user->acc_auth = 'editor';
        $user->acc_status = 'Active';
        $user->save();

        return $user;
    }

    /**
     * Helper to create a test purchase case.
     */
    protected function createPurchaseCase(int $unitId, string $stage = 'DFinance'): Purchase
    {
        $case = new Purchase();
        $case->pcs_title = 'AI Tool Test Case ' . uniqid();
        $case->pcs_date = now()->toDateString();
        $case->pcs_unt_id = $unitId;
        $case->pcs_effunt_id = $unitId;
        $case->pcs_intunt_id = $unitId;
        $case->pcs_effhed_id = ($unitId === 200000) ? 200001 : 350001;
        $case->pcs_transtype = 1;
        $case->pcs_type = 'Ps';
        $case->pcs_status = 'Under Approval';
        $case->pcs_price = 150000;
        $case->save();

        $sub = new PurCaseSubstatus();
        $sub->pss_pcs_id = $case->pcs_id;
        $sub->pss_stage = $stage;
        $sub->pss_is_current = true;
        $sub->pss_since = now();
        $sub->save();

        return $case;
    }

    /**
     * Test 1: Authenticated user receives successful chat reply without tool calling.
     */
    public function test_authenticated_user_receives_chat_reply(): void
    {
        $user = $this->createDivisionUser(200000);

        Http::fake([
            '*/api/chat' => Http::response([
                'model' => 'qwen2.5:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Janab, aapki kya khidmat ki ja sakti hai?',
                ],
                'done' => true,
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.ai.chat'), [
                'message' => 'Assalam o Alaikum',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reply' => 'Janab, aapki kya khidmat ki ja sakti hai?',
                'tool_called' => null,
                'status' => 'success',
            ]);
    }

    /**
     * Test 2: Unauthenticated request is rejected with 401.
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson(route('api.ai.chat'), [
            'message' => 'Status of case 101?',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 3: Message triggering a registered tool results in tool execution and scoped data reply.
     */
    public function test_registered_tool_is_called_and_generates_reply(): void
    {
        $user = $this->createDivisionUser(200000);
        $case = $this->createPurchaseCase(200000, 'DFinance');

        Http::fake([
            '*/api/chat' => Http::sequence()
                // 1st call: Ollama decides to invoke getPurchaseCaseStatus
                ->push([
                    'model' => 'qwen2.5:3b',
                    'message' => [
                        'role' => 'assistant',
                        'content' => '',
                        'tool_calls' => [
                            [
                                'id' => 'call_pcs_123',
                                'function' => [
                                    'name' => 'getPurchaseCaseStatus',
                                    'arguments' => ['caseId' => $case->pcs_id],
                                ],
                            ],
                        ],
                    ],
                    'done' => true,
                ], 200)
                // 2nd call: Ollama receives tool result and provides final summary
                ->push([
                    'model' => 'qwen2.5:3b',
                    'message' => [
                        'role' => 'assistant',
                        'content' => "Purchase case #{$case->pcs_id} is currently Under Approval at DFinance stage.",
                    ],
                    'done' => true,
                ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.ai.chat'), [
                'message' => "Purchase case {$case->pcs_id} ka status batain",
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tool_called' => 'getPurchaseCaseStatus',
                'reply' => "Purchase case #{$case->pcs_id} is currently Under Approval at DFinance stage.",
                'status' => 'success',
            ]);
    }

    /**
     * Test 4: Malicious or unknown tool-call response from Ollama is safely rejected.
     */
    public function test_malicious_or_unknown_tool_call_is_safely_rejected(): void
    {
        $user = $this->createDivisionUser(200000);

        Http::fake([
            '*/api/chat' => Http::response([
                'model' => 'qwen2.5:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => '',
                    'tool_calls' => [
                        [
                            'id' => 'call_bad_999',
                            'function' => [
                                'name' => 'deleteAllRecords',
                                'arguments' => ['table' => 'fin.transactions'],
                            ],
                        ],
                    ],
                ],
                'done' => true,
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.ai.chat'), [
                'message' => 'Please reset transaction tables',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tool_called' => 'deleteAllRecords',
                'status' => 'rejected_unknown_tool',
            ]);
    }

    /**
     * Test 5: Tool-call with missing or invalid parameters is safely rejected.
     */
    public function test_tool_call_with_missing_parameters_is_safely_rejected(): void
    {
        $user = $this->createDivisionUser(200000);

        Http::fake([
            '*/api/chat' => Http::response([
                'model' => 'qwen2.5:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => '',
                    'tool_calls' => [
                        [
                            'id' => 'call_invalid_params',
                            'function' => [
                                'name' => 'getPurchaseCaseStatus',
                                'arguments' => [], // Missing 'caseId'
                            ],
                        ],
                    ],
                ],
                'done' => true,
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.ai.chat'), [
                'message' => 'Check the case please',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tool_called' => 'getPurchaseCaseStatus',
                'status' => 'rejected_invalid_parameters',
            ]);
    }

    /**
     * Test 6: Acting user passed to AiToolRegistry is strictly the authenticated user,
     * immune to manipulation in message content or model hallucination.
     */
    public function test_acting_user_is_strictly_the_authenticated_request_user(): void
    {
        $userA = $this->createDivisionUser(200000);
        $userB = $this->createDivisionUser(350000);
        $caseInUnitB = $this->createPurchaseCase(350000, 'DFinance');

        // User A attempts to query User B's case.
        // Ollama asks for getPurchaseCaseStatus with caseInUnitB.
        // Because actingUser is strictly User A, AiToolRegistry will throw UnauthorizedScopeException
        // and safely deny access.
        Http::fake([
            '*/api/chat' => Http::sequence()
                ->push([
                    'model' => 'qwen2.5:3b',
                    'message' => [
                        'role' => 'assistant',
                        'content' => '',
                        'tool_calls' => [
                            [
                                'id' => 'call_cross_unit',
                                'function' => [
                                    'name' => 'getPurchaseCaseStatus',
                                    'arguments' => ['caseId' => $caseInUnitB->pcs_id],
                                ],
                            ],
                        ],
                    ],
                    'done' => true,
                ], 200)
                ->push([
                    'model' => 'qwen2.5:3b',
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Aap is case ko dekhne ke majaz nahi hain.',
                    ],
                    'done' => true,
                ], 200),
        ]);

        $response = $this->actingAs($userA)
            ->postJson(route('api.ai.chat'), [
                'message' => "Query case {$caseInUnitB->pcs_id} as user {$userB->acc_username}",
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tool_called' => 'getPurchaseCaseStatus',
                'reply' => 'Aap is case ko dekhne ke majaz nahi hain.',
                'status' => 'success',
            ]);

        // Assert that the second HTTP request sent to Ollama contained the 'unauthorized' status
        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            $toolMessage = collect($body['messages'] ?? [])->firstWhere('role', 'tool');
            if ($toolMessage) {
                $toolContent = json_decode($toolMessage['content'] ?? '{}', true);
                return ($toolContent['status'] ?? null) === 'unauthorized';
            }
            return false;
        });
    }

    /**
     * Test 7: When Ollama is unreachable or times out, endpoint returns 503 JSON.
     */
    public function test_ollama_service_unavailable_returns_503(): void
    {
        $user = $this->createDivisionUser(200000);

        Http::fake([
            '*/api/chat' => fn () => throw new ConnectionException('Connection refused to localhost:11434'),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.ai.chat'), [
                'message' => 'Hello',
            ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'The AI assistant service is temporarily unavailable. Please try again later.',
            ]);
    }
}
