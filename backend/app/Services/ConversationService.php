<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Facades\AI;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Create a new conversation for a user
     */
    public function createConversation(User $user, string $title = null): Conversation
    {
        try {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'title' => $title ?? 'Nueva conversación',
                'status' => 'active',
                'metadata' => []
            ]);

            Log::info('New conversation created', [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'title' => $conversation->title
            ]);

            return $conversation;
        } catch (Exception $e) {
            Log::error('Failed to create conversation', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user's conversations with pagination
     */
    public function getUserConversations(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->conversations()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a specific conversation with messages
     */
    public function getConversation(int $conversationId, User $user): ?Conversation
    {
        return $user->conversations()
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->find($conversationId);
    }

    /**
     * Process a user message and generate AI response
     */
    public function processMessage(
        Conversation $conversation, 
        string $content, 
        array $metadata = []
    ): Message {
        DB::beginTransaction();
        
        try {
            // Create user message
            $userMessage = $this->createMessage(
                $conversation,
                'user',
                $content,
                $metadata
            );

            // Get conversation context for AI
            $context = $this->buildConversationContext($conversation);
            
            // Generate AI response
            $aiResponse = $this->generateAIResponse($content, $context);
            
            // Create AI message
            $aiMessage = $this->createMessage(
                $conversation,
                'assistant',
                $aiResponse['content'],
                $aiResponse['metadata'] ?? []
            );

            // Update conversation title if it's the first exchange
            if ($conversation->messages()->count() <= 2) {
                $this->updateConversationTitle($conversation, $content);
            }

            // Update conversation last activity
            $conversation->touch();

            DB::commit();

            Log::info('Message processed successfully', [
                'conversation_id' => $conversation->id,
                'user_message_id' => $userMessage->id,
                'ai_message_id' => $aiMessage->id
            ]);

            return $aiMessage;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process message', [
                'conversation_id' => $conversation->id,
                'content' => substr($content, 0, 100),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Create a message in a conversation
     */
    public function createMessage(
        Conversation $conversation,
        string $role,
        string $content,
        array $metadata = []
    ): Message {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata
        ]);

        return $message;
    }

    /**
     * Generate AI response using the AI service
     */
    private function generateAIResponse(string $userMessage, array $context): array
    {
        try {
            // Check if the message is weather-related
            $weatherData = null;
            if ($this->isWeatherQuery($userMessage)) {
                $location = $this->extractLocationFromMessage($userMessage);
                if ($location) {
                    $weatherData = $this->weatherService->getCurrentWeather($location);
                }
            }

            // Prepare the prompt with context and weather data
            $prompt = $this->buildAIPrompt($userMessage, $context, $weatherData);
            
            // Get AI response
            $response = AI::generateResponse($prompt);

            return [
                'content' => $response,
                'metadata' => [
                    'weather_data_used' => !is_null($weatherData),
                    'location' => $location ?? null,
                    'response_tokens' => strlen($response),
                    'generated_at' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('AI response generation failed', [
                'user_message' => substr($userMessage, 0, 100),
                'error' => $e->getMessage()
            ]);

            // Return fallback response
            return [
                'content' => 'Lo siento, no pude procesar tu consulta en este momento. Por favor, inténtalo de nuevo.',
                'metadata' => [
                    'is_fallback' => true,
                    'error' => 'AI service unavailable'
                ]
            ];
        }
    }

    /**
     * Build conversation context for AI
     */
    private function buildConversationContext(Conversation $conversation): array
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10) // Last 10 messages for context
            ->get()
            ->reverse()
            ->values();

        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
                'timestamp' => $message->created_at->toISOString()
            ];
        })->toArray();
    }

    /**
     * Build AI prompt with context and weather data
     */
    private function buildAIPrompt(string $userMessage, array $context, ?array $weatherData): string
    {
        $prompt = "Usuario: {$userMessage}\n\n";

        // Add conversation context
        if (!empty($context)) {
            $prompt .= "Contexto de la conversación:\n";
            foreach ($context as $msg) {
                $role = $msg['role'] === 'user' ? 'Usuario' : 'Asistente';
                $prompt .= "{$role}: {$msg['content']}\n";
            }
            $prompt .= "\n";
        }

        // Add weather data if available
        if ($weatherData) {
            $prompt .= "Datos meteorológicos actuales:\n";
            $prompt .= "Ubicación: {$weatherData['location']}\n";
            $prompt .= "Temperatura: {$weatherData['temperature']}°C\n";
            $prompt .= "Descripción: {$weatherData['description']}\n";
            $prompt .= "Humedad: {$weatherData['humidity']}%\n";
            $prompt .= "Viento: {$weatherData['wind_speed']} km/h\n\n";
        }

        return $prompt;
    }

    /**
     * Check if a message is weather-related
     */
    private function isWeatherQuery(string $message): bool
    {
        $weatherKeywords = [
            'clima', 'tiempo', 'temperatura', 'lluvia', 'sol', 'nublado',
            'weather', 'temperature', 'rain', 'sunny', 'cloudy',
            '°c', '°f', 'grados', 'calor', 'frío', 'viento', 'humedad'
        ];

        $lowerMessage = strtolower($message);
        
        foreach ($weatherKeywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract location from user message
     */
    private function extractLocationFromMessage(string $message): ?string
    {
        // Simple location extraction - could be improved with NLP
        $patterns = [
            '/en ([A-Za-zÀ-ÿ\s,]+)/',
            '/de ([A-Za-zÀ-ÿ\s,]+)/',
            '/clima (?:en|de) ([A-Za-zÀ-ÿ\s,]+)/',
            '/tiempo (?:en|de) ([A-Za-zÀ-ÿ\s,]+)/',
            '/([A-Za-zÀ-ÿ\s,]+) clima/',
            '/([A-Za-zÀ-ÿ\s,]+) tiempo/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Update conversation title based on first message
     */
    private function updateConversationTitle(Conversation $conversation, string $firstMessage): void
    {
        try {
            // Generate a short title from the first message
            $title = $this->generateConversationTitle($firstMessage);
            
            $conversation->update(['title' => $title]);
            
        } catch (Exception $e) {
            Log::warning('Failed to update conversation title', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a title for the conversation
     */
    private function generateConversationTitle(string $message): string
    {
        // Truncate and clean the message for title
        $title = substr(trim($message), 0, 50);
        
        // Remove line breaks and extra spaces
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Add ellipsis if truncated
        if (strlen($message) > 50) {
            $title .= '...';
        }

        return $title ?: 'Conversación sin título';
    }

    /**
     * Delete a conversation and all its messages
     */
    public function deleteConversation(Conversation $conversation): bool
    {
        try {
            DB::beginTransaction();

            // Delete all messages first
            $conversation->messages()->delete();
            
            // Delete the conversation
            $deleted = $conversation->delete();

            DB::commit();

            Log::info('Conversation deleted', [
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id
            ]);

            return $deleted;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete conversation', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Archive a conversation
     */
    public function archiveConversation(Conversation $conversation): bool
    {
        try {
            $updated = $conversation->update(['status' => 'archived']);

            Log::info('Conversation archived', [
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id
            ]);

            return $updated;

        } catch (Exception $e) {
            Log::error('Failed to archive conversation', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(Conversation $conversation): array
    {
        return [
            'total_messages' => $conversation->messages()->count(),
            'user_messages' => $conversation->messages()->where('role', 'user')->count(),
            'assistant_messages' => $conversation->messages()->where('role', 'assistant')->count(),
            'created_at' => $conversation->created_at,
            'last_activity' => $conversation->updated_at,
            'duration_days' => $conversation->created_at->diffInDays(now()),
            'avg_message_length' => $conversation->messages()->avg(DB::raw('LENGTH(content)'))
        ];
    }

    /**
     * Search messages in conversations
     */
    public function searchMessages(User $user, string $query, int $perPage = 10): LengthAwarePaginator
    {
        return Message::whereHas('conversation', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('content', 'LIKE', "%{$query}%")
            ->with(['conversation'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent messages across all user conversations
     */
    public function getRecentMessages(User $user, int $limit = 50): Collection
    {
        return Message::whereHas('conversation', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['conversation'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
