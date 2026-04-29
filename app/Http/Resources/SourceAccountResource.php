<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourceAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'identifier' => $this->identifier,
            'enabled' => (bool) $this->enabled,
            'settings' => $this->settings,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'last_sync_status' => $this->last_sync_status,
            'created_at' => $this->created_at?->toIso8601String(),
            // credentials are intentionally NOT exposed.
        ];
    }
}
