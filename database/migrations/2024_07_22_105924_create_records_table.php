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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('name')->nullable();
            $table->float('quantity')->nullable();
            $table->string('measurement_unit')->nullable();
            $table->float('price')->nullable();
            $table->float('movement_quantity')->nullable();
            $table->float('amount')->nullable();
            $table->string('filepath')->nullable();
            $table->boolean('past')->default(true);
            $table->date('register_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('procedure_id')->references('id')->on('procedures');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
