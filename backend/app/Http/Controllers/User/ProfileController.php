<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Services\Guardian\GuardianService;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Attributes\AuthorizeToken;

/**
 * Zarządzanie profilem użytkownika.
 *
 * @tags Użytkownik
 */
class ProfileController extends Controller
{
    public function __construct(
        private readonly GuardianService $guardianService,
    ) {
    }

    /**
     * Informacja o profilu użytkownika.
     *
     * Zwraca dane aktualnego zalogowanego użytkownika.
     */
    #[AuthorizeToken(['user-profile'], anyScope: true)]
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->toArray());
    }

    /**
     * Aktualizuj profil.
     *
     * Aktualizuje dane profilowe użytkownika.
     */
    #[AuthorizeToken(['user-profile-manage'], anyScope: true)]
    #[BodyParameter('name', description: 'Nowa nazwa użytkownika', example: 'Jan Kowalski')]
    #[BodyParameter('email', description: 'Nowy adres email', example: 'jan.kowalski@example.com')]
    #[BodyParameter('guardian_email', description: 'E-mail opiekuna, który powinien zatwierdzić że należysz do niego.', example: 'opiekun@example.com')]
    #[BodyParameter('guardian_delete', description: 'Usuwanie e-mail opiekuna, jeśli dziecko jest pełnoletnie.', example: false)]
    #[BodyParameter('date_of_birth', description: 'Data urodzenia użytkownika', example: '2000-01-01')]
    #[BodyParameter('password', description: 'Nowe hasło (min. 8 znaków, max. 255 znaków, musi być potwierdzone)', example: 'P@ssw0rd123!@')]
    #[BodyParameter('password_confirmation', description: 'Potwierdzenie nowego hasła', example: 'P@ssw0rd123!@')]
    #[BodyParameter('old_password', description: 'Aktualne hasło (wymagane przy zmianie hasła)', example: 'NewP@ssw0rd123!@')]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user      = $request->user();

        if (isset($validated['guardian_email']) && ! $user->guardian_id) {
            $guardian = User::query()->where('email', $validated['guardian_email'])->first();

            $validated['guardian_id'] = $guardian->id;

            $this->guardianService->sendGuardianRequest($user, $guardian);

            unset($validated['guardian_email']);
        }

        if (isset($validated['guardian_delete']) && $user->guardian_id && $user->isAdult()) {
            $validated['guardian_id']          = null;
            $validated['guardian_approved_at'] = null;
        }

        if (isset($validated['date_of_birth']) && $user->date_of_birth) {
            throw ValidationException::withMessages(['date_of_birth' => ['Date of birth cannot be changed after it has been set.']]);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $request->user()->fresh(),
        ]);
    }

    /**
     * Usuń konto.
     *
     * Usuwa konto zalogowanego użytkownika - na zawsze!
     */
    #[AuthorizeToken(['user-profile-manage'], anyScope: true)]
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->delete();

        return response()->json(null, 200);
    }

    /**
     * Wylogowanie i unieważnienie tokena.
     *
     * Cofnij aktualny token dostępu użytkownika, unieważniając go.
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(null, 200);
    }

    /**
     * Rejestracja użytkownika w systemie.
     *
     * Tworzy nowe konto użytkownika.
     *
     * @unauthenticated
     */
    public function create(RegisterUserRequest $request): User
    {
        $validated = $request->validated();

        unset($validated['password_confirmation']);

        // Email verified, because it doesn't work locally :)
        $validated['email_verified_at'] = now();

        return User::create($validated);
    }

    /**
     * Zatwierdzanie opiekuństwa, które dziecko wysłało.
     *
     * Zatwierdzenie opiekuństwa nad kontem dziecka.
     */
    #[PathParameter('wardId', description: 'Identyfikator użytkownika podopiecznego.', type: 'int', example: '123e4567-e89b-12d3-a456-426614174000')]
    public function approveGuardian(string $wardId): JsonResponse
    {
        $guardian = auth()->user();
        $ward     = User::query()->where('id', $wardId)->first();

        if (! $ward) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if (! $guardian->isAdult()) {
            throw ValidationException::withMessages(['guardian' => ['Guardian must be at least 18 years old.']]);
        }

        if (isset($ward->guardian_approved_at) && $ward->guardian_approved_at) {
            return response()->json([
                'message' => 'Guardian is approved.',
            ], 422);
        }

        if ($guardian->id === $ward->id) {
            return response()->json([
                'message' => 'You cannot approve yourself as your own guardian.',
            ], 422);
        }

        $this->guardianService->approveGuardian($guardian, $ward);

        return response()->json([
            'message' => 'Guardian approval confirmed.',
        ]);
    }

    /**
     * Odrzucenie opiekuństwa, które dziecko wysłało.
     *
     * Odrzucenie opiekuństwa nad kontem dziecka.
     */
    #[PathParameter('wardId', description: 'Identyfikator użytkownika podopiecznego.', type: 'int', example: '123e4567-e89b-12d3-a456-426614174000')]
    public function rejectGuardian(string $wardId): JsonResponse
    {
        $guardian = auth()->user();
        $ward     = User::query()->where('id', $wardId)->first();

        if (! $ward) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if (isset($ward->guardian_approved_at) && $ward->guardian_approved_at) {
            return response()->json([
                'message' => 'Guardian is approved.',
            ], 422);
        }

        if ($guardian->id === $ward->id) {
            return response()->json([
                'message' => 'You cannot reject yourself as your own guardian.',
            ], 422);
        }

        $this->guardianService->rejectGuardian($guardian, $ward);

        return response()->json([
            'message' => 'Guardian request rejected.',
        ]);
    }
}
