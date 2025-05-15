<?php

namespace App\Http\Requests\V1\Admin\ProductManagement;

use App\Enums\Products\ProductStatus;
use App\Enums\Products\ProductType;
use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
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
            'status'        => 'nullable|in:' . implode(',', array_merge(
                    array_column(ProductStatus::cases(), 'value'),
                    ['deleted', 'all']
                )),
            'date_from'     => 'nullable|date',
            'date_to'       => 'nullable|date|after_or_equal:date_from',
            'category'      => 'nullable|integer|in:' . implode(',', array_column(ProductType::cases(), 'value')),
            'price_min'     => 'nullable|integer|min:0',
            'price_max'     => 'nullable|integer|min:0|gte:price_min',
            'page'          => 'nullable|integer|min:1',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ];
    }
}
