<?php

namespace App\Console\Commands;

use App\Facades\AI;
use App\Services\AIService;
use Illuminate\Console\Command;

class TestGeminiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:gemini {message? : The message to send to Gemini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gemini AI integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Testing Gemini AI Integration...');
        $this->newLine();

        // Test 1: Configuration
        $this->info('1. Checking Configuration...');
        $config = AI::validateConfiguration();
        
        $this->table(['Setting', 'Status'], [
            ['API Key Configured', $config['api_key_configured'] ? '✅ Yes' : '❌ No'],
            ['Model Configured', $config['model_configured'] ? '✅ Yes' : '❌ No'],
            ['Model', $config['model']],
            ['API Key Length', $config['api_key_length'] . ' characters'],
        ]);

        if (!$config['api_key_configured']) {
            $this->error('⚠️  Gemini API key is not configured!');
            $this->info('Please set your GEMINI_API_KEY in the .env file.');
            $this->info('Get your key from: https://makersuite.google.com/app/apikey');
            return 1;
        }

        $this->newLine();

        // Test 2: Health Check
        $this->info('2. Performing Health Check...');
        $health = AI::healthCheck();
        
        $statusIcon = match($health['status']) {
            'healthy' => '✅',
            'degraded' => '⚠️',
            'unhealthy' => '❌',
            default => '❓'
        };

        $this->info("Status: {$statusIcon} {$health['status']}");
        if (isset($health['response_time'])) {
            $this->info("Response Time: {$health['response_time']}ms");
        }
        if (isset($health['error'])) {
            $this->error("Error: {$health['error']}");
        }

        $this->newLine();

        // Test 3: Basic Response
        $this->info('3. Testing Basic Response...');
        $message = $this->argument('message') ?? 'Hola, ¿puedes ayudarme con el clima?';
        
        $this->info("Sending: {$message}");
        $this->newLine();

        try {
            $startTime = microtime(true);
            $response = AI::generateTextResponse($message);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->info('📝 Response received:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line($response);
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info("⏱️  Response time: {$responseTime}ms");

        } catch (\Exception $e) {
            $this->error('❌ Error generating response:');
            $this->error($e->getMessage());
            return 1;
        }

        $this->newLine();

        // Test 4: Weather-related query
        $this->info('4. Testing Weather Query...');
        $weatherQuery = '¿Cuál es el clima actual en Madrid?';
        $this->info("Sending: {$weatherQuery}");

        try {
            $startTime = microtime(true);
            $weatherResponse = AI::generateTextResponse($weatherQuery);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->info('🌤️  Weather Response:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line($weatherResponse);
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info("⏱️  Response time: {$responseTime}ms");

        } catch (\Exception $e) {
            $this->error('❌ Error with weather query:');
            $this->error($e->getMessage());
        }

        $this->newLine();

        // Test 5: Usage Stats
        $this->info('5. Usage Statistics...');
        $stats = AI::getUsageStats();
        
        $this->table(['Metric', 'Value'], [
            ['Total Requests', $stats['total_requests']],
            ['Successful Requests', $stats['successful_requests']],
            ['Failed Requests', $stats['failed_requests']],
            ['Average Response Time', $stats['average_response_time'] . 'ms'],
            ['Last Request', $stats['last_request_time'] ?? 'Never'],
        ]);

        $this->newLine();
        $this->info('✅ Gemini AI test completed!');

        if ($health['status'] === 'healthy') {
            $this->info('🎉 All systems operational!');
            return 0;
        } else {
            $this->warn('⚠️  Some issues detected. Check the output above.');
            return 1;
        }
    }
}
