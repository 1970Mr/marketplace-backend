<?php

namespace App\Http\Requests\V1\Admin\Users;

use App\Enums\Users\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

class UserManagementFilterRequest extends FormRequest
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
            'search'        => 'nullable|string|max:255',
            'date_from'     => 'nullable|date',
            'date_to'       => 'nullable|date|after_or_equal:from',
            'country'       => 'nullable|string|max:255',
            'status'        => 'nullable|in:' . implode(',', array_column(UserStatus::cases(), 'value')),
            'escrow_min'    => 'nullable|integer|min:0',
            'escrow_max'    => 'nullable|integer|min:0',
            'paid_min'      => 'nullable|integer|min:0',
            'paid_max'      => 'nullable|integer|min:0',
            'listing_count' => 'nullable|string|in:1-3,3-5,5-10,10+',
            'escrow_count'  => 'nullable|string|in:1-3,3-5,5-10,10+',
            'page'          => 'nullable|integer|min:1',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ];
    }
}
