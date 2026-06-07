<?php

namespace Tests\Feature\Account\Flik;

use App\Enums\Flik\FlikCodeStatus;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\Account\Flik\FlikCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FlikEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function testRequestCodeAndPayFlow(): void
    {
        // Arrange
        $sender = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $senderAccount = Account::factory()->create([
            'user_id' => $sender->id,
            'currency' => 'PLN',
            'balance' => 50000,
            'status' => 'open',
        ]);
        $card = Card::factory()->create([
            'account_id' => $senderAccount->id,
            'status' => 'active'
        ]);

        $receiver = User::factory()->create([
            'date_of_birth' => now()->subYears(25),
        ]);
        $receiverAccount = Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'PLN',
            'balance' => 0,
            'status' => 'open'
        ]);

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
        $this->assertEquals('pending', $tx['status']);

        // Check flik code status in DB
        $this->assertDatabaseHas('flik_codes', [ 'code' => $code, 'status' => FlikCodeStatus::USED->value ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $tx['id'],
            'status' => 'completed',
        ]);
    }

    public function testCheckStatusResponse(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        $card = Card::factory()->create([
            'account_id' => Account::factory()->create([
                'user_id' => $user->id,
                'currency' => 'PLN',
                'balance' => 50000,
                'status' => 'open',
            ]),
            'status' => 'active'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::ACTIVE->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($user, ['flik-status']);

        $response = $this->postJson("/api/flik/status/{$flikCode->code}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('status', $response->json());
        $this->assertEquals('active', $response->json('status'));

        $flikCode->update([
            'status' => FlikCodeStatus::EXPIRED->value,
        ]);

        $responseExpired = $this->postJson("/api/flik/status/{$flikCode->code}");
        $responseExpired->assertStatus(200);
        $this->assertArrayHasKey('status', $responseExpired->json());
        $this->assertEquals('expired', $responseExpired->json('status'));

        $flikCode->update([
            'status' => FlikCodeStatus::USED->value,
        ]);

        $responseUsed = $this->postJson("/api/flik/status/{$flikCode->code}");
        $responseUsed->assertStatus(200);
        $this->assertArrayHasKey('status', $responseUsed->json());
        $this->assertEquals('used', $responseUsed->json('status'));
    }
}

