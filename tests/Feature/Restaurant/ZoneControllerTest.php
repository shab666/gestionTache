<?php

namespace Tests\Feature\Restaurant;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de fonctionnalité pour ZoneController.
 */
class ZoneControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function test_liste_toutes_les_zones(): void
    {
        Zone::factory()->count(3)->create();

        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/restaurant/zones')
             ->assertOk()
             ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function test_creation_zone_valide(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/zones', [
                             'nom'         => 'Terrasse',
                             'description' => 'Espace extérieur ombragé',
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.nom', 'Terrasse')
                 ->assertJsonPath('message', 'Zone créée avec succès.');

        $this->assertDatabaseHas('zones', ['nom' => 'Terrasse']);
    }

    #[Test]
    public function test_creation_zone_sans_nom_echoue(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/restaurant/zones', ['description' => 'Sans nom'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['nom']);
    }

    #[Test]
    public function test_suppression_zone_avec_tables_actives_retourne_409(): void
    {
        $zone = Zone::factory()->create();

        // Créer des tables actives dans cette zone
        \App\Models\RestaurantTable::factory()->count(2)->create([
            'zone_id' => $zone->id,
            'active'  => true,
        ]);

        $this->actingAs($this->user, 'sanctum')
             ->deleteJson("/api/restaurant/zones/{$zone->id}")
             ->assertStatus(409)
             ->assertJsonPath('error', 'ZONE_HAS_ACTIVE_TABLES');
    }

    #[Test]
    public function test_mise_a_jour_zone(): void
    {
        $zone = Zone::factory()->create(['nom' => 'Ancien Nom']);

        $this->actingAs($this->user, 'sanctum')
             ->putJson("/api/restaurant/zones/{$zone->id}", ['nom' => 'Nouveau Nom'])
             ->assertOk()
             ->assertJsonPath('data.nom', 'Nouveau Nom');

        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'nom' => 'Nouveau Nom']);
    }
}
