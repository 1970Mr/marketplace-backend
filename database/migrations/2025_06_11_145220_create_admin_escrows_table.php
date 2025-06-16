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
        Schema::create('admin_escrows', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1); // EscrowStatus enum
            $table->tinyInteger('phase')->default(1); // EscrowPhase enum
            $table->tinyInteger('stage')->default(1); // EscrowStage enum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_escrows');
    }
};
