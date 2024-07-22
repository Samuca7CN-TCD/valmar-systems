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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->binary('icon')->nullable();
            $table->string('name')->unique();
            $table->string('name_plural')->unique();
            $table->string('abbreviation')->unique();
            $table->string('abbreviation_plural')->unique();
            $table->float('quantity');
            $table->unsignedBigInteger('measurement_unit_id');
            $table->double('total_value');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('measurement_unit_id')->references('id')->on('measurement_units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
