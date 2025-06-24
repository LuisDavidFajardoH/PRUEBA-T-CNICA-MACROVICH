<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConversationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConversationService $conversationService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationService = app(ConversationService::class);
        $this->user = User::factory()->create();
    }

    public function test_create_conversation_with_default_title(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals($this->user->id, $conversation->user_id);
        $this->assertEquals('Nueva conversación', $conversation->title);
        $this->assertEquals('active', $conversation->status);
    }

    public function test_create_conversation_with_custom_title(): void
    {
        $title = 'Mi conversación personalizada';
        $conversation = $this->conversationService->createConversation($this->user, $title);
        
        $this->assertEquals($title, $conversation->title);
    }

    public function test_get_user_conversations(): void
    {
        // Create some conversations
        $conversation1 = $this->conversationService->createConversation($this->user, 'Conversación 1');
        $conversation2 = $this->conversationService->createConversation($this->user, 'Conversación 2');
        
        $conversations = $this->conversationService->getUserConversations($this->user);
        
        $this->assertEquals(2, $conversations->total());
    }

    public function test_get_specific_conversation(): void
    {
        $conversation = $this->conversationService->createConversation($this->user, 'Test Conversation');
        
        $retrieved = $this->conversationService->getConversation($conversation->id, $this->user);
        
        $this->assertNotNull($retrieved);
        $this->assertEquals($conversation->id, $retrieved->id);
    }

    public function test_get_nonexistent_conversation_returns_null(): void
    {
        $retrieved = $this->conversationService->getConversation(999, $this->user);
        
        $this->assertNull($retrieved);
    }

    public function test_create_message_in_conversation(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        $message = $this->conversationService->createMessage(
            $conversation,
            'user',
            'Hello, this is a test message'
        );
        
        $this->assertEquals($conversation->id, $message->conversation_id);
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello, this is a test message', $message->content);
    }

    public function test_delete_conversation(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        // Add a message to test cascade delete
        $this->conversationService->createMessage($conversation, 'user', 'Test message');
        
        $result = $this->conversationService->deleteConversation($conversation);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('messages', ['conversation_id' => $conversation->id]);
    }

    public function test_archive_conversation(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        $result = $this->conversationService->archiveConversation($conversation);
        
        $this->assertTrue($result);
        $conversation->refresh();
        $this->assertEquals('archived', $conversation->status);
    }

    public function test_get_conversation_stats(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        // Add some messages
        $this->conversationService->createMessage($conversation, 'user', 'User message 1');
        $this->conversationService->createMessage($conversation, 'assistant', 'Assistant response');
        $this->conversationService->createMessage($conversation, 'user', 'User message 2');
        
        $stats = $this->conversationService->getConversationStats($conversation);
        
        $this->assertArrayHasKey('total_messages', $stats);
        $this->assertArrayHasKey('user_messages', $stats);
        $this->assertArrayHasKey('assistant_messages', $stats);
        $this->assertEquals(3, $stats['total_messages']);
        $this->assertEquals(2, $stats['user_messages']);
        $this->assertEquals(1, $stats['assistant_messages']);
    }

    public function test_search_messages(): void
    {
        $conversation1 = $this->conversationService->createConversation($this->user);
        $conversation2 = $this->conversationService->createConversation($this->user);
        
        $this->conversationService->createMessage($conversation1, 'user', 'How is the weather today?');
        $this->conversationService->createMessage($conversation2, 'user', 'What is the temperature?');
        $this->conversationService->createMessage($conversation2, 'user', 'Hello there');
        
        $results = $this->conversationService->searchMessages($this->user, 'weather');
        
        $this->assertEquals(1, $results->total());
    }

    public function test_get_recent_messages(): void
    {
        $conversation = $this->conversationService->createConversation($this->user);
        
        $message1 = $this->conversationService->createMessage($conversation, 'user', 'Message 1');
        sleep(1); // Small delay to ensure different timestamps
        $message2 = $this->conversationService->createMessage($conversation, 'assistant', 'Response 1');
        sleep(1); // Small delay to ensure different timestamps
        $message3 = $this->conversationService->createMessage($conversation, 'user', 'Message 2');
        
        $recentMessages = $this->conversationService->getRecentMessages($this->user, 10);
        
        $this->assertEquals(3, $recentMessages->count());
        // Messages should be ordered by created_at desc, so the last created should be first
        $this->assertEquals('Message 2', $recentMessages->first()->content);
    }
}
