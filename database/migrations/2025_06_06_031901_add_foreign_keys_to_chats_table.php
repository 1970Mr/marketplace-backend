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
        Schema::table('chats', static function (Blueprint $table) {
            $table->foreignId('admin_id')->change()->nullable()->constrained('admins')->cascadeOnDelete();
            $table->foreignId('escrow_id')->change()->nullable()->constrained('escrows')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            //
        });
    }
};
