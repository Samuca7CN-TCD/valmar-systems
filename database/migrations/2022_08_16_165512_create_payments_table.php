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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string("debt");
            $table->string("debtor");
            $table->double("total_amount");
            $table->double("partial_amount");
            $table->double("down_payment");
            $table->unsignedBigInteger("material_sale_id");
            $table->unsignedBigInteger("service_id");
            $table->unsignedBigInteger("payslip_id");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("material_sale_id")->references("id")->on("material_sales");
            $table->foreign("service_id")->references("id")->on("services");
            $table->foreign("payslip_id")->references("id")->on("payslips");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
