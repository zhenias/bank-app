<?php

namespace App\Models;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\Account\Flik\FlikCode;
use App\Models\Account\Transaction\Transaction;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['id', 'name', 'email', 'date_of_birth', 'guardian_id', 'guardian_approved_at', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id'                   => 'string',
            'name'                 => 'string',
            'email'                => 'string',
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'date_of_birth'        => 'datetime:Y-m-d',
            'guardian_approved_at' => 'datetime',
        ];
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function wards(): HasMany
    {
        return $this->hasMany(User::class, 'guardian_id');
    }

    public function isAdult(): bool
    {
        if (! $this->date_of_birth) {
            return false;
        }

        return $this->date_of_birth->age >= 18;
    }

    public function requiresGuardian(): bool
    {
        return ! $this->isAdult();
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, Account::class);
    }

    public function transactions()
    {
        return Transaction::whereHas('fromAccount', function ($q) {
            $q->where('user_id', $this->id);
        })->orWhereHas('toAccount', function ($q) {
            $q->where('user_id', $this->id);
        });
    }

    public function flikCodes(): HasManyThrough
    {
        return $this->hasManyThrough(FlikCode::class, Card::class, 'account_id', 'card_id', 'id', 'id');
    }

    public function sentTransactions()
    {
        return $this->hasManyThrough(Transaction::class, Account::class, 'user_id', 'from_account_id');
    }

    public function receivedTransactions()
    {
        return $this->hasManyThrough(Transaction::class, Account::class, 'user_id', 'to_account_id');
    }

    /**
     * @property array{id: string, name: string, email: string, guardian_approved_at: string|null}|null $guardian
     * @property bool                                                                                   $is_adult
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $array['is_adult'] = $this->isAdult();

        if (isset($array['guardian_id']) && $array['guardian_id']) {
            $guardian                                  = $this->guardian()->first(['id', 'name', 'email']);
            $array['guardian']                         = $guardian?->toArray();
            $array['guardian']['guardian_approved_at'] = $array['guardian_approved_at'];

            unset($array['guardian_id'], $array['guardian_approved_at']);

            return $array;
        }

        unset($array['guardian_id']);

        return $array;
    }
}
