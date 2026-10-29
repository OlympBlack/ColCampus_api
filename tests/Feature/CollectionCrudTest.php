<?php

namespace Tests\Feature;

use App\Models\CollectionImmobiliere;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CollectionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_and_view_collections(): void
    {
        CollectionImmobiliere::create([
            'slug' => 'studio-meuble',
            'title' => 'Studio meublé',
            'price_range' => 'Dès 50 000 FCFA/mois',
            'fallback_count' => 15,
            'image' => 'https://example.com/studio.jpg',
            'is_active' => true,
            'ordre' => 1,
        ]);

        $response = $this->getJson('/api/collections');
        $response->assertOk()
            ->assertJsonPath('data.0.slug', 'studio-meuble')
            ->assertJsonPath('data.0.title', 'Studio meublé');

        $single = $this->getJson('/api/collections/studio-meuble');
        $single->assertOk()
            ->assertJsonPath('data.title', 'Studio meublé');
    }

    public function test_admin_can_crud_collections(): void
    {
        $admin = User::factory()->create([
            'prenoms' => 'Admin',
            'telephone' => '+22997000003',
            'role' => 'admin',
            'is_verified' => true,
            'statut_verification' => 'valide',
        ]);

        Sanctum::actingAs($admin);

        // 1. Create
        $createRes = $this->postJson('/api/admin/collections', [
            'title' => 'Chambre salon VIP',
            'slug' => 'chambre-salon-vip',
            'room_type_value' => 'CHAMBRE_SALON_VIP',
            'price_range' => 'Dès 75 000 FCFA/mois',
            'fallback_count' => 8,
            'description' => 'Superbe logement standing',
            'hero_description' => 'Idéal pour doctorants',
            'features' => ['Climatisé', 'Wifi', 'Groupe'],
            'ideal_for' => 'Doctorants',
            'avg_rent' => '75 000 - 90 000 FCFA',
            'is_active' => true,
        ]);

        $createRes->assertCreated()
            ->assertJsonPath('data.slug', 'chambre-salon-vip')
            ->assertJsonPath('data.title', 'Chambre salon VIP');

        $collectionId = $createRes->json('data.id');

        // 2. Update
        $updateRes = $this->putJson("/api/admin/collections/{$collectionId}", [
            'title' => 'Chambre salon VIP Modifiée',
            'price_range' => 'Dès 80 000 FCFA/mois',
            'is_active' => true,
        ]);

        $updateRes->assertOk()
            ->assertJsonPath('data.title', 'Chambre salon VIP Modifiée');

        // 3. Toggle Status
        $toggleRes = $this->patchJson("/api/admin/collections/{$collectionId}/toggle-status");
        $toggleRes->assertOk()
            ->assertJsonPath('data.is_active', false);

        // 4. Delete
        $deleteRes = $this->deleteJson("/api/admin/collections/{$collectionId}");
        $deleteRes->assertOk();

        $this->assertDatabaseMissing('collections_immobilieres', ['id' => $collectionId]);
    }
}
