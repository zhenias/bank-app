<?php

namespace Database\Factories\Account;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\User;
use App\Services\Generator\AccountNumberGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    use AccountNumberGeneratorService;

    public function definition(): array
    {
        $currency = $this->faker->randomElement(['PLN', 'USD', 'EURO']);

        return [
            'user_id'        => User::factory(),
            'name'           => match($currency) {
                'PLN'  => $this->faker->randomElement(['Konto osobiste', 'Konto oszczędnościowe', 'Konto premium', 'SuperKonto']),
                'USD'  => 'Konto walutowe USD',
                'EURO' => 'Konto walutowe EUR',
            },
            'account_number' => $this->generate(),
            'balance'        => $this->faker->numberBetween(100, 1500000),
            'currency'       => $currency,
            'type'           => $this->faker->randomElement(['current', 'savings']),
        ];
    }

    public function withCards(int $count = 1): static
    {
        return $this->afterCreating(function (Account $account) use ($count) {
            Card::factory()->count($count)->create([
                'account_id' => $account->id,
            ]);
        });
    }
}
