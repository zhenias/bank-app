<?php

namespace Database\Factories\Account\Flik;

use App\Models\Account\Card\Card;
use App\Models\Account\Flik\FlikCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlikCode>
 */
class FlikCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $card = Card::factory()->create();

        return [
            'card_id'    => $card,
            'code'       => $this->faker->unique()->regexify('[0-9]{6}'),
            'expires_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'status'     => $this->faker->randomElement(['active', 'used', 'expired']),
        ];
    }
}
