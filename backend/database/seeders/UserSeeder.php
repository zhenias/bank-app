<?php

namespace Database\Seeders;

use App\Models\Account\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            ['name' => 'Marek Nowak',           'email' => 'marek@example.com'],
            ['name' => 'Anna Kowalska',         'email' => 'anna@example.com'],
            ['name' => 'Jan Wiśniewski',        'email' => 'jan@example.com'],
            ['name' => 'Katarzyna Zielińska',   'email' => 'kasia@example.com'],
            ['name' => 'Piotr Wójcik',          'email' => 'piotr@example.com'],
            ['name' => 'Magdalena Szymańska',   'email' => 'magda@example.com'],
            ['name' => 'Tomasz Kamiński',       'email' => 'tomasz@example.com'],
            ['name' => 'Agnieszka Lewandowska', 'email' => 'aga@example.com'],
            ['name' => 'Krzysztof Dąbrowski',   'email' => 'krzysztof@example.com'],
            ['name' => 'Test User',             'email' => 'test@example.com'],
        ];

        $created  = 0;
        $existing = 0;

        foreach ($usersData as $data) {
            $user = User::query()->where('email', $data['email'])->first();

            if ($user) {
                $this->command->warn('⚠️  User already exists:  ' . $user->email);
                $this->command->line('   Password:             password');
                ++$existing;

                continue;
            }

            $user = User::factory()->create([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            $accountsCount = 5;
            Account::factory()->count($accountsCount)->withCards(rand(1, 2))->create([
                'user_id' => $user->id,
            ]);

            $this->command->info('✅ User created:  ' . $user->email);
            $this->command->line('   Password:     password');
            ++$created;
        }

        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->line('   Created:   ' . $created);
        $this->command->line('   Existing:  ' . $existing);
        $this->command->line('   Total:     ' . count($usersData));
    }
}
