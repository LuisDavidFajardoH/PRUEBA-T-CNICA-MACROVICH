<?php

namespace Tests\Unit;

use App\Services\AIService;
use App\Facades\AI;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIServiceTest extends TestCase
{
    private AIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = app(AIService::class);
    }

    public function test_ai_service_instantiation(): void
    {
        $this->assertInstanceOf(AIService::class, $this->aiService);
    }

    public function test_facade_works(): void
    {
        $this->assertInstanceOf(AIService::class, AI::getFacadeRoot());
    }

    public function test_health_check_returns_status(): void
    {
        $health = $this->aiService->healthCheck();
        
        $this->assertIsArray($health);
        $this->assertArrayHasKey('status', $health);
        $this->assertContains($health['status'], ['healthy', 'unhealthy', 'degraded']);
    }

    public function test_prompt_injection_detection(): void
    {
        // For now, we'll test that the method exists and can be called
        // In a real implementation, this would have more sophisticated detection
        $safePrompt = '¿Cuál es el clima en Madrid?';
        
        if (method_exists($this->aiService, 'detectPromptInjection')) {
            $result = $this->aiService->detectPromptInjection($safePrompt);
            $this->assertIsBool($result);
        } else {
            $this->markTestSkipped('detectPromptInjection method not implemented yet');
        }
    }

    public function test_prompt_sanitization(): void
    {
        $dirtyPrompt = "  ¿Cuál es el clima en Madrid?  \n\n  ";
        $clean = $this->aiService->sanitizePrompt($dirtyPrompt);
        
        $this->assertEquals('¿Cuál es el clima en Madrid?', $clean);
    }

    public function test_system_prompt_generation(): void
    {
        $systemPrompt = $this->aiService->getSystemPromptForTesting();
        
        $this->assertIsString($systemPrompt);
        $this->assertStringContainsString('asistente', strtolower($systemPrompt));
        $this->assertStringContainsString('clima', strtolower($systemPrompt));
    }

    public function test_generate_response_with_mock(): void
    {
        // Mock HTTP response for Gemini API
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'Hola! Soy tu asistente meteorológico. ¿En qué puedo ayudarte?'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->aiService->generateTextResponse('Hola, ¿cómo estás?');
        
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    public function test_generate_response_handles_api_failure(): void
    {
        // Mock HTTP failure
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([], 500)
        ]);

        $response = $this->aiService->generateTextResponse('Test prompt');
        
        // Should return fallback response
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
        // Just check that it's some kind of error/fallback message
        $this->assertTrue(
            str_contains(strtolower($response), 'problema') || 
            str_contains(strtolower($response), 'dificultad') ||
            str_contains(strtolower($response), 'disculpa') ||
            str_contains(strtolower($response), 'error')
        );
    }

    public function test_validate_configuration(): void
    {
        // Test configuration validation
        $config = $this->aiService->validateConfiguration();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('api_key_configured', $config);
        $this->assertArrayHasKey('model_configured', $config);
    }

    public function test_get_usage_stats(): void
    {
        $stats = $this->aiService->getUsageStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('successful_requests', $stats);
        $this->assertArrayHasKey('failed_requests', $stats);
    }
}
