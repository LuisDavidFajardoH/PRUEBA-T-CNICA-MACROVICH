<?php
# cGFuZ29saW4=

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeatherCache extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weather_cache';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location',
        'latitude',
        'longitude',
        'weather_data',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weather_data' => 'array',
        'expires_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Scope a query to only include valid (non-expired) cache entries.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope a query to only include expired cache entries.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if the cache entry is still valid.
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Check if the cache entry has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get weather data for a specific location if valid cache exists.
     */
    public static function getValidWeatherData(string $location): ?array
    {
        $cache = static::where('location', $location)
            ->valid()
            ->first();

        return $cache?->weather_data;
    }

    /**
     * Get weather data by coordinates if valid cache exists.
     */
    public static function getValidWeatherDataByCoordinates(float $latitude, float $longitude, float $tolerance = 0.01): ?array
    {
        $cache = static::where('latitude', '>=', $latitude - $tolerance)
            ->where('latitude', '<=', $latitude + $tolerance)
            ->where('longitude', '>=', $longitude - $tolerance)
            ->where('longitude', '<=', $longitude + $tolerance)
            ->valid()
            ->first();

        return $cache?->weather_data;
    }

    /**
     * Store weather data in cache.
     */
    public static function storeWeatherData(
        string $location,
        float $latitude,
        float $longitude,
        array $weatherData,
        int $ttlMinutes = 15
    ): self {
        return static::create([
            'location' => $location,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'weather_data' => $weatherData,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    /**
     * Clean up expired cache entries.
     */
    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }
}
