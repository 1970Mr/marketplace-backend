<?php

namespace App\Http\Requests\V1\DirectEscrow;

use App\Enums\Escrow\DisputeResolution;
use App\Enums\Escrow\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resolution' => ['required', Rule::enum(DisputeResolution::class)],
            'note' => ['required', 'string', 'min:1', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }
}
