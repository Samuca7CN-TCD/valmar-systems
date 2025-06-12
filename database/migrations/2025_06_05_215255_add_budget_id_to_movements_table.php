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
            // Adiciona a coluna para armazenar o ID do orçamento que gerou o serviço
            // Onde 'type' = 1 (serviço)
            $table->foreignId('budget_id')
                  ->nullable() // Pode ser nulo, pois nem todo serviço vem de um orçamento
                  ->constrained('budgets') // Referencia a tabela 'budgets'
                  ->onDelete('set null') // Se o orçamento for excluído, define budget_id como NULL
                  ->after('previous_id'); // Ajuste a posição conforme desejar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('budget_id'); // Remove a chave estrangeira e a coluna
        });
    }
};
