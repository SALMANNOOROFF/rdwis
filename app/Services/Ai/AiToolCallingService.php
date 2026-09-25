<?php

namespace App\Services\Ai;

use App\Exceptions\AiServiceUnavailableException;
use App\Exceptions\AiToolRecordNotFoundException;
use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\User;
use App\Services\AiToolRegistry;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * AiToolCallingService
 *
 * Implements the core multi-turn tool-calling loop connecting the user's intent,
 * local Ollama inference, and the scoped AiToolRegistry.
 *
 * Guarantees:
 * 1. The acting user is ALWAYS passed explicitly from the authenticated request context.
 * 2. Tools not registered in AiToolRegistry are never executed.
 * 3. Parameters are strictly validated before invocation.
 * 4. Every tool call is logged with acting user ID and timestamp for auditability.
 */
class AiToolCallingService
{
    protected OllamaClient $client;
    protected AiToolRegistry $registry;

    public function __construct(?OllamaClient $client = null, ?AiToolRegistry $registry = null)
    {
        $this->client = $client ?? app(OllamaClient::class);
        $this->registry = $registry ?? app(AiToolRegistry::class);
    }

    /**
     * Convert AiToolRegistry definitions into Ollama/OpenAI standard tool schemas.
     *
     * @return array<int, array{type: string, function: array}>
     */
    public function getFormattedTools(): array
    {
        $tools = [];
        foreach (AiToolRegistry::getToolMap() as $toolName => $definition) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $definition['name'] ?? $toolName,
                    'description' => $definition['description'] ?? '',
                    'parameters' => $definition['parameters'] ?? [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Process a user message through Ollama with the tool-calling loop.
     *
     * @param string $userMessage
     * @param Authenticatable|CenAccount|User $actingUser
     * @param array<int, array{role: string, content: string}> $history
     * @return array{
     *     reply: string,
     *     tool_called: ?string,
     *     tool_arguments: ?array,
     *     tool_result: ?array,
     *     status: string
     * }
     *
     * @throws AiServiceUnavailableException
     */
    public function processMessage(
        string $userMessage,
        Authenticatable|CenAccount|User $actingUser,
        array $history = []
    ): array {
        // Build initial conversation messages
        $messages = [];

        // 1. System prompt (from Task 4 config / class)
        $messages[] = [
            'role' => 'system',
            'content' => AiSystemPrompt::get(),
        ];

        // 2. Filtered conversation history (only safe roles)
        foreach ($history as $turn) {
            if (isset($turn['role'], $turn['content']) && in_array($turn['role'], ['user', 'assistant'], true)) {
                $messages[] = [
                    'role' => (string) $turn['role'],
                    'content' => (string) $turn['content'],
                ];
            }
        }

        // 3. Current user message
        $messages[] = [
            'role' => 'user',
            'content' => trim($userMessage),
        ];

        $tools = $this->getFormattedTools();

        // Step 1: Initial call to Ollama
        $response = $this->client->chat($messages, $tools);

        $assistantMessage = $response['message'] ?? [];
        $toolCalls = $assistantMessage['tool_calls'] ?? [];

        // If no tool call was requested by the model, return natural language content directly
        if (empty($toolCalls)) {
            return [
                'reply' => (string) ($assistantMessage['content'] ?? ''),
                'tool_called' => null,
                'tool_arguments' => null,
                'tool_result' => null,
                'status' => 'success',
            ];
        }

        // Step 2 & 3: Handle the tool call
        $firstCall = $toolCalls[0];
        $toolName = (string) ($firstCall['function']['name'] ?? '');
        $rawArgs = $firstCall['function']['arguments'] ?? [];

        // Arguments might be a pre-parsed array or a JSON string
        if (is_string($rawArgs)) {
            $decoded = json_decode($rawArgs, true);
            $arguments = is_array($decoded) ? $decoded : [];
        } elseif (is_array($rawArgs)) {
            $arguments = $rawArgs;
        } else {
            $arguments = [];
        }

        // Guard A: Verify tool is in the allowed whitelist
        if (!AiToolRegistry::hasTool($toolName) || !in_array($toolName, AiToolRegistry::getAllowedTools(), true)) {
            Log::warning('AI tool calling rejected unregistered tool', [
                'attempted_tool' => $toolName,
                'acting_user_id' => $actingUser->getAuthIdentifier(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return [
                'reply' => AiSystemPrompt::getUnknownToolFallback(),
                'tool_called' => $toolName,
                'tool_arguments' => $arguments,
                'tool_result' => null,
                'status' => 'rejected_unknown_tool',
            ];
        }

        // Guard B: Validate parameters against schema
        $paramError = $this->validateToolParameters($toolName, $arguments);
        if ($paramError !== null) {
            Log::warning('AI tool calling rejected invalid parameters', [
                'tool' => $toolName,
                'arguments' => $arguments,
                'validation_error' => $paramError,
                'acting_user_id' => $actingUser->getAuthIdentifier(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return [
                'reply' => AiSystemPrompt::getInvalidParamsFallback(),
                'tool_called' => $toolName,
                'tool_arguments' => $arguments,
                'tool_result' => null,
                'status' => 'rejected_invalid_parameters',
            ];
        }

        // Step 4: Audit Log the verified tool invocation
        Log::info('AI tool invocation', [
            'tool' => $toolName,
            'params' => $arguments,
            'acting_user_id' => $actingUser->getAuthIdentifier(),
            'acting_username' => $actingUser->acc_username ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Step 5: Execute tool strictly with the authenticated $actingUser
        $toolResult = null;
        try {
            $toolResult = $this->registry->invoke($toolName, $arguments, $actingUser);
        } catch (AiToolRecordNotFoundException $e) {
            $toolResult = [
                'status' => 'not_found',
                'message' => $e->getMessage(),
            ];
        } catch (UnauthorizedScopeException $e) {
            $toolResult = [
                'status' => 'unauthorized',
                'message' => 'You are not authorized to view this record under your divisional scope.',
            ];
        } catch (MissingScopeContextException $e) {
            $toolResult = [
                'status' => 'missing_scope',
                'message' => $e->getMessage(),
            ];
        } catch (InvalidArgumentException $e) {
            $toolResult = [
                'status' => 'invalid_arguments',
                'message' => $e->getMessage(),
            ];
        } catch (Throwable $e) {
            Log::error('AI tool invocation threw unexpected error', [
                'tool' => $toolName,
                'arguments' => $arguments,
                'error' => $e->getMessage(),
                'acting_user_id' => $actingUser->getAuthIdentifier(),
            ]);

            $toolResult = [
                'status' => 'error',
                'message' => 'An error occurred while executing the query tool.',
            ];
        }

        // Step 6: Follow-up turn with Ollama to generate final natural language answer
        $followUpMessages = $messages;
        $followUpMessages[] = $assistantMessage;
        $followUpMessages[] = [
            'role' => 'tool',
            'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
        ];

        $finalResponse = $this->client->chat($followUpMessages, $tools);
        $finalContent = (string) ($finalResponse['message']['content'] ?? '');

        return [
            'reply' => $finalContent,
            'tool_called' => $toolName,
            'tool_arguments' => $arguments,
            'tool_result' => $toolResult,
            'status' => 'success',
        ];
    }

    /**
     * Validate arguments against required parameters and types declared in tool definition.
     */
    public function validateToolParameters(string $toolName, array $arguments): ?string
    {
        $definition = AiToolRegistry::getToolDefinition($toolName);
        if (!$definition) {
            return "No definition found for tool '{$toolName}'.";
        }

        $parameters = $definition['parameters'] ?? [];
        $required = $parameters['required'] ?? [];
        $properties = $parameters['properties'] ?? [];

        foreach ($required as $paramName) {
            $snake = Str::snake($paramName);
            $hasKey = array_key_exists($paramName, $arguments) || array_key_exists($snake, $arguments);

            if (!$hasKey) {
                return "Missing required parameter '{$paramName}'.";
            }

            $value = $arguments[$paramName] ?? $arguments[$snake] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                return "Parameter '{$paramName}' cannot be empty.";
            }

            $type = $properties[$paramName]['type'] ?? null;
            if ($type === 'integer') {
                if (!is_numeric($value) || (int) $value <= 0) {
                    return "Parameter '{$paramName}' must be a valid positive integer.";
                }
            }
        }

        return null;
    }
}
