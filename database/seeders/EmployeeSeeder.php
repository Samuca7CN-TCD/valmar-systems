<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('employees')->insert([
            [
                'name' => 'Valdinei',
                'surname' => 'dos Santos Nascimento',
                'salary' => null,
                'agreement' => 0,
                'contacts' => json_encode(array('+5573988559571', '+5573988121072')),
                'function_name' => 'CEO',
                'transportation voucher' => false,
                'payment_method_id' => null,
                'bank_id' => null,
                'pix_cpf' => null,
                'pix_email' => null,
                'pix_phone_number' => null,
                'pix_token' => null,
                'bank_ag' => null,
                'account_type_id' => null,
                'account_number' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Samuel',
                'surname' => 'Correia Nascimento',
                'salary' => 2000,
                'agreement' => 0,
                'contacts' => json_encode(array('+5573988121518')),
                'function_name' => 'Técnico em Informática',
                'transportation voucher' => false,
                'payment_method_id' => 2,
                'bank_id' => 4,
                'pix_cpf' => '06489734508',
                'pix_email' => 'samuca.7cn@gmail.com',
                'pix_phone_number' => '73988121518',
                'pix_token' => null,
                'bank_ag' => '00701',
                'account_type_id' => 1,
                'account_number' => '689785',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
