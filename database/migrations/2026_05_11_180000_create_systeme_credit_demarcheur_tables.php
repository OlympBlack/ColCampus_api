<?php
/**
 * Migration pour le système de crédits de publication (Démarcheur uniquement)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Table : portefeuille_publications
        Schema::create('portefeuilles_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('credits_gratuits')->default(2);
            $table->integer('credits_achetes')->default(0);
            $table->integer('credits_utilises')->default(0);
            $table->timestamps();
        });

        // 2. Table : forfait_publications
        Schema::create('forfaits_publications', function (Blueprint $table) {
            $table->id();
            $table->string('titre'); // Standard, Pro, Premium
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2);
            $table->integer('nombre_publications');
            $table->boolean('est_actif')->default(true);
            $table->boolean('est_populaire')->default(false);
            $table->integer('ordre_affichage')->default(0);
            $table->timestamps();
        });

        // 3. Table : achats_forfaits
        Schema::create('achats_forfaits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('forfait_id')->constrained('forfaits_publications')->onDelete('cascade');
            $table->integer('nombre_publications');
            $table->decimal('montant', 10, 2);
            $table->enum('statut_paiement', ['en_attente', 'valide', 'echoue'])->default('en_attente');
            $table->string('methode_paiement')->nullable();
            $table->string('reference_transaction')->nullable();
            $table->timestamps();
        });

        // 4. Table : transactions_credits
        Schema::create('transactions_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['GRATUIT', 'ACHAT', 'UTILISATION', 'REMBOURSEMENT']);
            $table->integer('montant'); // Positif ou négatif
            $table->string('source_type')->nullable(); // Ex: Model name
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_credits');
        Schema::dropIfExists('achats_forfaits');
        Schema::dropIfExists('forfaits_publications');
        Schema::dropIfExists('portefeuilles_publications');
    }
};
