<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KycWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_role_profile_and_requires_profile_photo(): void
    {
        Storage::fake('public');
        Mail::fake();

        $response = $this->post('/api/register', [
            'role' => 'etudiant',
            'name' => 'Doe',
            'prenoms' => 'Jane',
            'email' => 'jane@example.test',
            'password' => 'password123',
            'telephone' => '+22997000001',
            'photo_profil' => UploadedFile::fake()->image('avatar.jpg'),
            'etablissement' => 'IFRI',
            'filiere' => 'Genie Logiciel',
            'niveau' => 'L3',
            'sexe' => 'F',
            'preferences' => ['Calme', 'Non-fumeur'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'etudiant')
            ->assertJsonPath('user.statut_verification', 'quarantaine')
            ->assertJsonPath('user.profil.etablissement', 'IFRI');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.test',
            'role' => 'etudiant',
            'statut_verification' => 'quarantaine',
        ]);

        $this->assertDatabaseHas('profils_etudiants', [
            'etablissement' => 'IFRI',
            'filiere' => 'Genie Logiciel',
            'niveau' => 'L3',
            'sexe' => 'F',
        ]);
    }

    public function test_boutique_registration_persists_adresse_physique(): void
    {
        Storage::fake('public');
        Mail::fake();

        $response = $this->post('/api/register', [
            'role' => 'boutique',
            'name' => 'Bio',
            'prenoms' => 'Aminatou',
            'email' => 'aminatou@shop.test',
            'password' => 'password123',
            'telephone' => '+22997000011',
            'photo_profil' => UploadedFile::fake()->image('avatar.jpg'),
            'ifu_rccm' => 'RCCM-2026-001',
            'nom_boutique' => 'Amina Tech',
            'categorie' => 'Informatique',
            'adresse_physique' => 'Abomey-Calavi, carrefour IITA',
            'logo_boutique' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'boutique')
            ->assertJsonPath('user.profil.nom_boutique', 'Amina Tech')
            ->assertJsonPath('user.profil.adresse_physique', 'Abomey-Calavi, carrefour IITA');

        $this->assertDatabaseHas('profils_boutiques', [
            'ifu_rccm' => 'RCCM-2026-001',
            'nom_boutique' => 'Amina Tech',
            'adresse_physique' => 'Abomey-Calavi, carrefour IITA',
        ]);
    }
    public function test_validated_identity_document_unlocks_publishing_and_dashboard_actions(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'prenoms' => 'Document',
            'telephone' => '+22997000012',
            'role' => 'etudiant',
            'is_verified' => true,
            'statut_verification' => 'en_attente',
            'photo_profil' => 'profiles/avatar.jpg',
        ]);

        $user->profilEtudiant()->create([
            'etablissement' => 'IFRI',
            'filiere' => 'Genie Logiciel',
            'niveau' => 'L3',
            'sexe' => 'M',
            'preferences' => ['Calme'],
        ]);

        $user->documentsIdentite()->create([
            'type_piece' => 'CARTE_ETUDIANT',
            'numero_piece' => 'ETU-2026-VALIDE',
            'photo_piece_url' => 'kyc-documents/carte-valide.jpg',
            'statut_verification' => 'valide',
            'commentaire_admin' => null,
        ]);

        Sanctum::actingAs($user->fresh());

        $payload = [
            'titre' => 'Place libre a Tankpe',
            'description' => 'Acces debloque grace au document valide.',
            'quartier' => 'Tankpe',
            'loyer' => 18000,
            'caution' => 36000,
            'source_eau' => 'SONEB',
            'type_electricite' => 'SBEE_CARTE',
            'type_annonce' => 'PLACE_LIBRE_HOTE',
        ];

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.verification.can_publish', true)
            ->assertJsonPath('data.verification.can_contact', true);

        $this->post('/api/annonces', $payload)
            ->assertCreated()
            ->assertJsonPath('data.type_annonce', 'PLACE_LIBRE_HOTE');
    }
    public function test_kyc_submission_sets_pending_status_and_verified_middleware_blocks_then_allows_publishing(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'prenoms' => 'Testeur',
            'telephone' => '+22997000002',
            'role' => 'etudiant',
            'is_verified' => true,
            'statut_verification' => 'non_rempli',
            'photo_profil' => 'profiles/avatar.jpg',
        ]);

        $user->profilEtudiant()->create([
            'etablissement' => 'IFRI',
            'filiere' => 'Genie Logiciel',
            'niveau' => 'L3',
            'sexe' => 'M',
            'preferences' => ['Calme'],
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'titre' => 'Place libre a Zogbadje',
            'description' => 'Chambre propre et calme.',
            'quartier' => 'Zogbadje',
            'loyer' => 15000,
            'caution' => 30000,
            'source_eau' => 'SONEB',
            'type_electricite' => 'SBEE_CARTE',
            'type_annonce' => 'PLACE_LIBRE_HOTE',
        ];

        $this->post('/api/annonces', $payload)
            ->assertForbidden()
            ->assertJsonPath('verification_required', true);

        $this->post('/api/kyc/documents', [
            'documents' => [
                [
                    'type_piece' => 'CARTE_ETUDIANT',
                    'numero_piece' => 'ETU-2026-001',
                    'fichier' => UploadedFile::fake()->image('carte.jpg'),
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('user.statut_verification', 'pending_validation')
            ->assertJsonPath('user.documents_identite.0.statut_verification', 'en_attente');

        $this->assertDatabaseHas('documents_identite', [
            'user_id' => $user->id,
            'type_piece' => 'CARTE_ETUDIANT',
            'statut_verification' => 'en_attente',
        ]);

        $admin = User::factory()->create([
            'prenoms' => 'Administrateur',
            'telephone' => '+22997000003',
            'role' => 'admin',
            'is_verified' => true,
            'statut_verification' => 'valide',
        ]);

        Sanctum::actingAs($admin);

        $this->patch('/api/admin/kyc/' . $user->id, [
            'statut_verification' => 'valide',
        ])
            ->assertOk()
            ->assertJsonPath('user.statut_verification', 'valide')
            ->assertJsonPath('user.documents_identite.0.statut_verification', 'valide');

        Sanctum::actingAs($user->fresh());

        $this->post('/api/annonces', $payload)
            ->assertCreated()
            ->assertJsonPath('data.type_annonce', 'PLACE_LIBRE_HOTE');

        $this->assertDatabaseHas('documents_identite', [
            'user_id' => $user->id,
            'type_piece' => 'CARTE_ETUDIANT',
            'statut_verification' => 'valide',
        ]);

        $this->assertDatabaseHas('annonces', [
            'user_id' => $user->id,
            'titre' => 'Place libre a Zogbadje',
        ]);
    }
}
