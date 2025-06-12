<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->enum('service_status', [
                'Não Iniciado',
                'Em Andamento',
                'Pausado',
                'Finalizado',
                'Cancelado'
            ])->default('Não Iniciado')->after('observations'); // Você pode ajustar 'after' para a posição desejada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('service_status');
        });
    }
};
