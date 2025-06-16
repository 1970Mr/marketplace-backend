<?php

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
        Schema::create('direct_escrows', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1); // DirectEscrowStatus enum
            $table->tinyInteger('phase')->default(1); // DirectEscrowPhase enum
            $table->tinyInteger('stage')->default(1); // DirectEscrowStage enum
            $table->tinyInteger('dispute_reason')->nullable(); // DisputeReason enum
            $table->text('dispute_details')->nullable();
            $table->tinyInteger('dispute_resolution')->nullable(); // DisputeResolution enum
            $table->text('dispute_resolution_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_escrows');
    }
};
