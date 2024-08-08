<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('categories')->insert([
            [
                'icon' => Storage::get('public/img/categories/barras_chatas.png'),
                'name' => 'Barra Chata',
                'name_plural' => 'Barras Chatas',
                'abbreviation' => 'bar_chat',
                'abbreviation_plural' => 'bars_chats',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/canoplas.png'),
                'name' => 'Canopla',
                'name_plural' => 'Canoplas',
                'abbreviation' => 'canop',
                'abbreviation_plural' => 'canops',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/cantoneiras.png'),
                'name' => 'Cantoneira',
                'name_plural' => 'Cantoneiras',
                'abbreviation' => 'cant',
                'abbreviation_plural' => 'cants',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/chapas.png'),
                'name' => 'Chapa',
                'name_plural' => 'Chapas',
                'abbreviation' => 'chap',
                'abbreviation_plural' => 'chaps',
                'quantity' => 0,
                'measurement_unit_id' => 4,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/chapeus_chineses.png'),
                'name' => 'Chapéu Chinês',
                'name_plural' => 'Chapéus Chineses',
                'abbreviation' => 'chap_chin',
                'abbreviation_plural' => 'chaps_chins',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/curvas.png'),
                'name' => 'Curva',
                'name_plural' => 'Curvas',
                'abbreviation' => 'curv',
                'abbreviation_plural' => 'curvs',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/diversos.png'),
                'name' => 'Diverso',
                'name_plural' => 'Diversos',
                'abbreviation' => 'dirv',
                'abbreviation_plural' => 'dirvs',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/dutos.png'),
                'name' => 'Duto',
                'name_plural' => 'Dutos',
                'abbreviation' => 'dut',
                'abbreviation_plural' => 'duts',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/metalons.png'),
                'name' => 'Metalon',
                'name_plural' => 'Metalons',
                'abbreviation' => 'met',
                'abbreviation_plural' => 'mets',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/perfis_u.png'),
                'name' => 'Profile U',
                'name_plural' => 'Perfis U',
                'abbreviation' => 'perf_u',
                'abbreviation_plural' => 'perfs_u',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/triclamps.png'),
                'name' => 'Triclamp',
                'name_plural' => 'Triclamps',
                'abbreviation' => 'tric',
                'abbreviation_plural' => 'trics',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/torres.png'),
                'name' => 'Torre',
                'name_plural' => 'Torres',
                'abbreviation' => 'tor',
                'abbreviation_plural' => 'tors',
                'quantity' => 0,
                'measurement_unit_id' => 1,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/tubos.png'),
                'name' => 'Tubo',
                'name_plural' => 'Tubos',
                'abbreviation' => 'tub',
                'abbreviation_plural' => 'tubs',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/vergalhoes_redondos.png'),
                'name' => 'Vergalhão Redondo',
                'name_plural' => 'Vergalhões Redondos',
                'abbreviation' => 'verg_redon',
                'abbreviation_plural' => 'vergs_redons',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'icon' => Storage::get('public/img/categories/valvulas.png'),
                'name' => 'Válvula',
                'name_plural' => 'Vávulas',
                'abbreviation' => 'valv',
                'abbreviation_plural' => 'valvs',
                'quantity' => 0,
                'measurement_unit_id' => 3,
                'total_value' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }
}
