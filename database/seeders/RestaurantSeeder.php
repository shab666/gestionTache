<?php

namespace Database\Seeders;

use App\Enums\TableForme;
use App\Enums\TableStatut;
use App\Models\Client;
use App\Models\HistoriqueStatutTable;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de démonstration pour le module restaurant.
 *
 * Crée un jeu de données réaliste et cohérent :
 * - 3 zones avec des tables variées
 * - Des clients avec historique
 * - Des réservations dans différents états
 * - De l'historique de statuts (simulé pour la démo)
 *
 * IMPORTANT : Ce seeder insère les données directement sans passer
 * par les Services/Observers pour éviter les effets de bord lors du seed.
 * En production, toujours utiliser les Services.
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🍽️  Seeding données restaurant...');

        // =====================================================================
        // ZONES
        // =====================================================================
        $this->command->line('  → Création des zones...');

        $zones = [
            [
                'nom'         => 'Salle Principale',
                'description' => 'Grande salle intérieure climatisée, vue sur le jardin.',
            ],
            [
                'nom'         => 'Terrasse',
                'description' => 'Espace extérieur ombragé, idéal pour les beaux jours.',
            ],
            [
                'nom'         => 'Bar Lounge',
                'description' => 'Espace bar avec ambiance tamisée, réservations de 2 à 4 personnes.',
            ],
        ];

        $zonesCreees = collect($zones)->map(
            fn ($z) => Zone::firstOrCreate(
                ['nom' => $z['nom']],
                ['description' => $z['description']]
            )
        );

        $zoneInterieure = $zonesCreees[0];
        $zoneTerrsase   = $zonesCreees[1];
        $zoneBar        = $zonesCreees[2];

        // =====================================================================
        // TABLES DE RESTAURANT
        // =====================================================================
        $this->command->line('  → Création des tables...');

        $tablesData = [
            // Salle Principale — 6 tables
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T01', 'capacite' => 2, 'forme' => TableForme::Rond,      'position_x' => 10, 'position_y' => 15, 'statut' => TableStatut::Libre],
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T02', 'capacite' => 4, 'forme' => TableForme::Rectangle, 'position_x' => 25, 'position_y' => 15, 'statut' => TableStatut::Reservee],
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T03', 'capacite' => 4, 'forme' => TableForme::Rectangle, 'position_x' => 40, 'position_y' => 15, 'statut' => TableStatut::Occupee],
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T04', 'capacite' => 6, 'forme' => TableForme::Ovale,     'position_x' => 10, 'position_y' => 45, 'statut' => TableStatut::ANnettoyer],
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T05', 'capacite' => 8, 'forme' => TableForme::Rectangle, 'position_x' => 30, 'position_y' => 45, 'statut' => TableStatut::Libre],
            ['zone_id' => $zoneInterieure->id, 'numero' => 'T06', 'capacite' => 2, 'forme' => TableForme::Carre,     'position_x' => 55, 'position_y' => 45, 'statut' => TableStatut::HorsService],

            // Terrasse — 4 tables
            ['zone_id' => $zoneTerrsase->id, 'numero' => 'P01', 'capacite' => 4, 'forme' => TableForme::Rond,      'position_x' => 15, 'position_y' => 20, 'statut' => TableStatut::Libre],
            ['zone_id' => $zoneTerrsase->id, 'numero' => 'P02', 'capacite' => 4, 'forme' => TableForme::Rond,      'position_x' => 40, 'position_y' => 20, 'statut' => TableStatut::Libre],
            ['zone_id' => $zoneTerrsase->id, 'numero' => 'P03', 'capacite' => 6, 'forme' => TableForme::Rectangle, 'position_x' => 65, 'position_y' => 20, 'statut' => TableStatut::Reservee],
            ['zone_id' => $zoneTerrsase->id, 'numero' => 'P04', 'capacite' => 2, 'forme' => TableForme::Carre,     'position_x' => 25, 'position_y' => 55, 'statut' => TableStatut::Libre],

            // Bar Lounge — 3 tables
            ['zone_id' => $zoneBar->id, 'numero' => 'B01', 'capacite' => 2, 'forme' => TableForme::Rond,  'position_x' => 20, 'position_y' => 30, 'statut' => TableStatut::Occupee],
            ['zone_id' => $zoneBar->id, 'numero' => 'B02', 'capacite' => 4, 'forme' => TableForme::Ovale, 'position_x' => 50, 'position_y' => 30, 'statut' => TableStatut::Libre],
            ['zone_id' => $zoneBar->id, 'numero' => 'B03', 'capacite' => 2, 'forme' => TableForme::Rond,  'position_x' => 75, 'position_y' => 30, 'statut' => TableStatut::Libre],
        ];

        // On insère directement (sans observer) pour le seed,
        // mais on utilise updateOrCreate pour éviter les doublons
        $tables = [];
        foreach ($tablesData as $data) {
            $findCriteria = [
                'zone_id' => $data['zone_id'],
                'numero'  => $data['numero'],
            ];

            $data['forme']  = $data['forme']->value;
            $data['statut'] = $data['statut']->value;
            $data['active'] = true;

            $tables[] = RestaurantTable::updateOrCreate($findCriteria, $data);
        }

        // =====================================================================
        // CLIENTS
        // =====================================================================
        $this->command->line('  → Création des clients...');

        $clients = [
            ['nom' => 'Dupont',   'prenom' => 'Jean',    'email' => 'jean.dupont@email.com',   'telephone' => '0612345678', 'notes' => 'Client VIP, préfère la fenêtre'],
            ['nom' => 'Martin',   'prenom' => 'Sophie',  'email' => 'sophie.martin@email.com', 'telephone' => '0623456789', 'notes' => null],
            ['nom' => 'Bernard',  'prenom' => 'Pierre',  'email' => 'p.bernard@email.com',     'telephone' => '0634567890', 'notes' => 'Allergie aux fruits de mer'],
            ['nom' => 'Leroy',    'prenom' => 'Marie',   'email' => 'marie.leroy@email.com',   'telephone' => '0645678901', 'notes' => null],
            ['nom' => 'Moreau',   'prenom' => 'Thomas',  'email' => null,                       'telephone' => '0656789012', 'notes' => 'Réservation téléphonique'],
        ];

        $clientsCreees = collect($clients)->map(
            fn ($c) => Client::firstOrCreate(
                $c['email'] ? ['email' => $c['email']] : ['telephone' => $c['telephone']],
                $c
            )
        );

        // =====================================================================
        // RÉSERVATIONS
        // =====================================================================
        $this->command->line('  → Création des réservations...');

        $now = now();

        $reservationsData = [
            // Réservation en attente sur T02 (table 'reservee')
            [
                'table_id'         => $tables[1]->id, // T02
                'client_id'        => $clientsCreees[0]->id,
                'nom_client'       => 'Jean Dupont',
                'telephone_client' => '0612345678',
                'nombre_personnes' => 3,
                'date_debut'       => $now->copy()->addHours(2),
                'date_fin'         => $now->copy()->addHours(4),
                'statut'           => 'en_attente',
                'notes'            => 'Anniversaire, prévoir une bougie',
            ],
            // Réservation confirmée sur T03 (table 'occupee')
            [
                'table_id'         => $tables[2]->id, // T03
                'client_id'        => $clientsCreees[1]->id,
                'nom_client'       => 'Sophie Martin',
                'telephone_client' => '0623456789',
                'nombre_personnes' => 2,
                'date_debut'       => $now->copy()->subHour(),
                'date_fin'         => $now->copy()->addHour(),
                'statut'           => 'confirmee',
                'notes'            => null,
            ],
            // Réservation terminée sur T04 (table 'a_nettoyer')
            [
                'table_id'         => $tables[3]->id, // T04
                'client_id'        => $clientsCreees[2]->id,
                'nom_client'       => 'Pierre Bernard',
                'telephone_client' => '0634567890',
                'nombre_personnes' => 5,
                'date_debut'       => $now->copy()->subHours(3),
                'date_fin'         => $now->copy()->subHour(),
                'statut'           => 'terminee',
                'notes'            => 'Menu sans fruits de mer',
            ],
            // Réservation future sur P03 (table 'reservee')
            [
                'table_id'         => $tables[8]->id, // P03
                'client_id'        => null,
                'nom_client'       => 'Thomas Moreau',
                'telephone_client' => '0656789012',
                'nombre_personnes' => 4,
                'date_debut'       => $now->copy()->addDays(2)->setHour(20)->setMinute(0),
                'date_fin'         => $now->copy()->addDays(2)->setHour(22)->setMinute(0),
                'statut'           => 'en_attente',
                'notes'            => null,
            ],
            // Réservation annulée (passée)
            [
                'table_id'         => $tables[0]->id, // T01
                'client_id'        => $clientsCreees[3]->id,
                'nom_client'       => 'Marie Leroy',
                'telephone_client' => '0645678901',
                'nombre_personnes' => 1,
                'date_debut'       => $now->copy()->subDays(2)->setHour(12),
                'date_fin'         => $now->copy()->subDays(2)->setHour(14),
                'statut'           => 'annulee',
                'notes'            => 'Annulation client',
            ],
        ];

        foreach ($reservationsData as $data) {
            Reservation::create($data);
        }

        // =====================================================================
        // HISTORIQUE DES STATUTS (simulé pour la démo)
        // — En production, c'est l'Observer qui gère ça automatiquement
        // =====================================================================
        $this->command->line('  → Création de l\'historique des statuts...');

        $historiqueData = [
            // T02 : libre → reservee
            ['table_id' => $tables[1]->id, 'ancien_statut' => 'libre',    'nouveau_statut' => 'reservee',   'modifie_a' => $now->copy()->subMinutes(30), 'raison' => 'Réservation #1 créée pour Jean Dupont'],
            // T03 : libre → reservee → confirmee/occupee
            ['table_id' => $tables[2]->id, 'ancien_statut' => 'libre',    'nouveau_statut' => 'reservee',   'modifie_a' => $now->copy()->subHours(3),    'raison' => 'Réservation #2 créée pour Sophie Martin'],
            ['table_id' => $tables[2]->id, 'ancien_statut' => 'reservee', 'nouveau_statut' => 'occupee',    'modifie_a' => $now->copy()->subHour(),      'raison' => 'Réservation #2 confirmée — client arrivé'],
            // T04 : libre → reservee → occupee → a_nettoyer
            ['table_id' => $tables[3]->id, 'ancien_statut' => 'libre',    'nouveau_statut' => 'reservee',   'modifie_a' => $now->copy()->subHours(5),    'raison' => 'Réservation #3 créée pour Pierre Bernard'],
            ['table_id' => $tables[3]->id, 'ancien_statut' => 'reservee', 'nouveau_statut' => 'occupee',    'modifie_a' => $now->copy()->subHours(3),    'raison' => 'Réservation #3 confirmée — client arrivé'],
            ['table_id' => $tables[3]->id, 'ancien_statut' => 'occupee',  'nouveau_statut' => 'a_nettoyer', 'modifie_a' => $now->copy()->subHour(),      'raison' => 'Réservation #3 terminée — table à nettoyer'],
            // T06 : libre → hors_service
            ['table_id' => $tables[5]->id, 'ancien_statut' => 'libre',    'nouveau_statut' => 'hors_service','modifie_a' => $now->copy()->subDays(1),    'raison' => 'Pied de table cassé — en attente de réparation'],
        ];

        foreach ($historiqueData as $h) {
            HistoriqueStatutTable::create($h);
        }

        $this->command->info('✅  Restaurant seeded avec succès !');
        $this->command->table(
            ['Entité', 'Quantité créée'],
            [
                ['Zones', 3],
                ['Tables', count($tables)],
                ['Clients', count($clients)],
                ['Réservations', count($reservationsData)],
                ['Entrées historique', count($historiqueData)],
            ]
        );
    }
}
