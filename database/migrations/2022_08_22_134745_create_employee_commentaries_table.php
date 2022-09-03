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
        Schema::create('employee_commentaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("employee_id");
            $table->text("commentary")->nullable();
            $table->date("date");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("employee_id")->references("id")->on("employees");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_commentaries');
    }
};
