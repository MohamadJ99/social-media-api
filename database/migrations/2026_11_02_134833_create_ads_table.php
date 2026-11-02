<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();

            // Advertisement content
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Advertiser information
            $table->string('advertiser_name', 120);
            $table->string('advertiser_logo_path')->nullable();

            // Advertisement media and destination
            $table->string('image_path');
            $table->text('target_url');

            // Status
            $table->boolean('is_active')->default(false);

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};