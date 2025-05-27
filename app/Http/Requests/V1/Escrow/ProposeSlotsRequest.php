<?php

namespace App\Http\Requests\V1\Escrow;

use Illuminate\Foundation\Http\FormRequest;

class ProposeSlotsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slot_ids' => ['required', 'array', 'min:1'],
            'slot_ids.*' => ['required', 'integer', 'exists:time_slots,id']
        ];
    }
}
