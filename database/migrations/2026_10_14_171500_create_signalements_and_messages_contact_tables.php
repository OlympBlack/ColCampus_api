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
        // 1. Table des signalements d'annonces
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annonce_id')->constrained('annonces')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nom')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('motif'); // ARNAQUE_AVANCE, LOGEMENT_INDISPONIBLE, PRIX_ABUSIF, FAUSSES_INFOS, COMPORTEMENT_INAPPROPRIE, AUTRE
            $table->text('description');
            $table->string('statut')->default('EN_ATTENTE'); // EN_ATTENTE, TRAITE, REJETE
            $table->string('decision')->nullable(); // ANNONCE_DESACTIVEE, UTILISATEUR_AVERTI, SANS_SUITE, etc.
            $table->text('notes_admin')->nullable();
            $table->foreignId('traite_par_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('traite_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'created_at']);
        });

        // 2. Table des messages de contact / support
        Schema::create('messages_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nom');
            $table->string('email');
            $table->string('telephone')->nullable();
            $table->string('sujet');
            $table->text('message');
            $table->string('statut')->default('NOUVEAU'); // NOUVEAU, EN_COURS, TRAITE, ARCHIVE
            $table->text('reponse_admin')->nullable();
            $table->foreignId('traite_par_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('traite_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages_contact');
        Schema::dropIfExists('signalements');
    }
};
