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
        Schema::create('youtube_channels', static function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->json('business_locations')->nullable();
            $table->string('channel_age')->nullable()->comment('Based on the month');
            $table->bigInteger('subscribers')->nullable();
            $table->decimal('monthly_revenue', 10, 2)->nullable();
            $table->bigInteger('monthly_views')->nullable();
            $table->string('monetization_method')->nullable();
            $table->string('analytics_screenshot')->nullable();
            $table->json('listing_images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_channels');
    }
};
