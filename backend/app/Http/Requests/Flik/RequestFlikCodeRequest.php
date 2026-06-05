<?php

namespace App\Http\Requests\Flik;

use Illuminate\Foundation\Http\FormRequest;

class RequestFlikCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_id' => ['required', 'uuid', 'exists:cards,id'],
            'account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01', 'max:9999.99'],
        ];
    }
}

