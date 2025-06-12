<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id(); // ID do orçamento

            // Informações do cliente (se o cliente não tiver um modelo próprio, pode ser string)
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_address')->nullable();

            // Campos relacionados ao orçamento
            $table->text('description')->nullable(); // Descrição geral do orçamento
            $table->decimal('total_value', 10, 2)->default(0.00); // Valor total do orçamento
            $table->date('budget_date')->default(Carbon::now()->format('Y-m-d')); // Data de criação do orçamento

            $table->enum('status', [ // Status do orçamento
                'Rascunho', // Ou 'Criado'
                'Enviado',
                'Aprovado',
                'Rejeitado',
                'Cancelado'
            ])->default('Rascunho');

            $table->date('approval_rejection_date')->nullable(); // Data de aprovação ou rejeição
            $table->text('rejection_reason')->nullable(); // Motivo da rejeição (se aplicável)

            // Referência ao serviço gerado (se houver) - Será preenchido quando um serviço for criado a partir deste orçamento
            // Este campo será adicionado na migração de alteração de budgets abaixo para manter a consistência do plano.
            // $table->unsignedBigInteger('generated_service_id')->nullable();
            // $table->foreign('generated_service_id')->references('id')->on('movements');

            $table->softDeletes(); // Para exclusão lógica de orçamentos
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
