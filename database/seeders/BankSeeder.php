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
            ['code' => 1, 'name' => 'Banco do Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 2, 'name' => 'Banco Central do Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 3, 'name' => 'Banco da Amazônia', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 4, 'name' => 'Banco do Nordeste', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 7, 'name' => 'Banco Nacional de Desenvolvimento Econômico e Social (BNDES)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 25, 'name' => 'Banco Alfa', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 33, 'name' => 'Banco Santander', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 37, 'name' => 'Banco do Estado do Pará (Banpará)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 41, 'name' => 'Banrisul', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 47, 'name' => 'Banco do Estado de Sergipe (Banese)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 70, 'name' => 'Banco de Brasília (BRB)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 104, 'name' => 'Caixa Econômica Federal', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 121, 'name' => 'Agibank', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 184, 'name' => 'Banco Itaú BBA', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 208, 'name' => 'Banco BTG Pactual', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 212, 'name' => 'Banco Original', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 218, 'name' => 'Banco BS2', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 224, 'name' => 'Banco de Lage Landen Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 237, 'name' => 'Banco Bradesco (ou Next)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 260, 'name' => 'Nubank', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 290, 'name' => 'PagSeguro Internet', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 318, 'name' => 'Banco BMG', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 336, 'name' => 'C6 Bank', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 341, 'name' => 'Banco Itaú', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 366, 'name' => 'Banco Société Générale Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 376, 'name' => 'Banco J.P. Morgan', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 389, 'name' => 'Banco Mercantil do Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 422, 'name' => 'Banco Safra', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 456, 'name' => 'Banco MUFG Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 477, 'name' => 'Citibank N.A.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 505, 'name' => 'Banco Credit Suisse (Brasil)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 623, 'name' => 'Banco Pan', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 634, 'name' => 'Banco Mercantil de Investimentos', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 655, 'name' => 'Banco Votorantim', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 735, 'name' => 'Neon Pagamentos', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 746, 'name' => 'ModalMais Banco', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 752, 'name' => 'Banco BNP Paribas Brasil', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 756, 'name' => 'Banco Cooperativo do Brasil (Bancoob)', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 748, 'name' => 'Banco Cooperativo Sicredi', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 66, 'name' => 'Banco Morgan Stanley', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 79, 'name' => 'Banco Original do Agronegócio', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 380, 'name' => 'PicPay', 'created_at' => $now, 'updated_at' => $now],
        ]);

    }
}
