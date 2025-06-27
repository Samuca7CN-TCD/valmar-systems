<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Budget;
use App\Models\Movement;
use Illuminate\Support\Facades\DB;

class MigrateLegacyClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra os nomes de clientes legados das tabelas de orçamentos e movimentos para a nova tabela de clientes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando a migração de clientes legados...');

        DB::transaction(function () {
            // Fase 1: Migrar clientes da tabela de Orçamentos (budgets)
            $this->info('Processando clientes da tabela de orçamentos...');

            $legacyBudgets = Budget::whereNull('client_id')
                ->whereNotNull('client_name')
                ->where('client_name', '!=', '')
                ->select(
                    'client_name',
                    'client_email',
                    'client_phone',
                    'client_address',
                    'client_cpf_cnpj',
                    'client_cep'
                )
                ->distinct('client_name')
                ->get();

            foreach ($legacyBudgets as $legacy) {
                $client = null;

                // Etapa 1: Tentar encontrar o cliente pelo documento (maior prioridade)
                if (!empty($legacy->client_cpf_cnpj)) {
                    $client = Client::where('document', $legacy->client_cpf_cnpj)->first();
                }

                // Etapa 2: Se não encontrado, tentar pelo e-mail (segunda prioridade)
                if (!$client && !empty($legacy->client_email)) {
                    $client = Client::where('email', $legacy->client_email)->first();
                }

                // Etapa 3: Preparar os dados do orçamento atual
                $attributesFromBudget = array_filter([
                    'name' => $legacy->client_name,
                    'email' => $legacy->client_email,
                    'contacts' => $legacy->client_phone ? [$legacy->client_phone] : null,
                    'postal_code' => $legacy->client_cep,
                    'address_street' => $legacy->client_address,
                    'type' => strlen(preg_replace('/[^0-9]/', '', $legacy->client_cpf_cnpj)) > 11 ? 'juridica' : 'fisica',
                    'document' => !empty($legacy->client_cpf_cnpj) ? $legacy->client_cpf_cnpj : null,
                ], fn($value) => $value !== null);

                // Etapa 4: Atualizar cliente existente ou criar um novo
                if ($client) {
                    // Cliente encontrado. Atualiza com as novas informações, se houver.
                    $client->fill($attributesFromBudget);
                    if ($client->isDirty()) {
                        $client->save();
                        $this->line("Cliente '{$client->name}' [ID: {$client->id}] encontrado e atualizado.");
                    } else {
                         $this->line("Cliente '{$client->name}' [ID: {$client->id}] encontrado, sem necessidade de atualização.");
                    }
                } else {
                    // Cliente não encontrado. Cria um novo.
                    if (empty($attributesFromBudget['document'])) {
                        $attributesFromBudget['document'] = 'TEMP-' . uniqid();
                    }
                    $client = Client::create($attributesFromBudget);
                    $this->line("Cliente '{$client->name}' [ID: {$client->id}] criado.");
                }

                // Etapa 5: Vincular o orçamento ao cliente
                Budget::where('client_name', $legacy->client_name)->update(['client_id' => $client->id]);
            }

            // Fase 2: Migrar clientes da tabela de Movimentos (movements)
            $this->info("\nProcessando clientes da tabela de movimentos...");
            $legacyMovements = Movement::whereNull('client_id')
                ->whereNotNull('entity_name')
                ->where('entity_name', '!=', '')
                ->select('entity_name')
                ->distinct()
                ->get();

            foreach ($legacyMovements as $legacy) {
                // Tenta encontrar o cliente pelo nome. Se não existir, cria um com dados mínimos.
                $client = Client::firstOrCreate(
                    ['name' => $legacy->entity_name],
                    ['document' => 'TEMP-' . uniqid(), 'type' => 'fisica']
                );

                Movement::where('entity_name', $legacy->entity_name)->update(['client_id' => $client->id]);
                $this->line("Cliente '{$client->name}' processado a partir dos movimentos.");
            }
        });

        $this->info("\nMigração concluída com sucesso!");
        $this->warn("Atenção: Clientes com documento 'TEMP-...' devem ser revisados e atualizados manualmente.");

        return Command::SUCCESS;
    }
}
