<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Movement; // Importe o modelo Movement

class UpdateMovementStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando a migração de service_status para movimentos antigos...');

        // É crucial incluir registros soft-deleted para as queries que os visam
        // e também para os que visam os não soft-deleted corretamente.

        // Passo 1: Marcar serviços 'Finalizados'
        // Pegamos apenas os movimentos do tipo 'serviço' que estão 'ready = true'
        // e que AINDA NÃO TÊM um status (ou seja, são registros antigos não processados).
        $countFinalizado = Movement::query()
            ->whereNull('service_status') // Apenas os que não foram processados ainda
            ->where('type', 1)
            ->where('ready', true)
            ->update(['service_status' => 'Finalizado']);

        $this->command->info("Foram marcados {$countFinalizado} movimentos como 'Finalizado'.");

        // Passo 2: Marcar serviços 'Cancelados' (aqueles que estão soft-deleted)
        // Precisamos usar 'withTrashed()' para incluir os registros soft-deleted na query.
        // E garantimos que eles sejam do tipo 'serviço' e que ainda não foram processados
        // ou foram marcados com outro status que deve ser sobrescrito por 'Cancelado' se estiver soft-deleted.
        $countCancelado = Movement::query()
            ->withTrashed() // Inclui registros soft-deleted
            ->where('type', 1)
            ->whereNotNull('deleted_at') // Especificamente os que foram soft-deleted
            ->update(['service_status' => 'Cancelado']); // Sobrescreve qualquer status anterior se estiver soft-deleted

        $this->command->info("Foram marcados {$countCancelado} movimentos como 'Cancelado'.");

        // Passo 3: Marcar o restante como 'Em Andamento'
        // Pegamos movimentos do tipo 'serviço' que não estão 'ready = true' (já que foram tratados no Passo 1)
        // E que NÃO ESTÃO soft-deleted (já que foram tratados no Passo 2).
        // Também garantimos que eles AINDA NÃO TÊM um status.
        $countEmAndamento = Movement::query()
            ->whereNull('service_status') // Apenas os que não foram processados ainda (e não são finalizados/cancelados)
            ->where('type', 1)
            // Não precisamos verificar 'ready = false' aqui, pois se 'ready = true' já teriam sido 'Finalizado' no Passo 1.
            // A ausência de 'withTrashed()' automaticamente exclui os soft-deleted.
            ->update(['service_status' => 'Em Andamento']);

        $this->command->info("Foram marcados {$countEmAndamento} movimentos como 'Em Andamento'.");

        $this->command->info('Migração de service_status para movimentos antigos finalizada.');
    }
}