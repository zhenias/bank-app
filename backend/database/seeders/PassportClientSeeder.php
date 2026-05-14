<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PassportClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientId     = config('passport.client_id', 'bank-client-id');
        $clientSecret = config('passport.client_secret', 'bank-client-secret');
        $nameApp      = config('app.name', 'Bank Online System');
        $redirectUris  = [
            config('passport.redirect_uri', ''),
            'http://localhost:8000/auth/callback',
            'http://localhost:5174/auth/callback',
            'http://127.0.0.1:8000/auth/callback'
        ];
        $grantTypes   = ['authorization_code', 'refresh_token', 'password'];

        $exists = DB::table('oauth_clients')
        ->where('id', $clientId)
        ->orWhere('name', $nameApp)
        ->exists();

        if ($exists) {
            $this->command->info('⚠️ Passport client already exists. Updating...');

            DB::table('oauth_clients')
            ->where('id', $clientId)
            ->orWhere('name', $nameApp)
            ->update([
                'secret'        => Hash::make($clientSecret),
                'redirect_uris' => json_encode($redirectUris),
                'updated_at'    => now(),
                'grant_types'   => json_encode($grantTypes),
            ]);

            $this->command->info('✅ Passport client updating!');
            $this->command->info('   Client ID:     ' . $clientId);
            $this->command->info('   Client Secret: ' . $clientSecret);
            $this->command->info('   Redirect URI:  ' . implode(', ', $redirectUris));
            $this->command->info('   Grant types:   ' . implode(', ', $grantTypes));

            return;
        }

        DB::table('oauth_clients')->insert([
            'id'            => $clientId,
            'name'          => $nameApp,
            'secret'        => Hash::make($clientSecret),
            'provider'      => 'users',
            'redirect_uris' => json_encode($redirectUris),
            'grant_types'   => json_encode($grantTypes),
            'revoked'       => 0,
        ]);

        $this->command->info('✅ Passport client created!');
        $this->command->info('   Client ID:     ' . $clientId);
        $this->command->info('   Client Secret: ' . $clientSecret);
        $this->command->info('   Redirect URI:  ' . implode(', ', $redirectUris));
        $this->command->info('   Grant types:   ' . implode(', ', $grantTypes));
    }
}
