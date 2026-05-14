<?php

namespace App\Http\Requests\User;

use App\Rules\Adult\AdultGuardian;
use App\Rules\Password\CurrentPassword;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        $user = $this->user();

        return [
            /* @example Jan Kowalski */
            'name' => ['sometimes', 'string', 'max:255'],
            /* @example jan.kowalski@example.com */
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            /* @example "2000-01-01" */
            'date_of_birth' => ['sometimes', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            /* @example guardian@example.com */
            'guardian_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::exists('users', 'email'),
                Rule::notIn([$user->email]),
                new AdultGuardian(),
            ],
            /* @example "P@ssw0rd123!@" */
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                Password::min(8)
                    ->max(255)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            /* @example "P@ssw0rd123!@" */
            'password_confirmation' => ['sometimes', 'string', 'same:password'],
            /* @example "O&l*dP@ssw0rd123!@" */
            'old_password' => ['required_with:password', 'string', 'max:255', new CurrentPassword()],
            /* @example false */
            'guardian_delete' => ['sometimes', 'boolean'],
        ];
    }
}
