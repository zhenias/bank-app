<?php

namespace App\Models\Account\Transaction;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\Account\Flik\FlikCode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['from_account_id', 'to_account_id', 'from_card_id', 'amount', 'description', 'type', 'status', 'reference'])]
class Transaction extends Model
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
            'amount' => 'decimal:2',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'from_card_id');
    }

    public function flikCode(): HasOne
    {
        return $this->hasOne(FlikCode::class, 'used_in_transaction_id');
    }
}
