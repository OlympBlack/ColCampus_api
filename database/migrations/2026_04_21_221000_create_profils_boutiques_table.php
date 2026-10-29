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
        Schema::create('profils_boutiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('ifu_rccm')->unique();
            $table->string('nom_boutique');
            $table->string('categorie');
            $table->text('adresse_physique');
            $table->string('logo_url')->nullable();
            $table->string('ifu_doc_path')->nullable();
            $table->string('rccm_doc_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils_boutiques');
    }
};
