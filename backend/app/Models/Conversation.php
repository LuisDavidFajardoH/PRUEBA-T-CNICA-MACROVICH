<?php
# cGFuZ29saW4=

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'is_active',
        'last_message_at',
        'status', // Add virtual status attribute
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_message_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Status accessor - converts is_active to status string
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'archived';
    }

    /**
     * Status mutator - converts status string to is_active boolean
     */
    public function setStatusAttribute(string $value): void
    {
        $this->attributes['is_active'] = $value === 'active';
    }

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest messages for the conversation.
     */
    public function latestMessages(): HasMany
    {
        return $this->messages()->latest();
    }

    /**
     * Update the conversation's last message timestamp.
     */
    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Scope a query to only include active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
