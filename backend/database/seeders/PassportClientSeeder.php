<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientId = config('passport.client_id', 'bank-client-id');
        $clientSecret = config('passport.client_secret', 'bank-client-secret');
        $nameApp = config('app.name', 'Bank Online System');
        $redirectUri = config('passport.redirect_uri', '');
        $grantTypes = ['authorization_code', 'refresh_token', 'password', 'client_credentials'];

        if (!$users = User::query()->where('email', 'test@example.com')->get()) {
            $users = User::factory()->create([
                'password' => Hash::make('password'),
                'email'    => 'test@example.com'
            ]);
        }

        foreach ($users as $user) {
            $this->command->info('   User created: ' . $user->email);
            $this->command->info('   Password:     password');
        }

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
                'secret' => Hash::make($clientSecret),
                'redirect_uris' => json_encode([$redirectUri]),
                'updated_at' => now(),
                'grant_types' => json_encode($grantTypes),
            ]);

            $this->command->info('✅ Passport client updating!');
            $this->command->info('   Client ID: ' . $clientId);
            $this->command->info('   Client Secret: ' . $clientSecret);
            $this->command->info('   Redirect URI: ' . $redirectUri);

            return;
        }

        DB::table('oauth_clients')->insert([
            'id'                     => $clientId,
            'name'                   => $nameApp,
            'secret'                 => Hash::make($clientSecret),
            'provider'               => 'users',
            'redirect_uris'          => json_encode([$redirectUri]),
            'grant_types'            => json_encode($grantTypes),
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
