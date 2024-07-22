<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('actions')->insert([
            ['type' => 'create', 'name' => 'criar', 'past' => 'criou', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'update', 'name' => 'atualizar', 'past' => 'atualizou', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'delete', 'name' => 'deletar', 'past' => 'deletou', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'pay', 'name' => 'pagar', 'past' => 'pagou', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'conclude', 'name' => 'concluir', 'past' => 'concluiu', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'fire', 'name' => 'demitir', 'past' => 'demitiu', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'print', 'name' => 'imprimir', 'past' => 'imprimiu', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'refund', 'name' => 'reembolsar', 'past' => 'reembolsou', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'devolution', 'name' => 'devolver', 'past' => 'devolveu', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
