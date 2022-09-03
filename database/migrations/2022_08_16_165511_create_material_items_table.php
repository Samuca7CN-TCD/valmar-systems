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
        Schema::create('material_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("material_category_id");
            $table->double("unit_price");
            $table->float("percentage");
            $table->date("material_entrance")->nullable();
            $table->date("material_use")->nullable();
            $table->date("material_sale")->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("material_category_id")->references("id")->on("material_categories");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_items');
    }
};
