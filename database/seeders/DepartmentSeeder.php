<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('departments')->insert([
            ['type' => 'dashboard', 'name' => 'Dashboard', 'name_plural' => 'Dashboards', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'warehouse', 'name' => 'Almoxarifado', 'name_plural' => 'Almoxarifados', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'movements', 'name' => 'Entrada', 'name_plural' => 'Entradas', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'movements', 'name' => 'Uso', 'name_plural' => 'Usos', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'movements', 'name' => 'Venda', 'name_plural' => 'Vendas', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'report', 'name' => 'Relatório', 'name_plural' => 'Relatórios', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'payment', 'name' => 'Pagamento', 'name_plural' => 'Pagamentos', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'service', 'name' => 'Serviço', 'name_plural' => 'Serviços', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'employee', 'name' => 'Funcionário', 'name_plural' => 'Funcionários', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'employee', 'name' => 'Controle', 'name_plural' => 'Controles', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'employee', 'name' => 'Tabela de Horas Extras', 'name_plural' => 'Tabelas de Horas Extras', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'payslip', 'name' => 'Holerite', 'name_plural' => 'Holerites', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'pendence', 'name' => 'Pendência', 'name_plural' => 'Pendências', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'config', 'name' => 'Configuração', 'name_plural' => 'Configurações', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
