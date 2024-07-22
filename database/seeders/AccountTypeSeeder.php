<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('account_types')->insert([
            ['name' => 'Conta Corrente', 'cod' => 'current_account', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Conta Poupança', 'cod' => 'savings_account', 'created_at' => $now, 'updated_at' => $now]
        ]);
    }
}
