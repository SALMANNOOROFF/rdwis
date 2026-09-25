<?php

namespace Tests\Unit\Ai;

use App\Services\Ai\AiSystemPrompt;
use App\Services\Ai\AiToolCallingService;
use App\Services\Ai\OllamaClient;
use App\Services\AiToolRegistry;
use Tests\TestCase;

class AiToolCallingServiceTest extends TestCase
{
    protected AiToolCallingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiToolCallingService(
            client: new OllamaClient(),
            registry: new AiToolRegistry()
        );
    }

    public function test_formats_tools_matching_ollama_and_openai_schema(): void
    {
        $tools = $this->service->getFormattedTools();

        $this->assertIsArray($tools);
        $this->assertCount(3, $tools);

        $names = array_column(array_column($tools, 'function'), 'name');
        $this->assertContains('getPurchaseCaseStatus', $names);
        $this->assertContains('getChequeDetails', $names);
        $this->assertContains('getAttendanceSummary', $names);

        $first = $tools[0];
        $this->assertEquals('function', $first['type']);
        $this->assertArrayHasKey('name', $first['function']);
        $this->assertArrayHasKey('description', $first['function']);
        $this->assertArrayHasKey('parameters', $first['function']);
        $this->assertEquals('object', $first['function']['parameters']['type']);
    }

    public function test_validate_tool_parameters_passes_for_valid_arguments(): void
    {
        // getPurchaseCaseStatus
        $err = $this->service->validateToolParameters('getPurchaseCaseStatus', ['caseId' => 42]);
        $this->assertNull($err);

        // snake_case alias
        $err = $this->service->validateToolParameters('getPurchaseCaseStatus', ['case_id' => 42]);
        $this->assertNull($err);

        // getChequeDetails
        $err = $this->service->validateToolParameters('getChequeDetails', ['chequeNumber' => 'CHQ-9901']);
        $this->assertNull($err);

        // getAttendanceSummary
        $err = $this->service->validateToolParameters('getAttendanceSummary', [
            'employeeId' => '18-21-08-5273',
            'month' => '2024-08',
        ]);
        $this->assertNull($err);
    }

    public function test_validate_tool_parameters_rejects_missing_or_invalid_arguments(): void
    {
        // Missing parameter
        $err = $this->service->validateToolParameters('getPurchaseCaseStatus', []);
        $this->assertNotNull($err);
        $this->assertStringContainsString('Missing required parameter', $err);

        // Non-positive or non-numeric integer
        $err = $this->service->validateToolParameters('getPurchaseCaseStatus', ['caseId' => -5]);
        $this->assertNotNull($err);
        $this->assertStringContainsString('must be a valid positive integer', $err);

        $err = $this->service->validateToolParameters('getPurchaseCaseStatus', ['caseId' => 'abc']);
        $this->assertNotNull($err);

        // Empty string
        $err = $this->service->validateToolParameters('getChequeDetails', ['chequeNumber' => '   ']);
        $this->assertNotNull($err);
        $this->assertStringContainsString('cannot be empty', $err);

        // Unknown tool definition
        $err = $this->service->validateToolParameters('nonExistentTool', ['foo' => 'bar']);
        $this->assertNotNull($err);
    }

    public function test_system_prompt_retrieval_and_fallbacks(): void
    {
        $prompt = AiSystemPrompt::get();
        $this->assertNotEmpty($prompt);
        $this->assertStringContainsString('RDWIS', $prompt);
        $this->assertStringContainsString('Roman Urdu', $prompt);
        $this->assertStringContainsString('FNA', $prompt);

        $unknownFallback = AiSystemPrompt::getUnknownToolFallback();
        $this->assertNotEmpty($unknownFallback);

        $invalidFallback = AiSystemPrompt::getInvalidParamsFallback();
        $this->assertNotEmpty($invalidFallback);
    }
}
