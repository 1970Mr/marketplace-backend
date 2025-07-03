# EscrowTypeChangeRule

## Overview
A Laravel validation rule that prevents changing the `escrow_type` of a product when it has an active escrow.

## Purpose
This rule ensures data integrity by preventing changes to the escrow type once an escrow transaction has been initiated for a product.

## Usage

### Basic Usage
```php
use App\Rules\EscrowTypeChangeRule;

$rules = [
    'escrow_type' => [
        Rule::enum(EscrowType::class),
        new EscrowTypeChangeRule()
    ],
];
```

### Custom UUID Field Name
If your request uses a different field name for the UUID:

```php
$rules = [
    'escrow_type' => [
        Rule::enum(EscrowType::class),
        new EscrowTypeChangeRule('product_uuid') // Custom field name
    ],
];
```

## Validation Logic

The rule performs the following checks:

1. **UUID Presence**: If no UUID is provided in the request, validation passes
2. **Product Existence**: If the product with the given UUID doesn't exist, validation passes
3. **Escrow Existence**: If the product has no active escrow, validation passes
4. **Type Change**: If the product has an active escrow and the escrow_type is being changed, validation fails

## Error Message
When validation fails, the following message is returned:
```
Cannot change escrow type when product has an active escrow.
```

## Implementation Details

### Constructor Parameters
- `$uuidFieldName` (string, default: 'uuid'): The name of the field containing the product UUID

### Database Relationships Used
- `Product -> Offer -> Escrow`: The rule uses this relationship chain to check for active escrows

### Methods Used
- `Product::hasEscrow()`: Checks if the product has an active escrow through its offer

## Testing
Comprehensive tests are available in `tests/Unit/Rules/EscrowTypeChangeRuleTest.php` covering:
- Valid scenarios (no escrow, same type, missing UUID)
- Invalid scenarios (attempting to change type with active escrow)
- Custom field name usage 
