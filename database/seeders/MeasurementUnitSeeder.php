<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeasurementUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('measurement_units')->insert([
            [
                'name' => 'unidade',
                'name_plural' => 'unidades',
                'abbreviation' => 'und',
                'abbreviation_plural' => 'unds',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'par',
                'name_plural' => 'pares',
                'abbreviation' => 'pr',
                'abbreviation_plural' => 'prs',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'metro',
                'name_plural' => 'metros',
                'abbreviation' => 'mt',
                'abbreviation_plural' => 'mts',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'metro quadrado',
                'name_plural' => 'metros quadrados',
                'abbreviation' => 'mt²',
                'abbreviation_plural' => 'mts²',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'metro cúbico',
                'name_plural' => 'metros cúbicos',
                'abbreviation' => 'mt³',
                'abbreviation_plural' => 'mts³',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'quilo',
                'name_plural' => 'quilos',
                'abbreviation' => 'kg',
                'abbreviation_plural' => 'kgs',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'pacote',
                'name_plural' => 'pacotes',
                'abbreviation' => 'pct',
                'abbreviation_plural' => 'pcts',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
