<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'statut_verification')) {
                $table->enum('statut_verification', ['quarantaine', 'pending_validation', 'non_rempli', 'en_attente', 'valide', 'rejete'])->default('non_rempli')->after('is_verified');
            }

            if (!Schema::hasColumn('users', 'commentaire_admin')) {
                $table->text('commentaire_admin')->nullable()->after('statut_verification');
            }
        });

        Schema::table('publicites_boutiques', function (Blueprint $table) {
            if (!Schema::hasColumn('publicites_boutiques', 'vues_count')) {
                $table->unsignedInteger('vues_count')->default(0)->after('quartier_cible');
            }

            if (!Schema::hasColumn('publicites_boutiques', 'clics_count')) {
                $table->unsignedInteger('clics_count')->default(0)->after('vues_count');
            }
        });

        if (!Schema::hasTable('favoris')) {
            Schema::create('favoris', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('annonce_id')->constrained('annonces')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'annonce_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('favoris')) {
            Schema::drop('favoris');
        }

        Schema::table('publicites_boutiques', function (Blueprint $table) {
            if (Schema::hasColumn('publicites_boutiques', 'clics_count')) {
                $table->dropColumn('clics_count');
            }

            if (Schema::hasColumn('publicites_boutiques', 'vues_count')) {
                $table->dropColumn('vues_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'commentaire_admin')) {
                $table->dropColumn('commentaire_admin');
            }

            if (Schema::hasColumn('users', 'statut_verification')) {
                $table->dropColumn('statut_verification');
            }
        });
    }
};
