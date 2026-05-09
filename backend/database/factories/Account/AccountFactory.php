<?php

namespace Database\Factories\Account;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'account_number' => $this->faker->unique()->iban(),
            'balance'        => $this->faker->randomFloat(2, 0, 10000),
            'currency'       => 'PLN',
            'type'           => 'current',
        ];
    }
}
