<?php

namespace App\Models\Passport;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient
{
    public function skipsAuthorization(Authenticatable $user, array $scopes = []): bool
    {
        return $this->firstParty();
    }
}
