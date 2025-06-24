<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'messages_count' => $this->when(
                $this->relationLoaded('messages'),
                fn () => $this->messages->count()
            ),
            'last_message' => $this->when(
                $this->relationLoaded('messages') && $this->messages->isNotEmpty(),
                fn () => new MessageResource($this->messages->last())
            ),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'metadata' => $this->metadata,
            'stats' => $this->when(
                isset($this->stats),
                $this->stats
            ),
        ];
    }
}
