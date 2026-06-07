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
        return [
            'card_id'    => Card::factory(),
            'code'       => $this->faker->unique()->regexify('[0-9]{6}'),
            'expires_at' => now()->addMinutes(2),
            'status'     => $this->faker->randomElement(['active', 'used', 'expired']),
        ];
    }
}
