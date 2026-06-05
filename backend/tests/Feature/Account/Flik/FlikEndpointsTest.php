<?php

namespace Tests\Feature\Account\Flik;

use App\Enums\Flik\FlikCodeStatus;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FlikEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_code_and_pay_flow()
    {
        // Arrange
        $sender = User::factory()->create();
        $senderAccount = Account::factory()->create([ 'user_id' => $sender->id, 'currency' => 'PLN', 'balance' => 50000 ]);
        $card = Card::factory()->create([ 'account_id' => $senderAccount->id, 'card_number' => '4111111111111111' ]);

        $receiver = User::factory()->create();
        $receiverAccount = Account::factory()->create([ 'user_id' => $receiver->id, 'currency' => 'PLN', 'balance' => 0 ]);

        // Request code by sender
        Passport::actingAs($sender, ['flik-generate']);

        $response = $this->postJson("/api/flik/request-code/{$card->id}", [
            'card_id' => $card->id,
            'amount' => '10.00',
            'account_id' => $senderAccount->id,
        ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('code', $response->json());
        $this->assertArrayHasKey('amount', $response->json());

        $code = $response->json('code');

        // Pay with code (only code, no amount) by receiver
        Passport::actingAs($receiver, ['flik-pay']);

        $payResponse = $this->postJson('/api/flik/pay', ['code' => $code]);

        $payResponse->assertStatus(201);
        $this->assertArrayHasKey('transaction', $payResponse->json());

        $tx = $payResponse->json('transaction');

        $this->assertEquals('flik_payment', $tx['type']);
        $this->assertEquals('flik', $tx['payment_method']);
        $this->assertEquals('completed', $tx['status']);

        // Check flik code status in DB
        $this->assertDatabaseHas('flik_codes', [ 'code' => $code, 'status' => FlikCodeStatus::USED->value ]);
    }
}

