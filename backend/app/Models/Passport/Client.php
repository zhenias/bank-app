<?php

namespace App\Models\Passport;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * Określa, czy klient może pominąć ekran autoryzacji.
     * Dla własnych aplikacji (frontend) zwracamy 'true'.
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes = []): bool
    {
        // Dla klientów first-party pomijamy ekran autoryzacji
        return $this->firstParty();
    }
}
