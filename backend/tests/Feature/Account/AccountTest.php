<?php

namespace Tests\Feature\Account;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        Account::query()->delete();
        Card::query()->delete();
    }

    public function testListsAllUserAccounts(): void
    {
        Account::factory()->count(3)->create(['user_id' => $this->user->id]);

        User::factory()
        ->withAccount(2)
        ->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($this->user, ['accounts-view']);

        $response = $this->getJson('/api/accounts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'account_number',
                        'balance',
                        'currency',
                        'type',
                        'cards',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function testCreatesNewAccount(): void
    {
        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->postJson('/api/accounts', [
            'name'      => 'Konto oszczędnościowe',
            'currency'  => 'PLN',
            'type'      => 'savings',
            'with_card' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Konto oszczędnościowe')
            ->assertJsonPath('data.currency', 'PLN')
            ->assertJsonPath('data.type', 'savings')
            ->assertJsonPath('data.balance', '0.00')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'account_number',
                    'balance',
                    'currency',
                    'type',
                    'cards',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'name'    => 'Konto oszczędnościowe',
        ]);
    }

    public function testCreatesAccountWithoutCard(): void
    {
        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->postJson('/api/accounts', [
            'name'      => 'Konto bez karty',
            'with_card' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(0, 'data.cards');
    }

    public function testShowsAccountDetails(): void
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-details']);

        $response = $this->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonPath('data.name', $account->name)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'account_number',
                    'balance',
                    'currency',
                    'type',
                    'cards',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function testUpdatesAccountName(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'name'    => 'Stara nazwa',
        ]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->patchJson("/api/accounts/{$account->id}", [
            'name' => 'Nowa nazwa',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nowa nazwa');

        $this->assertDatabaseHas('accounts', [
            'id'   => $account->id,
            'name' => 'Nowa nazwa',
        ]);
    }

    public function testCannotUpdateAccountNumberOrBalance(): void
    {
        $account = Account::factory()->create([
            'user_id'        => $this->user->id,
            'account_number' => '12345678901234567890123456',
            'balance'        => 100000, // 1000.00 PLN
        ]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->patchJson("/api/accounts/{$account->id}", [
            'name'           => 'Nowa nazwa',
            'account_number' => '99999999999999999999999999',
            'balance'        => 999999,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('accounts', [
            'id'             => $account->id,
            'account_number' => '12345678901234567890123456',
            'balance'        => 100000,
        ]);
    }

    public function testClosesAccountWithZeroBalance(): void
    {
        Account::query()->each(fn ($account) => $account->delete());

        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 0,
        ]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Account closed.']);

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function testCannotCloseAccountWithBalance(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 50000,
        ]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(500); // lub 422 zależnie od implementacji

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }

    public function testRequiresAuthentication(): void
    {
        $response = $this->getJson('/api/accounts');

        $response->assertStatus(401);
    }

    public function testValidatesStoreRequest(): void
    {
        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->postJson('/api/accounts', [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testValidatesUpdateRequest(): void
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        Passport::actingAs($this->user, ['accounts-view', 'accounts-manage']);

        $response = $this->patchJson("/api/accounts/{$account->id}", [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
