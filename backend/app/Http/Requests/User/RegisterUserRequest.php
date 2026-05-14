<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            /* @example Jan Kowalski */
            'name' => ['required', 'string', 'max:255'],
            /* @example jan.kowalski@example.com */
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            /* @example "2000-01-01" */
            'date_of_birth' => ['sometimes', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            /* @example "P@ssw0rd123!@" */
            'password' => [
                'required',
                'string',
                'confirmed',
                new Password(8)
                    ->max(255)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            /* @example "P@ssw0rd123!@" */
            'password_confirmation' => ['sometimes', 'string', 'same:password'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'password_confirmation' => [
                'description' => 'Must match the password field.',
                'example'     => 'P@ssw0rd123!@',
            ],
        ];
    }
}
