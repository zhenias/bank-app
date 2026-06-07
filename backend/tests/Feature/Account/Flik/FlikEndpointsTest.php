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

        Passport::actingAs($receiver, ['flik-pay']);

        $payResponse = $this->postJson('/api/flik/pay', ['code' => $code]);

        $payResponse->assertStatus(201);
        $this->assertArrayHasKey('transaction', $payResponse->json());

        $tx = $payResponse->json('transaction');

        $this->assertEquals('flik_payment', $tx['type']);
        $this->assertEquals('flik', $tx['payment_method']);
        $this->assertEquals('pending', $tx['status']);

        $this->assertDatabaseHas('flik_codes', [ 'code' => $code, 'status' => FlikCodeStatus::USED->value ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $tx['id'],
            'from_account_id' => $senderAccount->id,
            'to_account_id' => $receiverAccount->id,
            'amount' => 1000,
            'type' => 'flik_payment',
            'payment_method' => 'flik',
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

    public function testCannotRequestCodeForOtherUserCard(): void
    {
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

        $otherUser = User::factory()->create([
            'date_of_birth' => now()->subYears(25),
        ]);

        Passport::actingAs($otherUser, ['flik-generate']);

        $response = $this->postJson("/api/flik/request-code/{$card->id}", [
            'card_id' => $card->id,
            'amount' => '10.00',
            'account_id' => $senderAccount->id,
        ]);

        $response->assertStatus(403);
    }

    public function testCannotPayWithExpiredCode(): void
    {
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
        Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'PLN',
            'balance' => 0,
            'status' => 'open'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::EXPIRED->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($receiver, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testCannotPayWithUsedCode(): void
    {
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
        Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'PLN',
            'balance' => 0,
            'status' => 'open'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::USED->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($receiver, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testCannotPayToSelf(): void
    {
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

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::ACTIVE->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($sender, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testCannotPayWhenPayerHasNoPlnAccount(): void
    {
        $sender = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $senderAccount = Account::factory()->create([
            'user_id' => $sender->id,
            'currency' => 'EUR',
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
        Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'PLN',
            'balance' => 0,
            'status' => 'open'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::ACTIVE->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($receiver, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testCannotPayWhenReceiverHasNoPlnAccount(): void
    {
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
        Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'EUR',
            'balance' => 0,
            'status' => 'open'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::ACTIVE->value,
            'card_id' => $card->id,
        ]);

        Passport::actingAs($receiver, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testCannotPayWithInsufficientFunds(): void
    {
        $sender = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $senderAccount = Account::factory()->create([
            'user_id' => $sender->id,
            'currency' => 'PLN',
            'balance' => 500,
            'status' => 'open',
        ]);
        $card = Card::factory()->create([
            'account_id' => $senderAccount->id,
            'status' => 'active'
        ]);

        $receiver = User::factory()->create([
            'date_of_birth' => now()->subYears(25),
        ]);
        Account::factory()->create([
            'user_id' => $receiver->id,
            'currency' => 'PLN',
            'balance' => 0,
            'status' => 'open'
        ]);

        $flikCode = FlikCode::factory()->create([
            'status' => FlikCodeStatus::ACTIVE->value,
            'card_id' => $card->id,
            'amount' => 10000,
        ]);

        Passport::actingAs($receiver, ['flik-pay']);

        $response = $this->postJson('/api/flik/pay', ['code' => $flikCode->code]);

        $response->assertStatus(422);
    }

    public function testStatusReturns404ForNonExistentCode(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['flik-status']);

        $response = $this->postJson('/api/flik/status/000000');

        $response->assertStatus(422);
    }

    public function testRequiresAuthentication(): void
    {
        $this->postJson('/api/flik/request-code/550e8400-e29b-41d4-a716-446655440000')
            ->assertStatus(401);

        $this->postJson('/api/flik/pay', ['code' => '123456'])
            ->assertStatus(401);

        $this->postJson('/api/flik/status/123456')
            ->assertStatus(401);
    }

    public function testRequiresScopeForRequestCode(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'currency' => 'PLN',
            'balance' => 50000,
            'status' => 'open',
        ]);
        $card = Card::factory()->create([
            'account_id' => $account->id,
            'status' => 'active'
        ]);

        Passport::actingAs($user, []);

        $response = $this->postJson("/api/flik/request-code/{$card->id}", [
            'card_id' => $card->id,
            'amount' => '10.00',
            'account_id' => $account->id,
        ]);

        $response->assertStatus(403);
    }

    public function testRequiresScopeForPay(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, []);

        $response = $this->postJson('/api/flik/pay', ['code' => '123456']);

        $response->assertStatus(403);
    }

    public function testRequiresScopeForStatus(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, []);

        $response = $this->postJson('/api/flik/status/123456');

        $response->assertStatus(403);
    }
}
