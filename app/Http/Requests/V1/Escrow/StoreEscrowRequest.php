<?php

namespace App\Http\Requests\V1\Escrow;

use Illuminate\Foundation\Http\FormRequest;

class StoreEscrowRequest extends FormRequest
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
            'offer_id'  => ['required', 'uuid', 'exists:offers,uuid'],
            'buyer_id'  => ['required', 'uuid', 'exists:users,uuid'],
            'seller_id' => ['required', 'uuid', 'exists:users,uuid'],
        ];
    }
}
