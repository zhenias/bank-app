<?php

namespace App\Models\Account\Flik;

use App\Models\Account\Card\Card;
use App\Models\Account\Transaction\Transaction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['card_id', 'code', 'expires_at', 'status', 'used_in_transaction_id'])]
class FlikCode extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'used_in_transaction_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return 'active' === $this->status && ! $this->isExpired();
    }
}
