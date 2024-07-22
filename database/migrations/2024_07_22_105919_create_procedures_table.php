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
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('previous_id')->nullable();
            $table->boolean('remaked')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('action_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('movement_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('previous_id')->references('id')->on('procedures');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('action_id')->references('id')->on('actions');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('movement_id')->references('id')->on('movements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
