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
            $table->string('title')->after('id');
            $table->integer('validity')->default(20)->after('title');
            $table->string('client_cpf_cnpj')->nullable()->after('client_email');
            $table->string('client_cep')->nullable()->after('client_address');
            // Remove default for TEXT column
            $table->text('contracted_responsibility')->after('description');
            $table->text('contractor_responsibility')->nullable()->after('contracted_responsibility');
            // Remove default for TEXT column
            $table->text('payment_method_description')->after('contractor_responsibility');
            // Remove default for TEXT column
            $table->text('bank_info_description')->after('payment_method_description');
            $table->enum('budget_type', ['Original', 'Correção'])->default('Original')->after('status');
            $table->unsignedBigInteger('original_budget_id')->nullable()->after('budget_type');
            $table->foreign('original_budget_id')->references('id')->on('budgets')->onDelete('set null');
            $table->integer('deadline')->default(20)->after('bank_info_description');
            // Remove default for TEXT column
            $table->string('deadline_start_description')->after('deadline');
            $table->enum('deadline_type', ['dias úteis', 'dias corridos'])->default('dias úteis')->after('deadline_start_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropForeign(['original_budget_id']);
            
            $table->dropColumn('title');
            $table->dropColumn('validity');

            $table->dropColumn('client_cpf_cnpj');
            $table->dropColumn('client_cep');
            
            $table->dropColumn('contractor_responsibility');
            $table->dropColumn('contracted_responsibility');

            $table->dropColumn('payment_method_description');
            $table->dropColumn('bank_info_description');

            $table->dropColumn('budget_type');
            $table->dropColumn('original_budget_id');

            $table->dropColumn('deadline');
            $table->dropColumn('deadline_start_description');
            $table->dropColumn('deadline_type');
        });
    }
};