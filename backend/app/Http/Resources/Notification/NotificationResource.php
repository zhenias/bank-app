<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /* @example "550e8400-e29b-41d4-a716-446655440000" */
            'id' => $this->id,
            /* @example "approved" */
            'type' => $this->data['type'] ?? $this->type,
            /* @example "Powiadomienie" */
            'title' => $this->data['title'] ?? 'Powiadomienie',
            /* @example "Treść powiadomienia" */
            'body' => $this->data['body'] ?? '',
            /* @example {} */
            'data' => $this->data,
            /* @example "2026-05-13T10:30:00.000000Z" */
            'read_at' => $this->read_at?->toISOString(),
            /* @example "2026-05-13T10:30:00.000000Z" */
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
