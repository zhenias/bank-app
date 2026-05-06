<?php

namespace App\Providers;

use App\Http\Responses\AuthorizationViewResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Contracts\AuthorizationViewResponse as AuthorizationViewResponseContract;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthorizationViewResponseContract::class, function () {
            return new AuthorizationViewResponse('vendor.passport.authorize');
        });
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        Passport::tokensCan([
            'user-profile' => 'Access profile information',
            'user-balance' => 'Access account balance',
        ]);
    }
}
