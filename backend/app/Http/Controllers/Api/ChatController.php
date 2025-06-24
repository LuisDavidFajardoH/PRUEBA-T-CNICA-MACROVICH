<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateConversationRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    /**
     * Get all conversations for the authenticated user
     */
    public function conversations(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 15);
        $conversations = $this->conversationService->getUserConversations(
            Auth::user(),
            $perPage
        );

        return ConversationResource::collection($conversations);
    }

    /**
     * Create a new conversation
     */
    public function createConversation(CreateConversationRequest $request): JsonResponse
    {
        try {
            $conversation = $this->conversationService->createConversation(
                Auth::user(),
                $request->validated('title')
            );

            // Process initial message if provided
            if ($request->has('initial_message')) {
                $aiMessage = $this->conversationService->processMessage(
                    $conversation,
                    $request->validated('initial_message')
                );

                // Reload conversation with messages
                $conversation->load('messages');
            }

            return response()->json([
                'message' => 'Conversación creada exitosamente',
                'data' => new ConversationResource($conversation)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create conversation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al crear la conversación',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get a specific conversation with messages
     */
    public function getConversation(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationService->getConversation(
            $conversationId,
            Auth::user()
        );

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        return response()->json([
            'data' => new ConversationResource($conversation)
        ]);
    }

    /**
     * Send a message to a conversation
     */
    public function sendMessage(SendMessageRequest $request, int $conversationId): JsonResponse
    {
        $conversation = $this->conversationService->getConversation(
            $conversationId,
            Auth::user()
        );

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        try {
            $aiMessage = $this->conversationService->processMessage(
                $conversation,
                $request->validated('content'),
                $request->validated('metadata', [])
            );

            // Get the user message as well
            $userMessage = $conversation->messages()
                ->where('role', 'user')
                ->latest()
                ->first();

            return response()->json([
                'message' => 'Mensaje procesado exitosamente',
                'data' => [
                    'user_message' => new MessageResource($userMessage),
                    'ai_message' => new MessageResource($aiMessage),
                    'conversation' => new ConversationResource($conversation->fresh())
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process message', [
                'conversation_id' => $conversationId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al procesar el mensaje',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationService->getConversation(
            $conversationId,
            Auth::user()
        );

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        try {
            $this->conversationService->deleteConversation($conversation);

            return response()->json([
                'message' => 'Conversación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete conversation', [
                'conversation_id' => $conversationId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la conversación',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Archive a conversation
     */
    public function archiveConversation(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationService->getConversation(
            $conversationId,
            Auth::user()
        );

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        try {
            $this->conversationService->archiveConversation($conversation);

            return response()->json([
                'message' => 'Conversación archivada exitosamente',
                'data' => new ConversationResource($conversation->fresh())
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to archive conversation', [
                'conversation_id' => $conversationId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al archivar la conversación',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationService->getConversation(
            $conversationId,
            Auth::user()
        );

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        $stats = $this->conversationService->getConversationStats($conversation);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Search messages in user's conversations
     */
    public function searchMessages(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        $query = $request->input('query');
        $perPage = $request->integer('per_page', 10);

        $messages = $this->conversationService->searchMessages(
            Auth::user(),
            $query,
            $perPage
        );

        return response()->json([
            'data' => MessageResource::collection($messages),
            'meta' => [
                'query' => $query,
                'total' => $messages->total(),
                'per_page' => $messages->perPage(),
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage()
            ]
        ]);
    }

    /**
     * Get recent messages across all conversations
     */
    public function recentMessages(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $limit = min($limit, 100); // Max 100 messages

        $messages = $this->conversationService->getRecentMessages(
            Auth::user(),
            $limit
        );

        return response()->json([
            'data' => MessageResource::collection($messages),
            'meta' => [
                'limit' => $limit,
                'count' => $messages->count()
            ]
        ]);
    }
}
