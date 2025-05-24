<?php

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Escrow\EscrowStage;
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
        Schema::create('escrows', static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('phase')->default(EscrowPhase::SIGNATURE->value);
            $table->unsignedTinyInteger('stage')->default(EscrowStage::AWAITING_SIGNATURE->value);
            $table->string('buyer_signature_path')->nullable();
            $table->string('seller_signature_path')->nullable();
            $table->json('payment_receipts')->nullable();
            $table->decimal('amount_received', 10, 2)->nullable();
            $table->decimal('amount_released', 10, 2)->nullable();
            $table->decimal('amount_refunded', 10, 2)->nullable();
            $table->unsignedTinyInteger('amount_received_method')->nullable();
            $table->unsignedTinyInteger('amount_released_method')->nullable();
            $table->unsignedTinyInteger('amount_refunded_method')->nullable();
            $table->text('cancellation_note')->nullable();
            $table->text('refund_reason')->nullable();
            $table->unsignedTinyInteger('status')->default(EscrowStatus::PENDING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
