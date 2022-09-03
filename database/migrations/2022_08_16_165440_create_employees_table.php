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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("surname");
            $table->double("salary");
            $table->double("additional");
            $table->string("phone_number");
            $table->unsignedBigInteger("payment_method_id");
            $table->string("pix");
            $table->string("branch");
            $table->string("account");
            $table->string("function");
            $table->boolean("transportation_vouchers");
            $table->boolean("situation");   
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("payment_method_id")->references("id")->on("payment_methods");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
