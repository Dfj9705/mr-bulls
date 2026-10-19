<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 80)->nullable();
            $table->string('title', 120);
            $table->string('highlight', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('image_alt', 180);
            $table->string('primary_label', 80)->nullable();
            $table->string('primary_url', 500)->nullable();
            $table->string('secondary_label', 80)->nullable();
            $table->string('secondary_url', 500)->nullable();
            $table->string('brand_line', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('hero_slides'); }
};
