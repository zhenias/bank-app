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

        Passport::enablePasswordGrant();

        Passport::tokensCan([
            // Podstawowe informacje o użytkowniku
            'user-profile' => 'Odczyt podstawowych danych profilu',

            // Informacje finansowe
            'balance-view' => 'Odczyt bieżącego salda konta',
            'balance-history' => 'Odczyt historii salda',

            // Karty
            'cards-view' => 'Odczyt listy kart',
            'cards-details' => 'Odczyt szczegółów karty (ostatnie 4 cyfry, typ, status)',
            'cards-transactions' => 'Odczyt transakcji kartą',
            'cards-manage' => 'Zarządzanie kartami (blokada, odblokowanie, limity)',

            // Transakcje
            'transactions-view' => 'Odczyt historii transakcji',
            'transactions-details' => 'Odczyt szczegółów transakcji',
            'transactions-transfer' => 'Wykonywanie przelewów',
            'transactions-scheduled' => 'Odczyt zaplanowanych przelewów',
            'transactions-schedule' => 'Planowanie przelewów',

            // Konta
            'accounts-view' => 'Odczyt listy kont bankowych',
            'accounts-details' => 'Odczyt szczegółów konta (numer, typ, waluta)',
            'accounts-statement' => 'Pobieranie wyciągów bankowych',

            // Blik / Płatności mobilne
            'blik-generate' => 'Generowanie kodu BLIK',
            'blik-confirm' => 'Potwierdzanie transakcji BLIK',

            // Przelewy
            'transfer-internal' => 'Przelew wewnętrzny (na konto w tym samym banku)',
            'transfer-external' => 'Przelew zewnętrzny (na konto w innym banku)',
            'transfer-express' => 'Przelew ekspresowy',
            'transfer-foreign' => 'Przelew zagraniczny',

            // Ustawienia i bezpieczeństwo
            'settings-view' => 'Odczyt ustawień konta',
            'settings-update' => 'Aktualizacja ustawień konta',
            'notifications-view' => 'Odczyt powiadomień',
            'notifications-manage' => 'Zarządzanie powiadomieniami',

            // Limity i uprawnienia
            'limits-view' => 'Odczyt limitów transakcyjnych',
            'limits-update' => 'Modyfikacja limitów transakcyjnych',

            // Beneficjenci (zaufani odbiorcy)
            'beneficiaries-view' => 'Odczyt listy zaufanych odbiorców',
            'beneficiaries-manage' => 'Zarządzanie zaufanymi odbiorcami',

            // Kredyty / Pożyczki
            'loans-view' => 'Odczyt listy kredytów',
            'loans-details' => 'Odczyt szczegółów kredytu',
            'loans-payments' => 'Odczyt harmonogramu spłat',

            // Produkty inwestycyjne
            'investments-view' => 'Odczyt produktów inwestycyjnych',
            'investments-details' => 'Odczyt szczegółów inwestycji',
        ]);

        Passport::defaultScopes([
            'user-profile',

            'balance-view',
            'balance-history',
        ]);
    }
}
