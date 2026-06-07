<?php

namespace Tests\Feature\Account\Transaction;

use App\Enums\Transaction\TransactionStatus;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\Account\Transaction\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanCreateTransferAndBalancesUpdate(): void
    {
        $user        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $fromAccount = Account::factory()->create([
            'user_id'        => $user->id,
            'currency'       => 'PLN',
            'balance'        => 100000,
            'account_number' => '11111111111111111111111111',
        ]);

        $recipient = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $toAccount = Account::factory()->create([
            'user_id'        => $recipient->id,
            'currency'       => 'PLN',
            'balance'        => 0,
            'account_number' => '22222222222222222222222222',
        ]);

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', [
            'to_account_number' => $toAccount->account_number,
            'amount'            => '100.50',
            'description'       => 'Test transfer',
            'reference'         => 'INV-100',
        ]);

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertNotEmpty($data['id']);
        $this->assertEquals('transfer', $data['type']);
        $this->assertEquals('completed', $data['status']);

        $fromAccount->refresh();
        $toAccount->refresh();
        $this->assertEquals(100000 - 10050, $fromAccount->balance);
        $this->assertEquals(10050, $toAccount->balance);
    }

    public function testTransferToOwnAccountIsRejected(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'        => $user->id,
            'currency'       => 'PLN',
            'balance'        => 100000,
            'account_number' => '11111111111111111111111111',
        ]);

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', [
            'to_account_number' => $account->account_number,
            'amount'            => '10.00',
        ]);

        $response->assertStatus(422);
    }

    public function testTransferWithInsufficientFundsIsRejected(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'        => $user->id,
            'currency'       => 'PLN',
            'balance'        => 500,
            'account_number' => '11111111111111111111111111',
        ]);

        $recipient = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $toAccount = Account::factory()->create([
            'user_id'        => $recipient->id,
            'currency'       => 'PLN',
            'account_number' => '22222222222222222222222222',
        ]);

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', [
            'to_account_number' => $toAccount->account_number,
            'amount'            => '10.00',
        ]);

        $response->assertStatus(422);
    }

    public function testTransferToNonexistentAccountIsRejected(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'        => $user->id,
            'currency'       => 'PLN',
            'balance'        => 100000,
            'account_number' => '11111111111111111111111111',
        ]);

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', [
            'to_account_number' => '99999999999999999999999999',
            'amount'            => '10.00',
        ]);

        $response->assertStatus(422);
    }

    public function testTransferBelowMinimumAmountIsRejected(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'        => $user->id,
            'currency'       => 'PLN',
            'balance'        => 100000,
            'account_number' => '11111111111111111111111111',
        ]);

        $recipient = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $toAccount = Account::factory()->create([
            'user_id'        => $recipient->id,
            'currency'       => 'PLN',
            'account_number' => '22222222222222222222222222',
        ]);

        Passport::actingAs($user, ['transactions-create']);

        $response = $this->postJson('/api/transactions', [
            'to_account_number' => $toAccount->account_number,
            'amount'            => '0.00',
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanListTheirTransactions(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'account_number' => Str::random(26),
        ]);

        Transaction::create([
            'from_account_id' => $account->id,
            'to_account_id'   => $otherAccount->id,
            'amount'          => 1000,
            'type'            => 'transfer',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Transaction::create([
            'from_account_id' => $otherAccount->id,
            'to_account_id'   => $account->id,
            'amount'          => 2000,
            'type'            => 'transfer',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function testTransactionListIsPaginated(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'account_number' => Str::random(26),
        ]);

        for ($i = 0; $i < 25; ++$i) {
            Transaction::create([
                'from_account_id' => $account->id,
                'to_account_id'   => $otherAccount->id,
                'amount'          => 100,
                'type'            => 'transfer',
                'status'          => TransactionStatus::COMPLETED->value,
            ]);
        }

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson('/api/transactions?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function testUserCanViewOwnTransaction(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'account_number' => Str::random(26),
        ]);

        $transaction = Transaction::create([
            'from_account_id' => $account->id,
            'to_account_id'   => $otherAccount->id,
            'amount'          => 1000,
            'type'            => 'transfer',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200);
        $this->assertEquals($transaction->id, $response->json('data.id'));
    }

    public function testUserCannotViewOthersTransaction(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'currency'       => 'PLN',
            'balance'        => 50000,
            'account_number' => Str::random(26),
        ]);

        $third        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $thirdAccount = Account::factory()->create([
            'user_id'        => $third->id,
            'currency'       => 'PLN',
            'account_number' => Str::random(26),
        ]);

        $transaction = Transaction::create([
            'from_account_id' => $otherAccount->id,
            'to_account_id'   => $thirdAccount->id,
            'amount'          => 1000,
            'type'            => 'transfer',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    public function testUserCanListAccountTransactions(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'account_number' => Str::random(26),
        ]);

        Transaction::create([
            'from_account_id' => $account->id,
            'to_account_id'   => $otherAccount->id,
            'amount'          => 1000,
            'type'            => 'transfer',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/accounts/{$account->id}/transactions");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function testUserCannotListOthersAccountTransactions(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'  => $other->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/accounts/{$otherAccount->id}/transactions");

        $response->assertStatus(403);
    }

    public function testUserCanListCardTransactions(): void
    {
        $user    = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $account = Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);
        $card = Card::factory()->create([
            'account_id' => $account->id,
            'status'     => 'active',
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'        => $other->id,
            'account_number' => Str::random(26),
        ]);

        Transaction::create([
            'from_account_id' => $account->id,
            'to_account_id'   => $otherAccount->id,
            'from_card_id'    => $card->id,
            'amount'          => 1000,
            'type'            => 'flik_payment',
            'status'          => TransactionStatus::COMPLETED->value,
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/cards/{$card->id}/transactions");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function testUserCannotListOthersCardTransactions(): void
    {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        Account::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);

        $other        = User::factory()->create(['date_of_birth' => now()->subYears(30)]);
        $otherAccount = Account::factory()->create([
            'user_id'  => $other->id,
            'currency' => 'PLN',
            'balance'  => 50000,
        ]);
        $otherCard = Card::factory()->create([
            'account_id' => $otherAccount->id,
            'status'     => 'active',
        ]);

        Passport::actingAs($user, ['transactions-view']);

        $response = $this->getJson("/api/cards/{$otherCard->id}/transactions");

        $response->assertStatus(403);
    }
}
