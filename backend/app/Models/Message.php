<?php
# cGFuZ29saW4=

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
        'tokens_used',
        'processing_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'processing_time' => 'decimal:3',
    ];

    /**
     * The possible roles for a message.
     */
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeFromUser($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    /**
     * Scope a query to only include assistant messages.
     */
    public function scopeFromAssistant($query)
    {
        return $query->where('role', self::ROLE_ASSISTANT);
    }

    /**
     * Scope a query to only include system messages.
     */
    public function scopeFromSystem($query)
    {
        return $query->where('role', self::ROLE_SYSTEM);
    }

    /**
     * Check if the message is from a user.
     */
    public function isFromUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if the message is from the assistant.
     */
    public function isFromAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Check if the message is a system message.
     */
    public function isFromSystem(): bool
    {
        return $this->role === self::ROLE_SYSTEM;
    }
}
