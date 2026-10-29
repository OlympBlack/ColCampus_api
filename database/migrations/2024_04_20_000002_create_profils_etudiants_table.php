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
        Schema::create('profils_etudiants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('etablissement');
            $table->string('filiere');
            $table->enum('niveau', ['L1', 'L2', 'L3', 'M1', 'M2']);
            $table->enum('sexe', ['M', 'F']);
            $table->json('preferences');
            $table->string('carte_etudiant_path')->nullable();
            $table->string('cip_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils_etudiants');
    }
};
