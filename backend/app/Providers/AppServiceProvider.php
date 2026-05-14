<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\SecuritySchemes\OAuthFlow;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Passport::authorizationView('vendor.passport.authorize');

        Scramble::registerApi('docs');

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT'),
                );

                $openApi->secure(
                    SecurityScheme::oauth2()
                        ->flow('password', function (OAuthFlow $flow) {
                            $flow->tokenUrl(config('app.url') . '/oauth/token');
                        }),
                );
            });

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        Passport::enablePasswordGrant();

        Passport::tokensCan([
            // Podstawowe informacje o użytkowniku
            'user-profile'        => 'Odczyt podstawowych danych profilu',
            'user-profile-manage' => 'Edycja podstawowych danych profilu, oraz usuwanie i edycja konta.',

            // Informacje finansowe
            'balance-view'    => 'Odczyt bieżącego salda konta',
            'balance-history' => 'Odczyt historii salda',

            // Karty
            'cards-view'         => 'Odczyt listy kart',
            'cards-details'      => 'Odczyt szczegółów karty (ostatnie 4 cyfry, typ, status)',
            'cards-transactions' => 'Odczyt transakcji kartą',
            'cards-manage'       => 'Zarządzanie kartami (blokada, odblokowanie, limity)',

            // Transakcje
            'transactions-view'      => 'Odczyt historii transakcji',
            'transactions-details'   => 'Odczyt szczegółów transakcji',
            'transactions-transfer'  => 'Wykonywanie przelewów',
            'transactions-scheduled' => 'Odczyt zaplanowanych przelewów',
            'transactions-schedule'  => 'Planowanie przelewów',

            // Konta
            'accounts-view'      => 'Odczyt listy kont bankowych',
            'accounts-details'   => 'Odczyt szczegółów konta (numer, typ, waluta)',
            'accounts-statement' => 'Pobieranie wyciągów bankowych',
            'accounts-manage'    => 'Zarządzanie kontami (tworzenia, edycja, usuwanie)',

            // Blik / Płatności mobilne
            'blik-generate' => 'Generowanie kodu BLIK',
            'blik-confirm'  => 'Potwierdzanie transakcji BLIK',

            // Przelewy
            'transfer-internal' => 'Przelew wewnętrzny (na konto w tym samym banku)',
            'transfer-external' => 'Przelew zewnętrzny (na konto w innym banku)',
            'transfer-express'  => 'Przelew ekspresowy',
            'transfer-foreign'  => 'Przelew zagraniczny',

            // Ustawienia i bezpieczeństwo
            'settings-view'        => 'Odczyt ustawień konta',
            'settings-update'      => 'Aktualizacja ustawień konta',
            'notifications-view'   => 'Odczyt powiadomień',
            'notifications-manage' => 'Zarządzanie powiadomieniami',

            // Limity i uprawnienia
            'limits-view'   => 'Odczyt limitów transakcyjnych',
            'limits-update' => 'Modyfikacja limitów transakcyjnych',

            // Beneficjenci (zaufani odbiorcy)
            'beneficiaries-view'   => 'Odczyt listy zaufanych odbiorców',
            'beneficiaries-manage' => 'Zarządzanie zaufanymi odbiorcami',

            // Kredyty / Pożyczki
            'loans-view'     => 'Odczyt listy kredytów',
            'loans-details'  => 'Odczyt szczegółów kredytu',
            'loans-payments' => 'Odczyt harmonogramu spłat',

            // Produkty inwestycyjne
            'investments-view'    => 'Odczyt produktów inwestycyjnych',
            'investments-details' => 'Odczyt szczegółów inwestycji',
        ]);

        Passport::defaultScopes([
            'user-profile',
            'user-profile-manage',

            'balance-view',
            'balance-history',
        ]);
    }
}
