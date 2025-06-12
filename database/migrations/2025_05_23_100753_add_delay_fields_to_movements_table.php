<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->boolean('delayed')->default(false)->after('ready');
            $table->string('delay_reason')->nullable()->after('delayed');
            $table->date('completion_date')->nullable()->after('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('delayed');
            $table->dropColumn('delay_reason');
            $table->dropColumn('completion_date');
        });
    }
};
