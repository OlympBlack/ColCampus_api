<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quartiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universite_id')->constrained('universites')->cascadeOnDelete();
            $table->string('nom', 120);
            $table->string('arrondissement', 120)->nullable();
            $table->string('source', 20)->default('officielle');
            $table->timestamps();
            $table->unique(['universite_id', 'nom']);
            $table->index(['universite_id', 'arrondissement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quartiers');
    }
};
