<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Get current weather for a location
     */
    public function current(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'units' => 'nullable|in:metric,imperial,kelvin'
        ]);

        try {
            $location = $request->input('location');
            $units = $request->input('units', 'metric');

            $weather = $this->weatherService->getCurrentWeather($location, $units);

            if (!$weather) {
                return response()->json([
                    'message' => 'No se pudo obtener el clima para la ubicación especificada',
                    'error' => 'location_not_found'
                ], 404);
            }

            return response()->json([
                'data' => $weather,
                'meta' => [
                    'location_query' => $location,
                    'units' => $units,
                    'cached' => $weather['cached'] ?? false,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Weather request failed', [
                'location' => $request->input('location', 'unknown'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener información meteorológica',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get weather forecast for a location
     */
    public function forecast(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'days' => 'nullable|integer|min:1|max:7',
            'units' => 'nullable|in:metric,imperial,kelvin'
        ]);

        try {
            $location = $request->input('location');
            $days = $request->integer('days', 5);
            $units = $request->input('units', 'metric');

            $forecast = $this->weatherService->getForecast($location, $days, $units);

            if (!$forecast) {
                return response()->json([
                    'message' => 'No se pudo obtener el pronóstico para la ubicación especificada',
                    'error' => 'location_not_found'
                ], 404);
            }

            return response()->json([
                'data' => $forecast,
                'meta' => [
                    'location_query' => $location,
                    'days' => $days,
                    'units' => $units,
                    'cached' => $forecast['cached'] ?? false,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Weather forecast request failed', [
                'location' => $request->input('location', 'unknown'),
                'days' => $request->input('days', 5),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener pronóstico meteorológico',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Search for locations
     */
    public function searchLocations(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);

        try {
            $query = $request->input('query');
            $locations = $this->weatherService->searchLocations($query);

            return response()->json([
                'data' => $locations,
                'meta' => [
                    'query' => $query,
                    'count' => count($locations),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Location search failed', [
                'query' => $request->input('query', 'unknown'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al buscar ubicaciones',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get weather service health status
     */
    public function health(): JsonResponse
    {
        try {
            $health = $this->weatherService->healthCheck();

            $statusCode = $health['status'] === 'healthy' ? 200 : 503;

            return response()->json([
                'data' => $health,
                'timestamp' => now()->toISOString()
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('Weather service health check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'data' => [
                    'status' => 'unhealthy',
                    'message' => 'Health check failed',
                    'error' => app()->environment('local') ? $e->getMessage() : 'Service unavailable'
                ],
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * Get weather service statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->weatherService->getStats();

            return response()->json([
                'data' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Weather service stats failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener estadísticas del servicio meteorológico',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Clear weather cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'nullable|string|max:255',
            'all' => 'nullable|boolean'
        ]);

        try {
            $location = $request->input('location');
            $clearAll = $request->boolean('all', false);

            if ($clearAll) {
                $cleared = $this->weatherService->clearAllCache();
                $message = "Se limpió toda la caché meteorológica. {$cleared} entradas eliminadas.";
            } elseif ($location) {
                $cleared = $this->weatherService->clearLocationCache($location);
                $message = $cleared 
                    ? "Se limpió la caché para la ubicación: {$location}"
                    : "No se encontró caché para la ubicación: {$location}";
            } else {
                return response()->json([
                    'message' => 'Debe especificar una ubicación o usar all=true'
                ], 422);
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'cleared' => $cleared,
                    'location' => $location,
                    'all' => $clearAll
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Weather cache clear failed', [
                'location' => $request->input('location'),
                'all' => $request->boolean('all'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al limpiar la caché meteorológica',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
