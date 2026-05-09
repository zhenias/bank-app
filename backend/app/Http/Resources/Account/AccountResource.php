<?php

namespace App\Http\Resources\Account;

use App\Http\Resources\Account\Card\CardResource;
use App\Models\Account\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Account $resource
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /* @example "123e4567-e89b-12d3-a456-426614174000" */
            'id' => $this->id,
            /* @example "Konto oszczędnościowe" */
            'name' => $this->name,
            /* @example "98123456780000000012345678" */
            'account_number' => $this->account_number,
            /* @example "2500.00" */
            'balance' => number_format($this->balance / 100, 2, '.', ''),
            /* @example "PLN" */
            'currency' => $this->currency,
            /* @example "savings" */
            'type'  => $this->type,
            'cards' => CardResource::collection($this->whenLoaded('cards')),
            /* @example "2024-06-01T12:00:00.000Z" */
            'created_at' => $this->created_at?->toISOString(),
            /* @example "2024-06-01T12:00:00.000Z" */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
