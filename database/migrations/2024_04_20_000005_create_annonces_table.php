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
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->enum('quartier', [
                'Zogbadje',
                'Tankpe',
                'Togoudo',
                'Bidossessi',
                'Arconville',
                'Houeto',
                'Ouedo',
                'Glo-Djigbe',
                'Hevie',
                'Kpota',
                'Acacia',
                'Calavi-Bas'
            ]);
            $table->decimal('loyer', 10, 2);
            $table->decimal('caution', 10, 2);
            $table->enum('source_eau', ['SONEB', 'Forage']);
            $table->enum('type_electricite', ['SBEE_CARTE', 'SBEE_CONVENTIONNEL']);
            $table->text('lien_google_maps')->nullable();
            $table->enum('type_annonce', ['CHAMBRE_VIDE', 'PLACE_LIBRE_HOTE', 'RECHERCHE_BINOME']);
            $table->enum('statut_disponibilite', ['available', 'taken', 'pending']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
