<?php

namespace App\Http\Resources\Account\Flik;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlikCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_id' => $this->card_id,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

