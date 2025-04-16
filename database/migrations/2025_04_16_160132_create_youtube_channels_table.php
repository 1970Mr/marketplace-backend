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
            $table->uuid();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('url')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->json('business_location')->nullable();
            $table->string('age_of_channel')->nullable()->comment('Based on the month');
            $table->bigInteger('subscribers')->nullable();
            $table->decimal('monthly_revenue', 10, 2)->nullable();
            $table->bigInteger('monthly_views')->nullable();
            $table->string('monetization_method')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->text('summary')->nullable();
            $table->text('about_channel')->nullable();
            $table->boolean('allow_buyer_messages')->default(true);
            $table->boolean('is_private')->default(false);
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
