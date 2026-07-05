<?php

namespace Tests\Feature\Restaurant;

use App\Contracts\TableTransitionInterface;
use App\Enums\TableStatut;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Database\Factories\RestaurantTableFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de fonctionnalité pour les endpoints de tables de restaurant.
 *
 * DÉMONSTRATION DE L'INJECTION DE DÉPENDANCES DANS LES TESTS :
 * Grâce au binding Interface → Implémentation dans AppServiceProvider,
 * on peut ici remplacer TableTransitionService par un Mock dans certains tests.
 * Le contrôleur n'a aucune idée qu'il reçoit un mock — c'est la puissance du DIP.
 */
class TableControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Crée un utilisateur authentifié pour tous les tests
        $this->user = User::factory()->create();
    }

    // =========================================================================
    // Tests du CRUD de base
    // =========================================================================

    #[Test]
    public function test_liste_les_tables_avec_authentification(): void
    {
        $zone = Zone::factory()->create();
        RestaurantTable::factory()->count(3)->create(['zone_id' => $zone->id]);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/restaurant/tables');

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'numero', 'capacite', 'forme', 'statut', 'zone'],
                     ],
                 ]);
    }

    #[Test]
    public function test_acces_refuse_sans_authentification(): void
    {
        $this->getJson('/api/restaurant/tables')
             ->assertUnauthorized();
    }

    #[Test]
    public function test_creation_table_avec_donnees_valides(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/tables', [
                             'zone_id'  => $zone->id,
                             'numero'   => 'T99',
                             'capacite' => 4,
                             'forme'    => 'rectangle',
                             'active'   => true,
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.numero', 'T99')
                 ->assertJsonPath('data.statut', 'libre')
                 ->assertJsonPath('message', 'Table créée avec succès.');

        $this->assertDatabaseHas('restaurant_tables', [
            'numero'   => 'T99',
            'zone_id'  => $zone->id,
            'statut'   => 'libre',
        ]);
    }

    #[Test]
    public function test_creation_table_echoue_avec_donnees_invalides(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/tables', [
                             'zone_id'  => 9999, // zone inexistante
                             'numero'   => '',
                             'capacite' => -1,
                             'forme'    => 'hexagone', // valeur d'enum invalide
                         ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['zone_id', 'numero', 'capacite', 'forme']);
    }

    // =========================================================================
    // Tests du changement de statut (State Machine)
    // =========================================================================

    #[Test]
    public function test_changement_statut_valide_libre_vers_reservee(): void
    {
        $table = RestaurantTable::factory()
                                ->for(Zone::factory())
                                ->libre()
                                ->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->patchJson("/api/restaurant/tables/{$table->id}/statut", [
                             'statut' => 'reservee',
                             'raison' => 'Test de réservation',
                         ]);

        $response->assertOk()
                 ->assertJsonPath('data.statut', 'reservee');

        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $table->id,
            'statut' => 'reservee',
        ]);

        // Vérifier que l'Observer a créé l'historique automatiquement
        $this->assertDatabaseHas('historique_statuts_tables', [
            'table_id'       => $table->id,
            'ancien_statut'  => 'libre',
            'nouveau_statut' => 'reservee',
            'raison'         => 'Test de réservation',
        ]);
    }

    #[Test]
    public function test_changement_statut_invalide_retourne_422(): void
    {
        // Table libre — on tente de passer en 'a_nettoyer' directement (INTERDIT)
        $table = RestaurantTable::factory()
                                ->for(Zone::factory())
                                ->libre()
                                ->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->patchJson("/api/restaurant/tables/{$table->id}/statut", [
                             'statut' => 'a_nettoyer',
                         ]);

        $response->assertUnprocessable()
                 ->assertJsonPath('error', 'INVALID_STATE_TRANSITION')
                 ->assertJsonPath('from', 'Libre')
                 ->assertJsonPath('to', 'À nettoyer');

        // La table ne doit PAS avoir changé de statut
        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $table->id,
            'statut' => 'libre',
        ]);
    }

    #[Test]
    public function test_valeur_enum_statut_invalide_retourne_422(): void
    {
        $table = RestaurantTable::factory()
                                ->for(Zone::factory())
                                ->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->patchJson("/api/restaurant/tables/{$table->id}/statut", [
                             'statut' => 'inexistant',
                         ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['statut']);
    }

    // =========================================================================
    // DÉMONSTRATION DU MOCK via l'Interface (DIP en action)
    // =========================================================================

    #[Test]
    public function test_controleur_utilise_le_service_de_transition_injecte(): void
    {
        $table = RestaurantTable::factory()
                                ->for(Zone::factory())
                                ->libre()
                                ->create();

        // On remplace TableTransitionService par un Mock grâce au binding d'interface
        // Le contrôleur ne sait pas qu'il reçoit un Mock — c'est la magie du DIP !
        $this->mock(TableTransitionInterface::class, function (MockInterface $mock) use ($table) {
            $mock->shouldReceive('canTransition')->andReturn(true);
            $mock->shouldReceive('transition')
                 ->once() // On vérifie que le contrôleur appelle bien le service
                 ->withArgs(function ($tableArg, $toArg) use ($table) {
                     return $tableArg->id === $table->id
                         && $toArg === TableStatut::Reservee;
                 });
        });

        $this->actingAs($this->user, 'sanctum')
             ->patchJson("/api/restaurant/tables/{$table->id}/statut", [
                 'statut' => 'reservee',
             ]);
        // Si le mock n'est pas appelé une fois, le test échoue automatiquement
    }

    // =========================================================================
    // Test de suppression avec protection
    // =========================================================================

    #[Test]
    public function test_suppression_table_sans_reservations_actives(): void
    {
        $table = RestaurantTable::factory()
                                ->for(Zone::factory())
                                ->create();

        $this->actingAs($this->user, 'sanctum')
             ->deleteJson("/api/restaurant/tables/{$table->id}")
             ->assertOk();

        $this->assertDatabaseMissing('restaurant_tables', ['id' => $table->id]);
    }
}
