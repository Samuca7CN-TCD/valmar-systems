<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('previous_id')->nullable();
            $table->smallInteger('type')->default(0);
            $table->boolean('remaked')->default(false);
            $table->boolean('ready')->default(false);
            $table->unsignedBigInteger('accounting_id');
            $table->string('motive');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->date('deadline')->nullable();
            $table->date('date')->default(Carbon::now()->format('Y-m-d'));
            $table->text('observations')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('previous_id')->references('id')->on('movements');
            $table->foreign('accounting_id')->references('id')->on('accountings');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
