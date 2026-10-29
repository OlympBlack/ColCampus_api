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
        Schema::create('collections_immobilieres', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('room_type_value')->nullable();
            $table->string('price_range')->nullable();
            $table->unsignedInteger('fallback_count')->default(0);
            $table->string('image')->nullable();
            $table->string('hero_banner')->nullable();
            $table->text('description')->nullable();
            $table->text('hero_description')->nullable();
            $table->json('features')->nullable();
            $table->string('ideal_for')->nullable();
            $table->string('avg_rent')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections_immobilieres');
    }
};
