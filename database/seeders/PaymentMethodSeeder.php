<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('payment_methods')->insert([
            [
                'name' => 'TED',
                'cod' => 'ted',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Conta Salário',
                'cod' => 'salary_account',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'PIX (E-mail)',
                'cod' => 'pix_email',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'PIX (CPF)',
                'cod' => 'pix_cpf',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'PIX (Celular)',
                'cod' => 'pix_phone_number',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'PIX (Dados Bacários)',
                'cod' => 'pix_bank_data',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'PIX (Token)',
                'cod' => 'pix_token',
                'created_at' => $now,
                'updated_at' => $now
            ]    
        ]);   
    }
}
