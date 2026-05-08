<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Attributes\AuthorizeToken;
use Illuminate\Validation\Rules;

/**
 * Zarządzanie profilem użytkownika.
 *
 * @tags Profile
 */
#[AuthorizeToken(['user-profile'], anyScope: true)]
class ProfileController extends Controller
{

    /**
    * Pobierz profil.
    *
    * Zwraca dane zalogowanego użytkownika.
    *
    * @unauthenticated
    */
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->toArray());
    }

    /**
     * Aktualizuj profil.
     *
     * Aktualizuje dane profilowe zalogowanego użytkownika.
     */
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
    * Usuwa konto zalogowanego użytkownika.
    */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->delete();

        return response()->json(['message' => 'Account deleted'], 200);
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
     * Rejestracja nowego użytkownika.
     *
     * Tworzy nowe konto użytkownika.
     */
    public function create(Request $request): User
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }
}
