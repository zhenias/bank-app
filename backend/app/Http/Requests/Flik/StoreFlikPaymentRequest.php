<?php

namespace App\Http\Requests\Flik;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlikPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ];
    }
}
