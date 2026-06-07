<?php

namespace Tests\Feature\Account\Transaction;

use App\Enums\Transaction\TransactionStatus;
use App\Models\Account\Account;
use App\Models\Account\Transaction\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transfer_and_balances_update()
    {
        // Arrange: user with one account (from) and recipient account
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $fromAccount = Account::factory()->create([
            'user_id' => $user->id,
            'currency' => 'PLN',
            'balance' => 100000, // 1000.00 PLN in cents
            'account_number' => '11111111111111111111111111',
        ]);

        $recipientUser = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $toAccount = Account::factory()->create([
            'user_id' => $recipientUser->id,
            'currency' => 'PLN',
            'balance' => 0,
            'account_number' => '22222222222222222222222222',
        ]);

        // Act: perform transfer of 100.50 PLN
        $payload = [
            'to_account_number' => $toAccount->account_number,
            'amount' => '100.50',
            'description' => 'Test transfer',
            'reference' => 'INV-100',
        ];

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', $payload);

        // Assert: response 201 and transaction created
        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertNotEmpty($data['id']);
        $this->assertEquals('transfer', $data['type']);
        $this->assertEquals('completed', $data['status']);

        // Balances updated: fromAccount -10050, toAccount +10050
        $fromAccount->refresh();
        $toAccount->refresh();

        $this->assertEquals(100000 - 10050, $fromAccount->balance);
        $this->assertEquals(10050, $toAccount->balance);
    }

    public function test_user_can_list_their_transactions()
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'currency' => 'PLN',
            'balance' => 50000,
        ]);

        // Create some transactions where user is the from_account or to_account
        $other = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $otherAccount = Account::factory()->create(['user_id' => $other->id, 'account_number' => Str::random(26)]);

        // create via model directly
        Transaction::create([
            'from_account_id' => $account->id,
            'to_account_id' => $otherAccount->id,
            'amount' => 1000,
            'type' => 'transfer',
            'status' => TransactionStatus::COMPLETED->value,
        ]);

        Transaction::create([
            'from_account_id' => $otherAccount->id,
            'to_account_id' => $account->id,
            'amount' => 2000,
            'type' => 'transfer',
            'status' => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }
}

