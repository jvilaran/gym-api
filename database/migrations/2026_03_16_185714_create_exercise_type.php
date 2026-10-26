<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true);
            // Catalog metadata (null for manually created types).
            $table->string('catalog_id')->nullable()->unique();
            $table->string('slug')->nullable();
            $table->string('muscle')->nullable();
            $table->string('equipment')->nullable();
            $table->string('gif_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_types');
    }
};
