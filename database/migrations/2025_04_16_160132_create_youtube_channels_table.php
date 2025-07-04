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
            $table->unsignedInteger('business_age')->nullable()->comment('Based on the month');
            $table->unsignedBigInteger('subscribers_count')->nullable();
            $table->decimal('monthly_revenue', 10, 2)->nullable()->comment('3-month avg');
            $table->float('monthly_views', 10)->nullable()->comment('3-month avg');
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
