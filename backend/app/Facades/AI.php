<?php
# cGFuZ29saW4=

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateResponse(string $userMessage, array $conversationHistory = [], bool $includeWeatherFunction = true)
 * @method static array generateWeatherResponse(string $userMessage, array $conversationHistory = [])
 * @method static string generateWeatherResponseText(string $userMessage, array $conversationHistory = [])
 * @method static string generateWeatherResponseSimple(string $userMessage)
 * @method static bool detectPromptInjection(string $input)
 * @method static string sanitizeInput(string $input)
 * @method static array getHealthStatus()
 *
 * @see \App\Services\AIService
 */
class AI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ai';
    }
}
