<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['fisica', 'juridica'])->default('fisica'); // Tipo de cliente
            
            // Campos para Pessoa Física e Jurídica
            $table->string('name'); // Nome (PF) ou Razão Social (PJ)
            $table->string('trade_name')->nullable(); // Nome Fantasia (PJ)
            $table->string('document')->unique(); // CPF ou CNPJ
            $table->string('email')->nullable()->unique();
            $table->json('contacts')->nullable(); // Para múltiplos telefones/contatos

            // Endereço Estruturado
            $table->string('postal_code', 9)->nullable(); // CEP
            $table->string('address_street')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state', 2)->nullable();
            
            $table->text('observations')->nullable(); // Campo para observações gerais

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
