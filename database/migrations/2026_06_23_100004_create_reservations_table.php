<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des réservations.
     *
     * Conception délibérée :
     * - client_id est nullable (réservation sans compte client)
     * - nom_client et telephone_client sont dupliqués pour la traçabilité
     *   (si un client est supprimé, on garde quand même les infos de la réservation)
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('table_id')
                  ->constrained('restaurant_tables')
                  ->cascadeOnDelete();

            // FK optionnelle : une réservation peut exister sans compte client
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->nullOnDelete();

            // Dénormalisation volontaire pour la traçabilité
            $table->string('nom_client');
            $table->string('telephone_client', 20);

            $table->unsignedInteger('nombre_personnes');

            $table->dateTime('date_debut');
            $table->dateTime('date_fin');

            $table->enum('statut', ['en_attente', 'confirmee', 'terminee', 'annulee', 'non_venue'])
                  ->default('en_attente');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index pour les recherches fréquentes par date
            $table->index(['date_debut', 'date_fin']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
