<?php

namespace Database\Factories\Account\Transaction;

use App\Models\Account\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromAccountUser = User::factory()->create();
        $toAccountUser   = User::factory()->create();
        $fromCardUser    = $fromAccountUser->cards()->factory()->create();

        return [
            'from_account_id' => $fromAccountUser,
            'to_account_id'   => $toAccountUser,
            'from_card_id'    => $fromCardUser,
            'amount'          => $this->faker->randomFloat(2, 1, 1000),
            'description'     => $this->faker->sentence(),
            'type'            => $this->faker->randomElement(['transfer', 'payment', 'withdrawal']),
            'status'          => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'reference'       => $this->faker->uuid(),
        ];
    }
}
