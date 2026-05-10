<?php

namespace App\Http\Requests\User;

use App\Rules\Password\CurrentPassword;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                Password::min(8)
                    ->max(255)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
//                    ->uncompromised(),
            ],
            'old_password' => ['required_with:password', 'string', 'max:255', new CurrentPassword()],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'name' => [
                'description' => 'Nowa nazwa użytkownika',
                'example'     => 'Jan Kowalski',
            ],
            'email' => [
                'description' => 'Nowy adres email',
                'example'     => 'jan@example.com',
            ],
            'password' => [
                'description' => 'Nowe hasło (min. 8 znaków, max. 255 znaków, musi być potwierdzone)',
                'example'     => 'newpassword123',
            ],
            'password_confirmation' => [
                'description' => 'Potwierdzenie nowego hasła',
                'example'     => 'newpassword123',
            ],
        ];
    }
}
