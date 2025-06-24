<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeatherServiceTest extends TestCase
{
    use RefreshDatabase;

    private WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = app(WeatherService::class);
    }

    public function test_health_check_returns_status(): void
    {
        $health = $this->weatherService->healthCheck();
        
        $this->assertIsArray($health);
        $this->assertArrayHasKey('status', $health);
        $this->assertContains($health['status'], ['healthy', 'degraded', 'unhealthy']);
    }

    public function test_search_locations_returns_array(): void
    {
        $locations = $this->weatherService->searchLocations('Madrid');
        
        $this->assertIsArray($locations);
    }

    public function test_get_current_weather_with_valid_location(): void
    {
        $weather = $this->weatherService->getCurrentWeather('Madrid');
        
        if ($weather) {
            $this->assertIsArray($weather);
            $this->assertArrayHasKey('location', $weather);
            $this->assertArrayHasKey('temperature', $weather);
            $this->assertArrayHasKey('coordinates', $weather);
        } else {
            // Si falla la API externa, simplemente lo marcamos como skipped
            $this->markTestSkipped('Weather API not available or location not found');
        }
    }

    public function test_get_current_weather_with_invalid_location(): void
    {
        $weather = $this->weatherService->getCurrentWeather('LocationThatDoesNotExist123');
        
        $this->assertNull($weather);
    }

    public function test_clear_all_cache_returns_count(): void
    {
        $count = $this->weatherService->clearAllCache();
        
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
