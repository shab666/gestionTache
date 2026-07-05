<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des tables de restaurant.
     *
     * IMPORTANT : La table SQL est nommée 'restaurant_tables' et non 'tables'
     * pour éviter le conflit avec le mot réservé SQL TABLE.
     * Le modèle Eloquent déclare : protected $table = 'restaurant_tables';
     */
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('zone_id')
                  ->constrained('zones')
                  ->cascadeOnDelete();

            $table->string('numero');
            $table->unsignedInteger('capacite');

            $table->enum('forme', ['rond', 'carre', 'rectangle', 'ovale'])
                  ->default('rectangle');

            // Position CSS pour le plan interactif 
            $table->decimal('position_x', 5, 2)->default(0.00);
            $table->decimal('position_y', 5, 2)->default(0.00);

            $table->enum('statut', ['libre', 'reservee', 'occupee', 'a_nettoyer', 'hors_service'])
                  ->default('libre');

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Unicité du numéro de table par zone
            $table->unique(['zone_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
