<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->unsignedBigInteger("measurement_unit_id");
            $table->float("quantity");
            $table->float("min")->nullable();
            $table->float("max")->nullable();
            $table->float("total_price");
            $table->boolean("list_in_uses_table");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('measurement_unit_id')->references('id')->on('measurement_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_categories');
    }
};
