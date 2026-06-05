<?php

namespace App\Http\Resources\Account\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'amount' => $this->amount,
            'signed_amount' => $this->signedAmountFor(auth()->user()->id),
            'from_account' => [
                'id' => $this->fromAccount?->id,
                'name' => $this->fromAccount?->name,
                'account_number' => $this->fromAccount?->account_number,
                'user' => $this->fromAccount?->user?->name,
            ],
            'to_account' => [
                'id' => $this->toAccount?->id,
                'name' => $this->toAccount?->name,
                'account_number' => $this->toAccount?->account_number,
                'user' => $this->toAccount?->user?->name,
            ],
            'from_card' => $this->when($this->from_card_id, [
                'id' => $this->card?->id,
                'last_four' => substr($this->card?->card_number ?? '', -4),
                'network' => $this->card?->network,
            ]),
            'description' => $this->description,
            'reference' => $this->reference,
            'failure_reason' => $this->failure_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

