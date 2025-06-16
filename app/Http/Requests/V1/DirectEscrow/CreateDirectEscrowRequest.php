<?php

namespace App\Http\Requests\V1\DirectEscrow;

use Illuminate\Foundation\Http\FormRequest;

class CreateDirectEscrowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offer_id' => ['required', 'exists:offers,id'],
            'buyer_id' => ['required', 'exists:users,id'],
            'seller_id' => ['required', 'exists:users,id'],
        ];
    }
}
