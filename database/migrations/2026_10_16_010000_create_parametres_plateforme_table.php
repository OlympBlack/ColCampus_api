<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parametres_plateforme', function (Blueprint $table) {
            $table->id();
            $table->string('nom_plateforme')->default('ColCampus');
            $table->string('slogan')->default('Plateforme étudiante de colocation et logement à Abomey-Calavi');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable()->default('/logo-colcampus.png');
            $table->string('campus_localisation')->default('Campus UAC & environs • Bénin');
            $table->string('telephone')->default('+229 01 54 47 00 02');
            $table->string('whatsapp')->default('+229 01 54 47 00 02');
            $table->string('email_contact')->default('contact@colcampus.com');
            $table->string('email_support')->default('support@colcampus.com');
            $table->string('signature_auteur')->default('GBASSI Jules-christ');
            $table->string('signature_url')->default('https://juleschrist-portfolio.vercel.app/');
            $table->string('signature_texte')->default('Conçu & développé par');
            $table->string('copyright_texte')->default('ColCampus. Tous droits réservés.');
            $table->timestamps();
        });

        // Insérer les paramètres par défaut initiaux
        DB::table('parametres_plateforme')->insert([
            'nom_plateforme' => 'ColCampus',
            'slogan' => 'Plateforme étudiante de colocation et logement à Abomey-Calavi',
            'description' => 'Plateforme étudiante de colocation et logement à Abomey-Calavi. Trouvez ou proposez facilement une chambre ou une colocation autour du campus.',
            'logo_url' => '/logo-colcampus.png',
            'campus_localisation' => 'Campus UAC & environs • Bénin',
            'telephone' => '+229 01 54 47 00 02',
            'whatsapp' => '+229 01 54 47 00 02',
            'email_contact' => 'contact@colcampus.com',
            'email_support' => 'support@colcampus.com',
            'signature_auteur' => 'GBASSI Jules-christ',
            'signature_url' => 'https://juleschrist-portfolio.vercel.app/',
            'signature_texte' => 'Conçu & développé par',
            'copyright_texte' => 'ColCampus. Tous droits réservés.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_plateforme');
    }
};
