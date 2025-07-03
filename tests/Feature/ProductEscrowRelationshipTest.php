<?php

namespace Tests\Feature;

use App\Enums\Escrow\EscrowType;
use App\Models\Escrow;
use App\Models\Offer;
use App\Models\Products\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductEscrowRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
    }

    /** @test */
    public function product_can_access_escrow_through_offer()
    {
        // Create product
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        // Create offer
        $offer = Offer::factory()->create([
            'product_id' => $product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);

        // Create escrow
        $escrow = Escrow::factory()->create([
            'offer_id' => $offer->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'type' => EscrowType::DIRECT,
        ]);

        // Test product->escrow() relationship
        $this->assertNotNull($product->escrow());
        $this->assertEquals($escrow->uuid, $product->escrow()->uuid);
        $this->assertTrue($product->hasEscrow());
    }

    /** @test */
    public function escrow_can_access_product_through_offer()
    {
        // Create product
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::ADMIN
        ]);

        // Create offer
        $offer = Offer::factory()->create([
            'product_id' => $product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);

        // Create escrow
        $escrow = Escrow::factory()->create([
            'offer_id' => $offer->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'type' => EscrowType::ADMIN,
        ]);

        // Test escrow->product() relationship
        $this->assertNotNull($escrow->product());
        $this->assertEquals($product->uuid, $escrow->product()->uuid);
    }

    /** @test */
    public function product_without_escrow_returns_null()
    {
        // Create product without offer/escrow
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'escrow_type' => EscrowType::DIRECT
        ]);

        $this->assertNull($product->escrow());
        $this->assertFalse($product->hasEscrow());
    }
}
