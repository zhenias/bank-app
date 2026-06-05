<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_account_number' => ['required', 'string', 'size:26'],
            'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_account_number.required' => 'Numer konta odbiorcy jest wymagany.',
            'to_account_number.size' => 'Numer konta musi mieć 26 znaków.',
            'amount.required' => 'Kwota jest wymagana.',
            'amount.min' => 'Minimalna kwota to 0.01 PLN.',
        ];
    }
}

