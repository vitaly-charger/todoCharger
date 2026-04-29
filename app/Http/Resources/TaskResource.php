<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $external = $this->whenLoaded('externalMappings', $this->externalMappings)
            ?? $this->externalMappings()->first();
        $first = is_iterable($external) ? collect($external)->first() : $external;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'needs_review' => (bool) $this->needs_review,
            'source' => [
                'type' => $this->source_type,
                'account_id' => $this->source_account_id,
                'account' => $this->whenLoaded('sourceAccount', fn () => [
                    'id' => $this->sourceAccount->id,
                    'name' => $this->sourceAccount->name,
                    'identifier' => $this->sourceAccount->identifier,
                ]),
                'external_id' => $this->source_external_id,
                'url' => $this->source_url,
            ],
            'ai' => [
                'summary' => $this->ai_summary,
                'reasoning_short' => $this->ai_reasoning_short,
                'follow_up_suggestion' => $this->follow_up_suggestion,
                'confidence' => $this->ai_confidence,
            ],
            'context_text' => $this->context_text,
            'external_sync' => $first ? [
                'status' => $this->external_sync_status,
                'provider' => $first->provider,
                'external_id' => $first->external_id,
                'url' => $first->external_url,
                'last_synced_at' => $first->last_synced_at?->toIso8601String(),
            ] : [
                'status' => $this->external_sync_status,
                'provider' => null,
                'external_id' => null,
                'url' => null,
                'last_synced_at' => $this->external_last_synced_at?->toIso8601String(),
            ],
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
