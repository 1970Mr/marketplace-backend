<?php

use App\Enums\Escrow\EscrowType;
use App\Enums\Products\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('about_business')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('industry')->nullable();
            $table->string('sub_industry')->nullable();
            $table->boolean('allow_buyer_message')->default(true);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_sold')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->unsignedTinyInteger('escrow_type')->default(EscrowType::ADMIN->value);
            $table->unsignedTinyInteger('status')->default(ProductStatus::PENDING->value);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->nullableMorphs('productable');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
