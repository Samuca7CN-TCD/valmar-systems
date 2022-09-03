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
        Schema::create('material_uses', function (Blueprint $table) {
            $table->id();
            $table->float("reason");
            $table->unsignedBigInteger("employee_id");
            $table->unsignedBigInteger("service_id");
            $table->string("service_description");
            $table->date("use_date");    
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("employee_id")->references("id")->on("employees");
            $table->foreign("service_id")->references("id")->on("services");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_uses');
    }
};
