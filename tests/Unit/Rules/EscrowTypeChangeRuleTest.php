<?php

namespace Tests\Unit\Rules;

use App\Enums\Escrow\EscrowType;
use App\Models\Escrow;
use App\Models\Offer;
use App\Models\Products\Product;
use App\Models\User;
use App\Rules\EscrowTypeChangeRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EscrowTypeChangeRuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
    }

    /** @test */
    public function validation_passes_when_product_does_not_exist()
    {
        $rule = new EscrowTypeChangeRule();

        // Mock request with non-existent UUID
        $request = new Request(['uuid' => 'non-existent-uuid']);
        app()->instance('request', $request);

        $fail = false;
        $rule->validate('escrow_type', EscrowType::ADMIN->value, function () use (&$fail) {
            $fail = true;
        });

        $this->assertFalse($fail);
    }

    /** @test */
    public function validation_passes_when_no_uuid_provided()
    {
        $rule = new EscrowTypeChangeRule();

        // Mock request without UUID
        $request = new Request([]);
        app()->instance('request', $request);

        $fail = false;
        $rule->validate('escrow_type', EscrowType::ADMIN->value, function () use (&$fail) {
            $fail = true;
        });

        $this->assertFalse($fail);
    }

    /** @test */
    public function validation_passes_when_product_has_no_escrow()
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        $rule = new EscrowTypeChangeRule();

        // Mock request with product UUID
        $request = new Request(['uuid' => $product->uuid]);
        app()->instance('request', $request);

        $fail = false;
        $rule->validate('escrow_type', EscrowType::ADMIN->value, function () use (&$fail) {
            $fail = true;
        });

        $this->assertFalse($fail);
    }

    /** @test */
    public function validation_passes_when_escrow_type_not_changed()
    {
        // Create product with escrow
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        $offer = Offer::factory()->create([
            'product_id' => $product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);

        Escrow::factory()->create([
            'offer_id' => $offer->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'type' => EscrowType::DIRECT,
        ]);

        $rule = new EscrowTypeChangeRule();

        // Mock request with same escrow type
        $request = new Request(['uuid' => $product->uuid]);
        app()->instance('request', $request);

        $fail = false;
        $rule->validate('escrow_type', EscrowType::DIRECT->value, function () use (&$fail) {
            $fail = true;
        });

        $this->assertFalse($fail);
    }

    /** @test */
    public function validation_fails_when_trying_to_change_escrow_type_with_active_escrow()
    {
        // Create product with escrow
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        $offer = Offer::factory()->create([
            'product_id' => $product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);

        Escrow::factory()->create([
            'offer_id' => $offer->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'type' => EscrowType::DIRECT,
        ]);

        $rule = new EscrowTypeChangeRule();

        // Mock request trying to change escrow type
        $request = new Request(['uuid' => $product->uuid]);
        app()->instance('request', $request);

        $fail = false;
        $failMessage = '';
        $rule->validate('escrow_type', EscrowType::ADMIN->value, function ($message) use (&$fail, &$failMessage) {
            $fail = true;
            $failMessage = $message;
        });

        $this->assertTrue($fail);
        $this->assertEquals('Cannot change escrow type when product has an active escrow.', $failMessage);
    }

    /** @test */
    public function validation_uses_custom_uuid_field_name()
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        $rule = new EscrowTypeChangeRule('product_uuid');

        // Mock request with custom field name
        $request = new Request(['product_uuid' => $product->uuid]);
        app()->instance('request', $request);

        $fail = false;
        $rule->validate('escrow_type', EscrowType::ADMIN->value, function () use (&$fail) {
            $fail = true;
        });

        $this->assertFalse($fail); // Should pass because no escrow exists
    }
}
