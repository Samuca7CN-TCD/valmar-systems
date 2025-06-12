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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade'); // Chave estrangeira para o orçamento
            $table->string('item_name'); // Nome do item (ex: "Desenvolvimento de Website", "Instalação Elétrica")
            $table->text('item_description')->nullable(); // Descrição detalhada do item
            $table->integer('quantity')->default(1); // Quantidade do item
            $table->decimal('unit_price', 10, 2)->default(0.00); // Preço unitário do item
            $table->decimal('subtotal', 10, 2)->default(0.00); // subtotal = quantity * unit_price

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
