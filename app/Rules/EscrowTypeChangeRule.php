<?php

namespace App\Rules;

use App\Models\Products\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class EscrowTypeChangeRule implements ValidationRule
{
    public function __construct(private string $uuidFieldName = 'uuid')
    {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $uuid = request()->get($this->uuidFieldName);

        if (!$uuid) {
            return;
        }

        $product = Product::where('uuid', $uuid)
            ->with('offer.escrow')
            ->first();

        if (!$product) {
            return;
        }

        if ($product->escrow_type?->value !== $value && $product->hasEscrow()) {
            $fail('Cannot change escrow type when product has an active escrow.');
        }
    }
}
