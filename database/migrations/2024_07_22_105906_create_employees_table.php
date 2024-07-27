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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->double('salary')->nullable();
            $table->double('agreement')->nullable();
            $table->json('contacts');
            $table->string('function_name');
            $table->boolean('transportation_voucher');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('overtime_payment_method_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('pix_cpf')->unique()->nullable();
            $table->string('pix_email')->unique()->nullable();
            $table->string('pix_phone_number')->unique()->nullable();
            $table->string('pix_token')->unique()->nullable();
            $table->string('bank_ag')->unique()->nullable();
            $table->unsignedBigInteger('account_type_id')->nullable();
            $table->string('account_number')->unique()->nullable();
            $table->boolean('fired')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('overtime_payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('bank_id')->references('id')->on('banks');
            $table->foreign('account_type_id')->references('id')->on('account_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
