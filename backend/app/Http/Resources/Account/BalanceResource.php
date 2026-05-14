<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example PLN */
            'currency' => $this->currency,
            /** @example 2500.00 */
            'balance' => number_format($this->total_balance / 100, 2, '.', ''),
        ];
    }
}
