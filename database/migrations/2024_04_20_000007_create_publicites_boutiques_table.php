<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publicites_boutiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre_promo');
            $table->string('url_image_banniere');
            $table->string('lien_action');
            $table->string('quartier_cible')->nullable();
            $table->unsignedInteger('vues_count')->default(0);
            $table->unsignedInteger('clics_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicites_boutiques');
    }
};
