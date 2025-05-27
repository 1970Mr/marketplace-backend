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
        Schema::create('time_slots', static function (Blueprint $table) {
            $table->id();
            $table->dateTime('datetime');
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('escrow_time_slot', static function (Blueprint $table) {
            $table->foreignId('escrow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->primary(['escrow_id', 'time_slot_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('escrow_time_slot');
    }
};
