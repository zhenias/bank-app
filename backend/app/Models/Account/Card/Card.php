<?php

namespace App\Models\Account\Card;

use App\Models\Account\Account;
use App\Models\Account\Flik\FlikCode;
use App\Models\Account\Transaction\Transaction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'card_number', 'exp_month', 'exp_year', 'cvv', 'status', 'type', 'network'])]
class Card extends Model
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
            'exp_month' => 'integer',
            'exp_year'  => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_card_id');
    }

    public function flikCodes(): HasMany
    {
        return $this->hasMany(FlikCode::class);
    }
}
