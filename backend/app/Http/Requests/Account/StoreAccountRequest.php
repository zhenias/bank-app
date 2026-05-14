<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'name'      => ['required', 'string', 'max:255'],
            'currency'  => ['sometimes', 'string', 'in:PLN'],
            'type'      => ['sometimes', 'string', 'in:current,savings'],
            'with_card' => ['sometimes', 'boolean'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'name' => [
                'description' => 'Nazwa konta',
                'example'     => 'Konto oszczędnościowe',
            ],
            'currency' => [
                'example' => 'PLN',
            ],
            'type' => [
                'example' => 'savings',
            ],
            'with_card' => [
                'example' => true,
            ],
        ];
    }
}
