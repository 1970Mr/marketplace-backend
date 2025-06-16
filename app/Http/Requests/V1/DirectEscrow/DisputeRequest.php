<?php

namespace App\Http\Requests\V1\DirectEscrow;

use App\Enums\Escrow\DisputeReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(DisputeReason::class)],
            'details' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }
}
