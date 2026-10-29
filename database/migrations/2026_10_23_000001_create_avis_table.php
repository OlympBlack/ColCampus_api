<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('universite_id')->nullable()->constrained('universites')->nullOnDelete();
            $table->string('quartier');
            $table->string('type_chambre')->nullable();
            $table->unsignedTinyInteger('nombre_places')->nullable();
            $table->unsignedInteger('budget_min')->nullable();
            $table->unsignedInteger('budget_max');
            $table->date('date_souhaitee')->nullable();
            $table->text('description');
            $table->string('statut')->default('ouverte'); // ouverte, pourvue, archivee
            $table->timestamps();

            $table->index(['statut', 'created_at']);
            $table->index(['universite_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
