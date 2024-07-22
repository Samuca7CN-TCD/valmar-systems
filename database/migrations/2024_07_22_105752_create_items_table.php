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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('profile_img')->unique()->nullable();
            $table->string('name')->unique();
            $table->unsignedBigInteger('category_id');
            $table->double('price')->default(0);
            $table->float('quantity')->default(0);
            $table->float('min_quantity')->nullable();
            $table->float('max_quantity')->nullable();
            $table->unsignedBigInteger('measurement_unit_id');
            $table->float('unit_equivalent')->default(1);
            $table->boolean('list_in_uses')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('measurement_unit_id')->references('id')->on('measurement_units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
