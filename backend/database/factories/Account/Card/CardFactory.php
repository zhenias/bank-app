<?php

namespace Database\Factories\Account\Card;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = Account::factory()->create();

        return [
            'account_id'  => $account,
            'card_number' => $this->faker->creditCardNumber(),
            'cvv'         => $this->faker->randomNumber(3),
            'exp_month'   => $this->faker->date('m'),
            'exp_year'    => $this->faker->date('Y'),
            'status'      => $this->faker->randomElement(['active', 'inactive', 'blocked']),
            'type'        => $this->faker->randomElement(['debit', 'credit', 'prepaid', 'virtual']),
            'network'     => $this->faker->randomElement(['Visa', 'MasterCard', 'American Express', 'Discover']),
        ];
    }
}
