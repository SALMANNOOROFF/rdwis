<?php

namespace App\Services\Ai;

use App\Exceptions\AiServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * OllamaClient
 *
 * Safe HTTP wrapper around the local Ollama REST API (/api/chat).
 * Handles timeouts, network failures, and bad HTTP status codes by converting
 * them into typed AiServiceUnavailableException instances.
 */
class OllamaClient
{
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct(?string $baseUrl = null, ?string $model = null, ?int $timeout = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.ollama.base_url', 'http://localhost:11434'), '/');
        $this->model = $model ?? config('services.ollama.model', 'qwen2.5:3b');
        $this->timeout = $timeout ?? (int) config('services.ollama.timeout', 45);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Send a chat completion request to Ollama /api/chat.
     *
     * @param array<int, array{role: string, content?: string, tool_calls?: array}> $messages
     * @param array<int, array> $tools
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     *
     * @throws AiServiceUnavailableException
     */
    public function chat(array $messages, array $tools = [], array $options = []): array
    {
        $url = $this->baseUrl . '/api/chat';

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        if (!empty($options)) {
            $payload['options'] = $options;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if (!$response->successful()) {
                throw new AiServiceUnavailableException(
                    "Ollama API returned HTTP {$response->status()}: {$response->body()}"
                );
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new AiServiceUnavailableException("Ollama API returned an invalid response format.");
            }

            return $data;
        } catch (ConnectionException $e) {
            throw new AiServiceUnavailableException(
                "Unable to connect to Ollama service at {$this->baseUrl}: {$e->getMessage()}",
                0,
                $e
            );
        } catch (RequestException $e) {
            throw new AiServiceUnavailableException(
                "Ollama HTTP request failed: {$e->getMessage()}",
                0,
                $e
            );
        } catch (Throwable $e) {
            if ($e instanceof AiServiceUnavailableException) {
                throw $e;
            }
            throw new AiServiceUnavailableException(
                "Ollama service error: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Ping the Ollama service to check if it is alive.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get($this->baseUrl . '/');
            return $response->successful() && str_contains($response->body(), 'Ollama is running');
        } catch (Throwable) {
            return false;
        }
    }
}
