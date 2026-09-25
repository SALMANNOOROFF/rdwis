<?php

namespace Tests\Unit\Ai;

use App\Exceptions\AiServiceUnavailableException;
use App\Services\Ai\OllamaClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaClientTest extends TestCase
{
    public function test_initializes_with_config_or_custom_values(): void
    {
        $client = new OllamaClient('http://my-ollama:11434', 'llama3:8b', 60);

        $this->assertEquals('http://my-ollama:11434', $client->getBaseUrl());
        $this->assertEquals('llama3:8b', $client->getModel());
        $this->assertEquals(60, $client->getTimeout());
    }

    public function test_chat_sends_correct_payload_and_returns_data(): void
    {
        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'qwen2.5:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Test reply',
                ],
                'done' => true,
            ], 200),
        ]);

        $client = new OllamaClient('http://localhost:11434', 'qwen2.5:3b', 10);
        $result = $client->chat([
            ['role' => 'user', 'content' => 'Hello'],
        ]);

        $this->assertEquals('Test reply', $result['message']['content']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'http://localhost:11434/api/chat'
                && $data['model'] === 'qwen2.5:3b'
                && $data['stream'] === false
                && $data['messages'][0]['content'] === 'Hello';
        });
    }

    public function test_chat_throws_typed_exception_on_server_error(): void
    {
        Http::fake([
            'http://localhost:11434/api/chat' => Http::response('Internal Server Error', 500),
        ]);

        $client = new OllamaClient('http://localhost:11434', 'qwen2.5:3b', 10);

        $this->expectException(AiServiceUnavailableException::class);
        $this->expectExceptionMessage('Ollama API returned HTTP 500');

        $client->chat([
            ['role' => 'user', 'content' => 'Hello'],
        ]);
    }

    public function test_chat_throws_typed_exception_on_connection_failure(): void
    {
        Http::fake([
            'http://localhost:11434/api/chat' => fn () => throw new ConnectionException('Failed to connect'),
        ]);

        $client = new OllamaClient('http://localhost:11434', 'qwen2.5:3b', 10);

        $this->expectException(AiServiceUnavailableException::class);
        $this->expectExceptionMessage('Unable to connect to Ollama service');

        $client->chat([
            ['role' => 'user', 'content' => 'Hello'],
        ]);
    }

    public function test_is_available_returns_true_when_ollama_runs(): void
    {
        Http::fake([
            'http://localhost:11434/' => Http::response('Ollama is running', 200),
        ]);

        $client = new OllamaClient('http://localhost:11434', 'qwen2.5:3b', 10);
        $this->assertTrue($client->isAvailable());
    }

    public function test_is_available_returns_false_when_unreachable(): void
    {
        Http::fake([
            'http://localhost:11434/' => Http::response('Not Found', 404),
        ]);

        $client = new OllamaClient('http://localhost:11434', 'qwen2.5:3b', 10);
        $this->assertFalse($client->isAvailable());
    }
}
