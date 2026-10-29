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
        Schema::create('documents_identite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type_piece', ['CARTE_ETUDIANT', 'CIP', 'IFU', 'RCCM']);
            $table->string('numero_piece');
            $table->string('photo_piece_url');
            $table->enum('statut_verification', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_identite');
    }
};
