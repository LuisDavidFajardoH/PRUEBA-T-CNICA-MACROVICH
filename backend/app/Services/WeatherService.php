<?php
# cGFuZ29saW4=

namespace App\Services;

use App\Models\WeatherCache;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class WeatherService
{
    private string $baseUrl;
    private string $geocodingUrl;
    private int $weatherCacheTtl;
    private int $geocodingCacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('services.openmeteo.base_url');
        $this->geocodingUrl = config('services.openmeteo.geocoding_url');
        $this->weatherCacheTtl = config('services.openmeteo.weather_cache_ttl', 900); // 15 minutes
        $this->geocodingCacheTtl = config('services.openmeteo.geocoding_cache_ttl', 86400); // 24 hours
    }

    /**
     * Get weather data for a location
     */
    public function getWeatherData(
        string $location,
        bool $includeForecast = true,
        int $forecastDays = 3
    ): array {
        try {
            // First, get coordinates from location
            $coordinates = $this->getCoordinatesFromLocation($location);
            
            if (!$coordinates) {
                return [
                    'success' => false,
                    'error' => 'Location not found',
                    'message' => "Lo siento, no pude encontrar la ubicación '{$location}'. ¿Podrías verificar el nombre de la ciudad? 🗺️"
                ];
            }

            // Check cache first
            $cachedWeather = WeatherCache::getValidWeatherDataByCoordinates(
                $coordinates['latitude'],
                $coordinates['longitude']
            );

            if ($cachedWeather) {
                Log::info('Weather data served from cache', [
                    'location' => $location,
                    'coordinates' => $coordinates
                ]);

                return [
                    'success' => true,
                    'data' => $cachedWeather,
                    'source' => 'cache',
                    'location' => $coordinates['display_name'] ?? $location,
                    'coordinates' => $coordinates
                ];
            }

            // Fetch fresh weather data
            $weatherData = $this->fetchWeatherFromAPI(
                $coordinates['latitude'],
                $coordinates['longitude'],
                $includeForecast,
                $forecastDays
            );

            if (!$weatherData['success']) {
                return $weatherData;
            }

            // Cache the weather data
            WeatherCache::storeWeatherData(
                $coordinates['display_name'] ?? $location,
                $coordinates['latitude'],
                $coordinates['longitude'],
                $weatherData['data'],
                $this->weatherCacheTtl / 60 // Convert to minutes
            );

            Log::info('Fresh weather data fetched and cached', [
                'location' => $location,
                'coordinates' => $coordinates
            ]);

            return [
                'success' => true,
                'data' => $weatherData['data'],
                'source' => 'api',
                'location' => $coordinates['display_name'] ?? $location,
                'coordinates' => $coordinates
            ];

        } catch (Exception $e) {
            Log::error('Weather service error', [
                'location' => $location,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Lo siento, tengo problemas técnicos para obtener los datos del clima. ¿Podrías intentar de nuevo en unos minutos? 🔧'
            ];
        }
    }

    /**
     * Get current weather data for a location (simplified method)
     */
    public function getCurrentWeather(string $location, string $units = 'metric'): ?array
    {
        try {
            // Use the existing getWeatherData method
            $weatherData = $this->getWeatherData($location, false, 0);
            
            if (!$weatherData['success']) {
                return null;
            }

            // Extract current weather from the response
            $data = $weatherData['data'] ?? [];
            $current = $data['current'] ?? [];
            $locationData = $weatherData['coordinates'] ?? [];
            
            return [
                'location' => $weatherData['location'] ?? $location,
                'country' => $locationData['country'] ?? '',
                'coordinates' => [
                    'latitude' => $locationData['latitude'] ?? 0,
                    'longitude' => $locationData['longitude'] ?? 0
                ],
                'temperature' => round($current['temperature_2m'] ?? 0, 1),
                'feels_like' => round($current['apparent_temperature'] ?? 0, 1),
                'humidity' => $current['relative_humidity_2m'] ?? 0,
                'pressure' => $current['surface_pressure'] ?? 0,
                'wind_speed' => round($current['wind_speed_10m'] ?? 0, 1),
                'wind_direction' => $current['wind_direction_10m'] ?? 0,
                'visibility' => $current['visibility'] ?? 0,
                'uv_index' => $current['uv_index'] ?? 0,
                'weather_code' => $current['weather_code'] ?? 0,
                'description' => $this->getWeatherDescription($current['weather_code'] ?? 0),
                'icon' => $this->getWeatherIcon($current['weather_code'] ?? 0),
                'is_day' => ($current['is_day'] ?? 1) == 1,
                'units' => $units,
                'cached' => $weatherData['source'] === 'cache',
                'timestamp' => now()->toISOString()
            ];

        } catch (Exception $e) {
            Log::error('Failed to get current weather', [
                'location' => $location,
                'units' => $units,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get weather icon based on weather code
     */
    private function getWeatherIcon(int $weatherCode): string
    {
        $iconMap = [
            0 => 'clear-day',
            1 => 'partly-cloudy-day',
            2 => 'cloudy',
            3 => 'overcast',
            45 => 'fog',
            48 => 'fog',
            51 => 'drizzle',
            53 => 'drizzle',
            55 => 'drizzle',
            56 => 'sleet',
            57 => 'sleet',
            61 => 'rain',
            63 => 'rain',
            65 => 'rain',
            66 => 'sleet',
            67 => 'sleet',
            71 => 'snow',
            73 => 'snow',
            75 => 'snow',
            77 => 'hail',
            80 => 'rain',
            81 => 'rain',
            82 => 'rain',
            85 => 'snow',
            86 => 'snow',
            95 => 'thunderstorm',
            96 => 'thunderstorm',
            99 => 'thunderstorm'
        ];

        return $iconMap[$weatherCode] ?? 'unknown';
    }

    /**
     * Get coordinates from location name using geocoding
     */
    private function getCoordinatesFromLocation(string $location): ?array
    {
        $cacheKey = 'geocoding_' . md5(strtolower($location));
        
        return Cache::remember($cacheKey, $this->geocodingCacheTtl, function () use ($location) {
            try {
                $response = Http::timeout(10)
                    ->get("{$this->geocodingUrl}/search", [
                        'name' => $location,
                        'count' => 1,
                        'language' => 'es',
                        'format' => 'json'
                    ]);

                if (!$response->successful()) {
                    Log::warning('Geocoding API request failed', [
                        'location' => $location,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $data = $response->json();
                
                if (empty($data['results'])) {
                    Log::info('Location not found in geocoding', ['location' => $location]);
                    return null;
                }

                $result = $data['results'][0];
                
                return [
                    'latitude' => (float) $result['latitude'],
                    'longitude' => (float) $result['longitude'],
                    'display_name' => $result['name'] ?? $location,
                    'country' => $result['country'] ?? null,
                    'admin1' => $result['admin1'] ?? null,
                    'admin2' => $result['admin2'] ?? null,
                ];

            } catch (Exception $e) {
                Log::error('Geocoding error', [
                    'location' => $location,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Fetch weather data from Open-Meteo API
     */
    private function fetchWeatherFromAPI(
        float $latitude,
        float $longitude,
        bool $includeForecast,
        int $forecastDays
    ): array {
        try {
            $params = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current_weather' => 'true',
                'timezone' => 'auto',
                'current' => [
                    'temperature_2m',
                    'relative_humidity_2m',
                    'apparent_temperature',
                    'is_day',
                    'precipitation',
                    'weather_code',
                    'cloud_cover',
                    'pressure_msl',
                    'surface_pressure',
                    'wind_speed_10m',
                    'wind_direction_10m',
                    'wind_gusts_10m'
                ]
            ];

            if ($includeForecast) {
                $params['daily'] = [
                    'weather_code',
                    'temperature_2m_max',
                    'temperature_2m_min',
                    'apparent_temperature_max',
                    'apparent_temperature_min',
                    'precipitation_sum',
                    'precipitation_hours',
                    'wind_speed_10m_max',
                    'wind_gusts_10m_max',
                    'wind_direction_10m_dominant',
                    'uv_index_max'
                ];
                $params['forecast_days'] = min($forecastDays, 7);
            }

            // Convert arrays to comma-separated strings
            foreach (['current', 'daily' ] as $key) {
                if (isset($params[$key]) && is_array($params[$key])) {
                    $params[$key] = implode(',', $params[$key]);
                }
            }

            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/forecast", $params);

            if (!$response->successful()) {
                throw new Exception(
                    'Open-Meteo API request failed (Status: ' . $response->status() . ')'
                );
            }

            $data = $response->json();
            
            // Process and format the weather data
            $processedData = $this->processWeatherData($data);

            return [
                'success' => true,
                'data' => $processedData
            ];

        } catch (Exception $e) {
            Log::error('Weather API fetch error', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process and format weather data from API response
     */
    private function processWeatherData(array $data): array
    {
        $processed = [
            'timestamp' => now()->toISOString(),
            'timezone' => $data['timezone'] ?? 'UTC',
            'current' => null,
            'daily' => []
        ];

        // Process current weather
        if (isset($data['current'])) {
            $current = $data['current'];
            $processed['current'] = [
                'time' => $current['time'] ?? null,
                'temperature' => $current['temperature_2m'] ?? null,
                'apparent_temperature' => $current['apparent_temperature'] ?? null,
                'humidity' => $current['relative_humidity_2m'] ?? null,
                'precipitation' => $current['precipitation'] ?? null,
                'weather_code' => $current['weather_code'] ?? null,
                'weather_description' => $this->getWeatherDescription($current['weather_code'] ?? 0),
                'cloud_cover' => $current['cloud_cover'] ?? null,
                'pressure' => $current['pressure_msl'] ?? null,
                'wind_speed' => $current['wind_speed_10m'] ?? null,
                'wind_direction' => $current['wind_direction_10m'] ?? null,
                'wind_gusts' => $current['wind_gusts_10m'] ?? null,
                'is_day' => $current['is_day'] ?? null,
            ];
        }

        // Process daily forecast
        if (isset($data['daily'])) {
            $daily = $data['daily'];
            $timeArray = $daily['time'] ?? [];
            
            foreach ($timeArray as $index => $date) {
                $processed['daily'][] = [
                    'date' => $date,
                    'weather_code' => $daily['weather_code'][$index] ?? null,
                    'weather_description' => $this->getWeatherDescription($daily['weather_code'][$index] ?? 0),
                    'temperature_max' => $daily['temperature_2m_max'][$index] ?? null,
                    'temperature_min' => $daily['temperature_2m_min'][$index] ?? null,
                    'apparent_temperature_max' => $daily['apparent_temperature_max'][$index] ?? null,
                    'apparent_temperature_min' => $daily['apparent_temperature_min'][$index] ?? null,
                    'precipitation_sum' => $daily['precipitation_sum'][$index] ?? null,
                    'precipitation_hours' => $daily['precipitation_hours'][$index] ?? null,
                    'wind_speed_max' => $daily['wind_speed_10m_max'][$index] ?? null,
                    'wind_gusts_max' => $daily['wind_gusts_10m_max'][$index] ?? null,
                    'wind_direction' => $daily['wind_direction_10m_dominant'][$index] ?? null,
                    'uv_index_max' => $daily['uv_index_max'][$index] ?? null,
                ];
            }
        }

        return $processed;
    }

    /**
     * Convert weather code to human-readable description
     */
    private function getWeatherDescription(int $weatherCode): string
    {
        $weatherCodes = [
            0 => 'Despejado',
            1 => 'Mayormente despejado',
            2 => 'Parcialmente nublado',
            3 => 'Nublado',
            45 => 'Niebla',
            48 => 'Niebla con escarcha',
            51 => 'Llovizna ligera',
            53 => 'Llovizna moderada',
            55 => 'Llovizna intensa',
            56 => 'Llovizna helada ligera',
            57 => 'Llovizna helada intensa',
            61 => 'Lluvia ligera',
            63 => 'Lluvia moderada',
            65 => 'Lluvia intensa',
            66 => 'Lluvia helada ligera',
            67 => 'Lluvia helada intensa',
            71 => 'Nieve ligera',
            73 => 'Nieve moderada',
            75 => 'Nieve intensa',
            77 => 'Granizo',
            80 => 'Chubascos ligeros',
            81 => 'Chubascos moderados',
            82 => 'Chubascos intensos',
            85 => 'Chubascos de nieve ligeros',
            86 => 'Chubascos de nieve intensos',
            95 => 'Tormenta',
            96 => 'Tormenta con granizo ligero',
            99 => 'Tormenta con granizo intenso',
        ];

        return $weatherCodes[$weatherCode] ?? 'Condición desconocida';
    }

    /**
     * Format weather data for AI consumption
     */
    public function formatWeatherForAI(array $weatherData): string
    {
        if (!$weatherData['success']) {
            return "Error al obtener datos meteorológicos: " . ($weatherData['message'] ?? $weatherData['error']);
        }

        $data = $weatherData['data'];
        $location = $weatherData['location'];
        $formatted = "📍 **{$location}**\n\n";

        // Current weather
        if (isset($data['current'])) {
            $current = $data['current'];
            $formatted .= "🌡️ **Condiciones Actuales:**\n";
            $formatted .= "- Temperatura: {$current['temperature']}°C (sensación térmica: {$current['apparent_temperature']}°C)\n";
            $formatted .= "- Condición: {$current['weather_description']}\n";
            $formatted .= "- Humedad: {$current['humidity']}%\n";
            $formatted .= "- Viento: {$current['wind_speed']} km/h\n";
            
            if ($current['precipitation'] && $current['precipitation'] > 0) {
                $formatted .= "- Precipitación: {$current['precipitation']} mm\n";
            }
            
            $formatted .= "- Presión: {$current['pressure']} hPa\n";
            $formatted .= "- Nubosidad: {$current['cloud_cover']}%\n\n";
        }

        // Daily forecast
        if (!empty($data['daily'])) {
            $formatted .= "📅 **Pronóstico:**\n";
            foreach (array_slice($data['daily'], 0, 3) as $index => $day) {
                $date = date('j M', strtotime($day['date']));
                $formatted .= "- **{$date}**: {$day['weather_description']}, ";
                $formatted .= "Max: {$day['temperature_max']}°C, Min: {$day['temperature_min']}°C";
                
                if ($day['precipitation_sum'] && $day['precipitation_sum'] > 0) {
                    $formatted .= ", Lluvia: {$day['precipitation_sum']} mm";
                }
                
                $formatted .= "\n";
            }
        }

        $formatted .= "\n🕐 **Datos actualizados:** " . date('H:i') . " hrs";
        $formatted .= "\n📊 **Fuente:** Open-Meteo";

        return $formatted;
    }

    /**
     * Clean up expired weather cache entries
     */
    public function cleanupExpiredCache(): int
    {
        return WeatherCache::cleanupExpired();
    }

    /**
     * Get weather service health status
     */
    public function getHealthStatus(): array
    {
        $cacheKey = 'weather_service_health_check';
        
        return Cache::remember($cacheKey, 300, function () {
            try {
                // Test geocoding
                $coordinates = $this->getCoordinatesFromLocation('Madrid');
                $geocodingWorking = $coordinates !== null;

                // Test weather API if geocoding works
                $weatherWorking = false;
                $responseTime = null;
                
                if ($geocodingWorking) {
                    $startTime = microtime(true);
                    $weatherData = $this->fetchWeatherFromAPI(
                        $coordinates['latitude'],
                        $coordinates['longitude'],
                        false,
                        1
                    );
                    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                    $weatherWorking = $weatherData['success'];
                }

                $status = 'healthy';
                if (!$geocodingWorking || !$weatherWorking) {
                    $status = 'unhealthy';
                } elseif ($responseTime > 5000) { // 5 seconds
                    $status = 'degraded';
                }

                return [
                    'status' => $status,
                    'geocoding_working' => $geocodingWorking,
                    'weather_api_working' => $weatherWorking,
                    'response_time' => $responseTime,
                    'cache_entries' => WeatherCache::count(),
                    'expired_entries' => WeatherCache::expired()->count(),
                    'last_check' => now()->toISOString(),
                ];

            } catch (Exception $e) {
                return [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'geocoding_working' => false,
                    'weather_api_working' => false,
                    'last_check' => now()->toISOString(),
                ];
            }
        });
    }

    /**
     * Get weather statistics
     */
    public function getWeatherStats(): array
    {
        return [
            'total_cache_entries' => WeatherCache::count(),
            'valid_cache_entries' => WeatherCache::valid()->count(),
            'expired_cache_entries' => WeatherCache::expired()->count(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'most_requested_locations' => $this->getMostRequestedLocations(),
        ];
    }

    /**
     * Get cache hit rate (simplified approximation)
     */
    private function getCacheHitRate(): float
    {
        // This would require more sophisticated tracking in a real app
        // For now, return a placeholder
        return 0.75; // 75% hit rate
    }

    /**
     * Get most requested locations
     */
    private function getMostRequestedLocations(): array
    {
        return WeatherCache::select('location')
            ->groupBy('location')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->pluck('location')
            ->toArray();
    }

    /**
     * Health check for the weather service
     */
    public function healthCheck(): array
    {
        try {
            $startTime = microtime(true);
            
            // Test API connectivity with a simple request
            $response = Http::timeout(10)->get($this->baseUrl . '/forecast', [
                'latitude' => 40.7128,  // New York coordinates for test
                'longitude' => -74.0060,
                'current' => 'temperature_2m',
                'forecast_days' => 1
            ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'message' => 'Weather service is operational',
                    'response_time_ms' => $responseTime,
                    'api_status' => 'connected',
                    'cache_status' => $this->getCacheStatus(),
                    'last_check' => now()->toISOString()
                ];
            } else {
                return [
                    'status' => 'degraded',
                    'message' => 'Weather API returned non-200 status',
                    'response_time_ms' => $responseTime,
                    'api_status' => 'error',
                    'status_code' => $response->status(),
                    'cache_status' => $this->getCacheStatus(),
                    'last_check' => now()->toISOString()
                ];
            }

        } catch (Exception $e) {
            Log::error('Weather service health check failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'unhealthy',
                'message' => 'Weather service is not accessible',
                'error' => $e->getMessage(),
                'api_status' => 'disconnected',
                'cache_status' => $this->getCacheStatus(),
                'last_check' => now()->toISOString()
            ];
        }
    }

    /**
     * Get cache status information
     */
    private function getCacheStatus(): array
    {
        try {
            return [
                'total_entries' => WeatherCache::count(),
                'valid_entries' => WeatherCache::valid()->count(),
                'expired_entries' => WeatherCache::expired()->count(),
                'status' => 'operational'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear all weather cache
     */
    public function clearAllCache(): int
    {
        try {
            $count = WeatherCache::count();
            WeatherCache::truncate();
            
            Log::info('All weather cache cleared', [
                'entries_deleted' => $count
            ]);

            return $count;
        } catch (Exception $e) {
            Log::error('Failed to clear all weather cache', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Clear cache for a specific location
     */
    public function clearLocationCache(string $location): bool
    {
        try {
            $deleted = WeatherCache::where('location', 'LIKE', "%{$location}%")->delete();
            
            Log::info('Location cache cleared', [
                'location' => $location,
                'entries_deleted' => $deleted
            ]);

            return $deleted > 0;
        } catch (Exception $e) {
            Log::error('Failed to clear location cache', [
                'location' => $location,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get weather forecast for multiple days
     */
    public function getForecast(string $location, int $days = 5, string $units = 'metric'): ?array
    {
        try {
            $coordinates = $this->getCoordinatesFromLocation($location);
            
            if (!$coordinates) {
                return null;
            }

            $cacheKey = "forecast_" . md5($location . $days . $units);
            
            // Check cache first
            return Cache::remember($cacheKey, $this->weatherCacheTtl, function () use ($coordinates, $days, $units, $location) {
                $response = Http::timeout(30)->get($this->baseUrl . '/forecast', [
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                    'daily' => 'temperature_2m_max,temperature_2m_min,weather_code,precipitation_sum',
                    'timezone' => 'auto',
                    'forecast_days' => min($days, 16) // Max 16 days
                ]);

                if (!$response->successful()) {
                    Log::warning('Weather forecast API request failed', [
                        'location' => $location,
                        'status' => $response->status()
                    ]);
                    return null;
                }

                $data = $response->json();
                
                return [
                    'location' => $location,
                    'coordinates' => $coordinates,
                    'forecast' => $this->formatForecastData($data['daily']),
                    'units' => $units,
                    'cached' => false,
                    'retrieved_at' => now()->toISOString()
                ];
            });

        } catch (Exception $e) {
            Log::error('Weather forecast request failed', [
                'location' => $location,
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format forecast data for better readability
     */
    private function formatForecastData(array $dailyData): array
    {
        $forecast = [];
        $dates = $dailyData['time'] ?? [];
        $maxTemps = $dailyData['temperature_2m_max'] ?? [];
        $minTemps = $dailyData['temperature_2m_min'] ?? [];
        $weatherCodes = $dailyData['weather_code'] ?? [];
        $precipitation = $dailyData['precipitation_sum'] ?? [];

        for ($i = 0; $i < count($dates); $i++) {
            $forecast[] = [
                'date' => $dates[$i],
                'temperature_max' => round($maxTemps[$i] ?? 0, 1),
                'temperature_min' => round($minTemps[$i] ?? 0, 1),
                'weather_code' => $weatherCodes[$i] ?? 0,
                'description' => $this->getWeatherDescription($weatherCodes[$i] ?? 0),
                'precipitation' => round($precipitation[$i] ?? 0, 1)
            ];
        }

        return $forecast;
    }

    /**
     * Search for locations using geocoding
     */
    public function searchLocations(string $query): array
    {
        try {
            $cacheKey = "location_search_" . md5($query);
            
            return Cache::remember($cacheKey, $this->geocodingCacheTtl, function () use ($query) {
                $response = Http::timeout(10)->get($this->geocodingUrl . '/search', [
                    'name' => $query,
                    'count' => 10,
                    'language' => 'es',
                    'format' => 'json'
                ]);

                if (!$response->successful()) {
                    Log::warning('Geocoding search failed', [
                        'query' => $query,
                        'status' => $response->status()
                    ]);
                    return [];
                }

                $data = $response->json();
                $results = $data['results'] ?? [];

                return array_map(function ($location) {
                    return [
                        'name' => $location['name'],
                        'country' => $location['country'] ?? '',
                        'admin1' => $location['admin1'] ?? '',
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'] ?? '',
                        'population' => $location['population'] ?? null
                    ];
                }, $results);
            });

        } catch (Exception $e) {
            Log::error('Location search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
