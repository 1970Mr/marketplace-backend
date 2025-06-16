<?php

namespace App\Http\Requests\V1\DirectEscrow;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'integer', 'min:1'],
        ];
    }
}
