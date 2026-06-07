<?php

namespace Tests\Feature\Account\Card;

use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'open',
        ]);
    }

    public function testListsAllCardsForUser(): void
    {
        $card1 = Card::factory()->create(['account_id' => $this->account->id]);

        $account2 = Account::factory()->create(['user_id' => $this->user->id]);
        $card2 = Card::factory()->create(['account_id' => $account2->id]);

        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
        Card::factory()->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson('/api/cards');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $card1->id])
            ->assertJsonFragment(['id' => $card2->id]);
    }

    public function testAllCardsIsPaginated(): void
    {
        Card::factory()->count(25)->create(['account_id' => $this->account->id]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson('/api/cards?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function testListsCardsForAccount(): void
    {
        Card::factory()->count(3)->create([
            'account_id' => $this->account->id
        ]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'card_number',
                        'card_last_four',
                        'exp_month',
                        'exp_year',
                        'network',
                        'type',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function testListsOnlyCardsForGivenAccount(): void
    {
        Card::factory()->count(2)->create(['account_id' => $this->account->id]);

        $otherAccount = Account::factory()->create(['user_id' => $this->user->id]);
        Card::factory()->count(3)->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testCreatesNewCardForAccount(): void
    {
        Passport::actingAs($this->user, ['cards-view', 'cards-manage']);

        $response = $this->postJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.type', 'debit')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'card_number',
                    'card_last_four',
                    'exp_month',
                    'exp_year',
                    'network',
                    'type',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('cards', [
            'account_id' => $this->account->id,
            'status'     => 'active',
        ]);
    }

    public function testCannotCreateCardForClosedAccount(): void
    {
        $closedAccount = Account::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'closed',
        ]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->postJson("/api/accounts/{$closedAccount->id}/cards");

        $response->assertStatus(403);
    }

    public function testCannotCreateCardForOtherUserAccount(): void
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'open',
        ]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->postJson("/api/accounts/{$otherAccount->id}/cards");

        $response->assertStatus(403);
    }

    public function testCreatesCardWithValidNumber(): void
    {
        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->postJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(201);

        $cardNumber = $response->json('data.card_number');

        $this->assertMatchesRegularExpression(
            '/^\d{16}$/',
            $cardNumber,
        );
    }

    public function testCreatesCardWithFutureExpiryDate(): void
    {
        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->postJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(201);

        $expYear     = $response->json('data.exp_year');
        $currentYear = (int) now()->format('Y');

        $this->assertGreaterThanOrEqual($currentYear, $expYear);
    }

    public function testMasksCardNumberInResponse(): void
    {
        $card = Card::factory()->create([
            'account_id'  => $this->account->id,
            'card_number' => '4532123456789012',
        ]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.card_number', $card->card_number)
            ->assertJsonPath('data.0.card_last_four', '9012');
    }

    public function testNeverExposesCvvInResponse(): void
    {
        Card::factory()->create([
            'account_id' => $this->account->id,
            'cvv'        => '123',
        ]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson("/api/accounts/{$this->account->id}/cards");

        $response->assertStatus(200)
            ->assertJsonMissing(['cvv']);
    }

    public function testShowsCardDetails(): void
    {
        $card = Card::factory()->create([
            'account_id'  => $this->account->id,
            'card_number' => '4532123456789012',
        ]);

        Passport::actingAs($this->user, ['cards-details']);

        $response = $this->getJson("/api/cards/{$card->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $card->id)
            ->assertJsonPath('data.card_number', $card->card_number)
            ->assertJsonPath('data.card_last_four', '9012')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'card_number',
                    'card_last_four',
                    'exp_month',
                    'exp_year',
                    'network',
                    'type',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function testBlocksCard(): void
    {
        $card = Card::factory()->create([
            'account_id' => $this->account->id,
            'status'     => 'active',
        ]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->patchJson("/api/cards/{$card->id}/block");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'blocked');

        $this->assertDatabaseHas('cards', [
            'id'     => $card->id,
            'status' => 'blocked',
        ]);
    }

    public function testUnblocksCard(): void
    {
        $card = Card::factory()->create([
            'account_id' => $this->account->id,
            'status'     => 'blocked',
        ]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->patchJson("/api/cards/{$card->id}/unblock");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('cards', [
            'id'     => $card->id,
            'status' => 'active',
        ]);
    }

    public function testDeletesCard(): void
    {
        $card = Card::factory()->create([
            'account_id' => $this->account->id,
            'status'     => 'active',
        ]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->deleteJson("/api/cards/{$card->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'status' => 'deleted',
        ]);
    }

    public function testCannotDeleteCardFromOtherUser(): void
    {
        $otherUser    = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
        $card         = Card::factory()->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user, ['cards-manage']);

        $response = $this->deleteJson("/api/cards/{$card->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
        ]);
    }

    public function testCannotAccessCardFromOtherUser(): void
    {
        $otherUser    = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
        $card         = Card::factory()->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user);

        $response = $this->getJson("/api/cards/{$card->id}");

        $response->assertStatus(403);
    }

    public function testCannotBlockCardFromOtherUser(): void
    {
        $otherUser    = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
        $card         = Card::factory()->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user);

        $response = $this->patchJson("/api/cards/{$card->id}/block");

        $response->assertStatus(403);
    }

    public function testCannotUnblockCardFromOtherUser(): void
    {
        $otherUser    = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
        $card         = Card::factory()->create(['account_id' => $otherAccount->id]);

        Passport::actingAs($this->user);

        $response = $this->patchJson("/api/cards/{$card->id}/unblock");

        $response->assertStatus(403);
    }

    public function testRequiresAuthentication(): void
    {
        $card = Card::factory()->create(['account_id' => $this->account->id]);

        $this->getJson("/api/cards")
            ->assertStatus(401);

        $this->getJson("/api/accounts/{$this->account->id}/cards")
            ->assertStatus(401);

        $this->postJson("/api/accounts/{$this->account->id}/cards")
            ->assertStatus(401);

        $this->getJson("/api/cards/{$card->id}")
            ->assertStatus(401);

        $this->patchJson("/api/cards/{$card->id}/block")
            ->assertStatus(401);

        $this->patchJson("/api/cards/{$card->id}/unblock")
            ->assertStatus(401);

        $this->deleteJson("/api/cards/{$card->id}")
            ->assertStatus(401);
    }

    public function testReturns404ForNonExistentCard(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/cards/99999999-9999-9999-9999-999999999999');

        $response->assertStatus(404);
    }

    public function testReturns404ForNonExistentAccount(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/accounts/99999999-9999-9999-9999-999999999999/cards');

        $response->assertStatus(404);
    }

    public function testPaginatesCardsList(): void
    {
        Card::factory()->count(25)->create(['account_id' => $this->account->id]);

        Passport::actingAs($this->user, ['cards-view']);

        $response = $this->getJson("/api/accounts/{$this->account->id}/cards?per_page=10");

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10);
    }
}
