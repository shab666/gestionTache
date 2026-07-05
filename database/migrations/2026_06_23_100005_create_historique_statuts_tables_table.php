<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table d'historique des changements de statuts.
     *
     * Cette table est alimentée EXCLUSIVEMENT par TableObserver::updated().
     * Aucun contrôleur ou service ne doit y écrire directement.
     * Cela garantit un audit trail complet et automatique.
     */
    public function up(): void
    {
        Schema::create('historique_statuts_tables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('table_id')
                  ->constrained('restaurant_tables')
                  ->cascadeOnDelete();

            $table->string('ancien_statut');
            $table->string('nouveau_statut');

            // FK optionnelle vers l'utilisateur ayant effectué le changement
            $table->foreignId('modifie_par')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->dateTime('modifie_a');
            $table->text('raison')->nullable();

            // Pas de timestamps() car modifie_a joue ce rôle
            // (on veut une précision à la seconde, pas created_at/updated_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_statuts_tables');
    }
};
