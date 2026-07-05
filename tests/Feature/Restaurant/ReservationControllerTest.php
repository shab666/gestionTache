<?php

namespace Tests\Feature\Restaurant;

use App\Contracts\ReservationServiceInterface;
use App\Enums\ReservationStatut;
use App\Enums\TableStatut;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de fonctionnalité pour les endpoints de réservations.
 *
 * Ces tests valident :
 * 1. La création atomique (DB::transaction)
 * 2. Le changement de statut de la table lors des actions
 * 3. La création automatique de l'historique par l'Observer
 * 4. Les règles de validation des FormRequests
 */
class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Zone $zone;
    private RestaurantTable $table;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->zone   = Zone::factory()->create();
        $this->table  = RestaurantTable::factory()->libre()->create(['zone_id' => $this->zone->id]);
        $this->client = Client::factory()->create();
    }

    // =========================================================================
    // Tests de CRÉATION (atomicité DB::transaction)
    // =========================================================================

    #[Test]
    public function test_creation_reservation_atomique_change_statut_table(): void
    {
        // Vérifier l'état initial
        $this->assertEquals(TableStatut::Libre, $this->table->statut);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/reservations', [
                             'table_id'         => $this->table->id,
                             'client_id'        => $this->client->id,
                             'nom_client'       => 'Jean Test',
                             'telephone_client' => '0600000000',
                             'nombre_personnes' => 2,
                             'date_debut'       => now()->addDay()->format('Y-m-d H:i:s'),
                             'date_fin'         => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.statut', 'en_attente')
                 ->assertJsonPath('message', 'Réservation créée avec succès.');

        // La réservation est en base
        $this->assertDatabaseHas('reservations', [
            'table_id'   => $this->table->id,
            'nom_client' => 'Jean Test',
            'statut'     => 'en_attente',
        ]);

        // La table a changé de statut automatiquement (atomicité !)
        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $this->table->id,
            'statut' => 'reservee',
        ]);

        // L'historique a été créé par l'Observer
        $this->assertDatabaseHas('historique_statuts_tables', [
            'table_id'       => $this->table->id,
            'ancien_statut'  => 'libre',
            'nouveau_statut' => 'reservee',
        ]);
    }

    #[Test]
    public function test_creation_reservation_echoue_si_date_debut_dans_passe(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/reservations', [
                             'table_id'         => $this->table->id,
                             'nom_client'       => 'Test',
                             'telephone_client' => '0600000000',
                             'nombre_personnes' => 2,
                             'date_debut'       => now()->subDay()->format('Y-m-d H:i:s'),
                             'date_fin'         => now()->addHour()->format('Y-m-d H:i:s'),
                         ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['date_debut']);
    }

    #[Test]
    public function test_creation_reservation_echoue_si_date_fin_avant_date_debut(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/restaurant/reservations', [
                             'table_id'         => $this->table->id,
                             'nom_client'       => 'Test',
                             'telephone_client' => '0600000000',
                             'nombre_personnes' => 2,
                             'date_debut'       => now()->addDays(2)->format('Y-m-d H:i:s'),
                             'date_fin'         => now()->addDay()->format('Y-m-d H:i:s'),
                         ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['date_fin']);
    }

    // =========================================================================
    // Tests du cycle de vie : confirmer → terminer
    // =========================================================================

    #[Test]
    public function test_confirmer_reservation_change_table_en_occupee(): void
    {
        // Arrange : table réservée, réservation en attente
        $this->table->update(['statut' => TableStatut::Reservee->value]);
        $reservation = Reservation::factory()->create([
            'table_id' => $this->table->id,
            'statut'   => ReservationStatut::EnAttente->value,
        ]);

        // Act
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson("/api/restaurant/reservations/{$reservation->id}/confirmer");

        // Assert
        $response->assertOk()
                 ->assertJsonPath('data.statut', 'confirmee');

        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $this->table->id,
            'statut' => 'occupee',
        ]);
    }

    #[Test]
    public function test_terminer_reservation_change_table_en_a_nettoyer(): void
    {
        // Arrange : table occupée, réservation confirmée
        $this->table->update(['statut' => TableStatut::Occupee->value]);
        $reservation = Reservation::factory()->create([
            'table_id' => $this->table->id,
            'statut'   => ReservationStatut::Confirmee->value,
        ]);

        // Act
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson("/api/restaurant/reservations/{$reservation->id}/terminer");

        // Assert
        $response->assertOk()
                 ->assertJsonPath('data.statut', 'terminee');

        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $this->table->id,
            'statut' => 'a_nettoyer',
        ]);
    }

    #[Test]
    public function test_annuler_reservation_remet_table_libre(): void
    {
        // Arrange : table réservée
        $this->table->update(['statut' => TableStatut::Reservee->value]);
        $reservation = Reservation::factory()->create([
            'table_id' => $this->table->id,
            'statut'   => ReservationStatut::EnAttente->value,
        ]);

        // Act
        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson("/api/restaurant/reservations/{$reservation->id}/annuler");

        // Assert
        $response->assertOk()
                 ->assertJsonPath('data.statut', 'annulee');

        $this->assertDatabaseHas('restaurant_tables', [
            'id'     => $this->table->id,
            'statut' => 'libre',
        ]);
    }

    // =========================================================================
    // Test du filtre par date
    // =========================================================================

    #[Test]
    public function test_filtre_reservations_par_date(): void
    {
        $dateCible = now()->addDays(5)->format('Y-m-d');

        // Créer une réservation pour la date cible
        Reservation::factory()->create([
            'table_id'   => $this->table->id,
            'date_debut' => now()->addDays(5)->setHour(19),
            'date_fin'   => now()->addDays(5)->setHour(21),
        ]);

        // Créer une réservation pour une autre date
        $autreTable = RestaurantTable::factory()->create(['zone_id' => $this->zone->id]);
        Reservation::factory()->create([
            'table_id'   => $autreTable->id,
            'date_debut' => now()->addDays(10)->setHour(19),
            'date_fin'   => now()->addDays(10)->setHour(21),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/restaurant/reservations?date={$dateCible}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }
}
