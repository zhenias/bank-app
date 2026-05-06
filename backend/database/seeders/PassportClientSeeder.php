<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientId = config('passport.client_id', 'bank-client-id');
        $clientSecret = config('passport.client_secret', 'bank-client-secret');
        $nameApp = config('app.name', 'Bank Online System');
        $redirectUri = config('passport.redirect_uri', 'http://localhost:8000/auth/callback');

        // Sprawdź, czy już istnieje
        $exists = DB::table('oauth_clients')
            ->where('id', $clientId)
            ->orWhere('name', $nameApp)
            ->exists();

        if ($exists) {
            $this->command->info('⚠️  Passport client already exists. Updating...');

                DB::table('oauth_clients')
                ->where('id', $clientId)
                ->orWhere('name', $nameApp)
                ->update([
                    'secret' => $clientSecret,
                    'redirect_uris' => json_encode([$redirectUri]),
                    'updated_at' => now(),
                ]);

            $this->command->info('✅ Passport client updating!');
            $this->command->info('   Client ID: ' . $clientId);
            $this->command->info('   Client Secret: ' . $clientSecret);
            $this->command->info('   Redirect URI: ' . $redirectUri);

            return;
        }

        // Utwórz klienta ręcznie
        DB::table('oauth_clients')->insert([
            'id'                     => $clientId,
            'name'                   => $nameApp,
            'secret'                 => $clientSecret,
            'provider'               => 'users',
            'redirect_uris'          => json_encode([$redirectUri]),
            'grant_types'           => json_encode(['authorization_code', 'refresh_token']),
            'revoked'                => 0,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $this->command->info('✅ Passport client created!');
        $this->command->info('   Client ID:     ' . $clientId);
        $this->command->info('   Client Secret: ' . $clientSecret);
        $this->command->info('   Redirect URI:  ' . $redirectUri);
    }
}
