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
        Schema::create('instagram_accounts', static function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->json('business_locations')->nullable();
            $table->unsignedInteger('business_age')->nullable()->comment('Based on the month');
            $table->unsignedBigInteger('followers_count')->nullable();
            $table->unsignedInteger('posts_count')->nullable();
            $table->float('average_likes', 10)->nullable()->comment('3-post avg');
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
        Schema::dropIfExists('instagram_accounts');
    }
};
