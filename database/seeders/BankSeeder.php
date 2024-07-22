<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('banks')->insert([
            ['code' => 341, 'name' => 'Banco Itaú', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 237, 'name' => 'Banco Bradesco', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 104, 'name' => 'Caixa Econômica Federal', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 001, 'name' => 'Banco do Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 033, 'name' => 'Banco Santander', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 260, 'name' => 'Nubank', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 077, 'name' => 'Banco Inter', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 655, 'name' => 'Banco Votorantim', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 336, 'name' => 'C6 Bank', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 208, 'name' => 'BTG Pactual', 'created_at' => $now, 'updated_at' => $now]
        ]);
    }
}
