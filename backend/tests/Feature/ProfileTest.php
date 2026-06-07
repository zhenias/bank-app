<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testShowProfile(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile']);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(200)
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function testShowProfileRequiresScope(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, []);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(403);
    }

    public function testShowProfileRequiresAuth(): void
    {
        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(401);
    }

    public function testUpdateProfileName(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->patchJson('/api/user/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function testUpdateProfileEmail(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->patchJson('/api/user/profile', [
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    public function testCannotUpdateDateOfBirthAfterSet(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->patchJson('/api/user/profile', [
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_of_birth']);
    }

    public function testCanSetDateOfBirthIfNotSet(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => null,
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->patchJson('/api/user/profile', [
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'date_of_birth' => '2000-01-01',
        ]);
    }

    public function testUpdateProfilePassword(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
            'password' => bcrypt('oldpassword'),
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->patchJson('/api/user/profile', [
            'old_password' => 'oldpassword',
            'password' => 'NewP@ssw0rd123!@',
            'password_confirmation' => 'NewP@ssw0rd123!@',
        ]);

        $response->assertStatus(200);
    }

    public function testUpdateProfileRequiresScope(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile']);

        $response = $this->patchJson('/api/user/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateProfileRequiresAuth(): void
    {
        $response = $this->patchJson('/api/user/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    public function testDeleteAccount(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile-manage']);

        $response = $this->deleteJson('/api/user/profile');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function testDeleteAccountRequiresScope(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile']);

        $response = $this->deleteJson('/api/user/profile');

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function testDeleteAccountRequiresAuth(): void
    {
        $response = $this->deleteJson('/api/user/profile');

        $response->assertStatus(401);
    }

    public function testRevokeToken(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user, ['user-profile']);

        $response = $this->postJson('/api/user/profile/revoke-token');

        $response->assertStatus(200);
    }

    public function testRevokeTokenRequiresAuth(): void
    {
        $response = $this->postJson('/api/user/profile/revoke-token');

        $response->assertStatus(401);
    }

    public function testRegisterUser(): void
    {
        $response = $this->postJson('/api/user/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'P@ssw0rd123!@',
            'password_confirmation' => 'P@ssw0rd123!@',
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('email', 'newuser@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);
    }

    public function testRegisterUserRequiresPasswordConfirmation(): void
    {
        $response = $this->postJson('/api/user/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'P@ssw0rd123!@',
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function testRegisterUserRequiresUniqueEmail(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->postJson('/api/user/register', [
            'name' => 'New User',
            'email' => 'taken@example.com',
            'password' => 'P@ssw0rd123!@',
            'password_confirmation' => 'P@ssw0rd123!@',
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testGuardianApproval(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $ward = User::factory()->create([
            'date_of_birth' => now()->subYears(10),
            'guardian_id' => $guardian->id,
            'guardian_approved_at' => null,
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson("/api/user/guardian/approve/{$ward->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Guardian approval confirmed.');

        $this->assertDatabaseHas('users', [
            'id' => $ward->id,
            'guardian_approved_at' => now(),
        ]);
    }

    public function testGuardianApprovalRequiresAdultGuardian(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(16),
        ]);
        $ward = User::factory()->create([
            'date_of_birth' => now()->subYears(10),
            'guardian_id' => $guardian->id,
            'guardian_approved_at' => null,
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson("/api/user/guardian/approve/{$ward->id}");

        $response->assertStatus(422);
    }

    public function testCannotApproveSelfAsGuardian(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user);

        $response = $this->postJson("/api/user/guardian/approve/{$user->id}");

        $response->assertStatus(422);
    }

    public function testCannotApproveAlreadyApprovedGuardian(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $ward = User::factory()->create([
            'date_of_birth' => now()->subYears(10),
            'guardian_id' => $guardian->id,
            'guardian_approved_at' => now(),
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson("/api/user/guardian/approve/{$ward->id}");

        $response->assertStatus(422);
    }

    public function testGuardianRejection(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $ward = User::factory()->create([
            'date_of_birth' => now()->subYears(10),
            'guardian_id' => $guardian->id,
            'guardian_approved_at' => null,
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson("/api/user/guardian/reject/{$ward->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Guardian request rejected.');
    }

    public function testCannotRejectAlreadyApprovedGuardian(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);
        $ward = User::factory()->create([
            'date_of_birth' => now()->subYears(10),
            'guardian_id' => $guardian->id,
            'guardian_approved_at' => now(),
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson("/api/user/guardian/reject/{$ward->id}");

        $response->assertStatus(422);
    }

    public function testCannotRejectSelfAsGuardian(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($user);

        $response = $this->postJson("/api/user/guardian/reject/{$user->id}");

        $response->assertStatus(422);
    }

    public function testGuardianApprovalReturns404ForNonExistentWard(): void
    {
        $guardian = User::factory()->create([
            'date_of_birth' => now()->subYears(30),
        ]);

        Passport::actingAs($guardian);

        $response = $this->postJson('/api/user/guardian/approve/550e8400-e29b-41d4-a716-446655440000');

        $response->assertStatus(404);
    }
}
