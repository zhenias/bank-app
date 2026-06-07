<?php

namespace App\Http\Resources\Account\Flik;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlikCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /* @example "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d" */
            'id' => $this->id,
            /* @example "123e4567-e89b-12d3-a456-426614174000" */
            'card_id' => $this->card_id,
            /* @example "active"|"expired" */
            'status' => $this->status,
            /* @example "2500.00" */
            'amount' => number_format($this->amount / 100, 2, ',', ''),
            /* @example "2026-12-31T12:00:00.000Z" */
            'expires_at' => $this->expires_at?->toISOString(),
            /* @example true */
            'is_active' => $this->isActive(),
            /* @example false */
            'is_expired' => $this->isExpired(),
            /* @example "2024-06-01T12:00:00.000Z" */
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
