<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Attributes\AuthorizeToken;

/**
 * Zarządzanie profilem użytkownika.
 *
 * @tags Profile
 */
class ProfileController extends Controller
{
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
    #[AuthorizeToken(['user-profile'], anyScope: true)]
    #[BodyParameter('name', description: 'Nowa nazwa użytkownika', example: 'Jan Kowalski')]
    #[BodyParameter('email', description: 'Nowy adres email', example: 'jan.kowalski@example.com')]
    #[BodyParameter('password', description: 'Nowe hasło (min. 8 znaków, max. 255 znaków, musi być potwierdzone)', example: 'P@ssw0rd123!@')]
    #[BodyParameter('password_confirmation', description: 'Potwierdzenie nowego hasła', example: 'P@ssw0rd123!@')]
    #[BodyParameter('old_password', description: 'Aktualne hasło (wymagane przy zmianie hasła)', example: 'NewP@ssw0rd123!@')]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

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
    #[AuthorizeToken(['user-profile'], anyScope: true)]
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
    #[AuthorizeToken(['user-profile'], anyScope: true)]
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
    #[BodyParameter('name', description: 'Nazwa użytkownika', type: 'string', example: 'Jan Kowalski')]
    #[BodyParameter('email', description: 'Adres email', type: 'email', example: 'jan.kowalski@example.com')]
    #[BodyParameter('password', description: 'Hasło (min. 8 znaków, max. 255 znaków, musi być potwierdzone)', type: 'password', example: 'P@ssw0rd123!@')]
    #[BodyParameter('password_confirmation', description: 'Potwierdzenie nowego hasła', type: 'string', example: 'P@ssw0rd123!@')]
    public function create(RegisterUserRequest $request): User
    {
        $validated = $request->validated();

        unset($validated['password_confirmation']);

        $user = User::create($validated);

        event(new Registered($user));

        return $user;
    }
}
