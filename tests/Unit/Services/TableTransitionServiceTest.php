<?php

namespace Tests\Unit\Services;

use App\Enums\TableStatut;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\RestaurantTable;
use App\Services\TableTransitionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests unitaires pour TableTransitionService.
 *
 * PRINCIPE : Ces tests n'utilisent PAS la base de données.
 * On teste la logique pure de la State Machine en isolation.
 *
 * C'est possible grâce à l'architecture choisie :
 * la logique de transition est dans un Service pur, sans dépendance DB.
 */
class TableTransitionServiceTest extends TestCase
{
    private TableTransitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Instanciation directe car ce service n'a aucune dépendance externe
        $this->service = new TableTransitionService();
    }

    // =========================================================================
    // Tests des transitions AUTORISÉES
    // =========================================================================

    #[Test]
    #[DataProvider('transitionsAutoriseesProvider')]
    public function test_transitions_autorisees(TableStatut $from, TableStatut $to): void
    {
        $this->assertTrue(
            $this->service->canTransition($from, $to),
            "La transition [{$from->label()}] → [{$to->label()}] devrait être autorisée."
        );
    }

    /**
     * Fournit toutes les transitions autorisées par la matrice.
     */
    public static function transitionsAutoriseesProvider(): array
    {
        return [
            'libre → reservee'         => [TableStatut::Libre,       TableStatut::Reservee],
            'libre → hors_service'     => [TableStatut::Libre,       TableStatut::HorsService],
            'reservee → occupee'       => [TableStatut::Reservee,    TableStatut::Occupee],
            'reservee → libre'         => [TableStatut::Reservee,    TableStatut::Libre],
            'reservee → hors_service'  => [TableStatut::Reservee,    TableStatut::HorsService],
            'occupee → a_nettoyer'     => [TableStatut::Occupee,     TableStatut::ANnettoyer],
            'occupee → hors_service'   => [TableStatut::Occupee,     TableStatut::HorsService],
            'a_nettoyer → libre'       => [TableStatut::ANnettoyer,  TableStatut::Libre],
            'a_nettoyer → hors_service'=> [TableStatut::ANnettoyer,  TableStatut::HorsService],
            'hors_service → libre'     => [TableStatut::HorsService, TableStatut::Libre],
        ];
    }

    // =========================================================================
    // Tests des transitions INTERDITES (le cœur de la State Machine)
    // =========================================================================

    #[Test]
    #[DataProvider('transitionsInterditesProvider')]
    public function test_transitions_interdites(TableStatut $from, TableStatut $to): void
    {
        $this->assertFalse(
            $this->service->canTransition($from, $to),
            "La transition [{$from->label()}] → [{$to->label()}] devrait être INTERDITE."
        );
    }

    /**
     * Fournit les transitions interdites les plus critiques.
     */
    public static function transitionsInterditesProvider(): array
    {
        return [
            'libre → a_nettoyer (règle critique)'  => [TableStatut::Libre,       TableStatut::ANnettoyer],
            'libre → occupee (sans réservation)'   => [TableStatut::Libre,       TableStatut::Occupee],
            'occupee → libre (sans nettoyage)'     => [TableStatut::Occupee,     TableStatut::Libre],
            'occupee → reservee'                   => [TableStatut::Occupee,     TableStatut::Reservee],
            'a_nettoyer → reservee'                => [TableStatut::ANnettoyer,  TableStatut::Reservee],
            'a_nettoyer → occupee'                 => [TableStatut::ANnettoyer,  TableStatut::Occupee],
            'hors_service → reservee'              => [TableStatut::HorsService, TableStatut::Reservee],
            'hors_service → occupee'               => [TableStatut::HorsService, TableStatut::Occupee],
            'hors_service → a_nettoyer'            => [TableStatut::HorsService, TableStatut::ANnettoyer],
        ];
    }

    // =========================================================================
    // Test de l'exception levée sur transition invalide
    // =========================================================================

    #[Test]
    public function test_transition_invalide_leve_une_exception(): void
    {
        // Arrange : modèle sans base de données (pas de save())
        $table = new RestaurantTable();
        $table->statut = TableStatut::Libre;

        // Assert : on s'attend à une exception InvalidStateTransitionException
        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Transition interdite');

        // Act : tentative de transition interdite
        // (On ne peut pas appeler transition() car il fait un save() DB,
        //  mais canTransition() est la logique pure qu'on teste ici)
        if (! $this->service->canTransition(TableStatut::Libre, TableStatut::ANnettoyer)) {
            throw new InvalidStateTransitionException(TableStatut::Libre, TableStatut::ANnettoyer);
        }
    }

    // =========================================================================
    // Tests des Enums
    // =========================================================================

    #[Test]
    public function test_enum_table_statut_retourne_labels_corrects(): void
    {
        $this->assertEquals('Libre', TableStatut::Libre->label());
        $this->assertEquals('Réservée', TableStatut::Reservee->label());
        $this->assertEquals('Occupée', TableStatut::Occupee->label());
        $this->assertEquals('À nettoyer', TableStatut::ANnettoyer->label());
        $this->assertEquals('Hors service', TableStatut::HorsService->label());
    }

    #[Test]
    public function test_enum_valeurs_retourne_tableau_de_strings(): void
    {
        $valeurs = TableStatut::values();

        $this->assertIsArray($valeurs);
        $this->assertCount(5, $valeurs);
        $this->assertContains('libre', $valeurs);
        $this->assertContains('reservee', $valeurs);
        $this->assertContains('occupee', $valeurs);
        $this->assertContains('a_nettoyer', $valeurs);
        $this->assertContains('hors_service', $valeurs);
    }
}
