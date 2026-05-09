<?php

namespace App\Models\Account;

use App\Models\Account\Card\Card;
use App\Models\Account\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'account_number', 'balance', 'currency', 'type'])]
// #[Hidden(['account_number'])]
class Account extends Model
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
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    public function transactions()
    {
        return Transaction::where('from_account_id', $this->id)
            ->orWhere('to_account_id', $this->id);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance / 100, 2, '.', '') . ' ' . $this->currency;
    }
}
