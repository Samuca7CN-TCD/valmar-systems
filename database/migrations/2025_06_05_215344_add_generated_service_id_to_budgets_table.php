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
        Schema::table('budgets', function (Blueprint $table) {
            // Adiciona a coluna para armazenar o ID do serviço gerado a partir deste orçamento
            $table->foreignId('generated_service_id')
                  ->nullable() // Pode ser nulo, pois nem todo orçamento gera um serviço imediatamente
                  ->constrained('movements') // Referencia a tabela 'movements'
                  ->onDelete('set null') // Se o serviço for excluído, define generated_service_id como NULL
                  ->after('rejection_reason'); // Ajuste a posição
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('generated_service_id');
        });
    }
};
