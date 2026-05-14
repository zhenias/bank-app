<?php

namespace App\Http\Resources\Account\Card;

use App\Models\Account\Card\Card;
use App\Services\Generator\CardNumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Card $resource
 */
class CardResource extends JsonResource
{
    use CardNumberGeneratorService;

    public function toArray(Request $request): array
    {
        return [
            /* @example "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d" */
            'id' => $this->id,
            /* @example "**** **** **** 1234" */
            'card_number' => $this->when(
                $this->card_number,
                $this->maskCardNumber($this->card_number),
            ),
            /* @example "1234" */
            'card_last_four' => $this->when(
                $this->card_number,
                substr($this->card_number, -4),
            ),
            /* @example 123 */
            'cvv' => $this->cvv,
            /* @example 12 */
            'exp_month' => $this->exp_month,
            /* @example 2024 */
            'exp_year' => $this->exp_year,
            /* @example "Visa" */
            'network' => $this->network,
            /* @example "credit" */
            'type' => $this->type,
            /* @example "active" */
            'status' => $this->status,
            /* @example "2024-06-01T12:00:00.000Z" */
            'created_at' => $this->created_at?->toISOString(),
            /* @example "2024-06-01T12:00:00.000Z" */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
