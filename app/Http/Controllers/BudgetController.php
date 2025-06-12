<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Models\Department;
use App\Models\Accounting;
use App\Models\Movement;
use App\Models\Procedure;
use App\Models\Record;

use App\Models\Budget;
use App\Models\BudgetItem;

use Dompdf\Dompdf;
use Dompdf\Options;

use Illuminate\Database\Eloquent\Builder;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 25;
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $clientFilter = $request->input('client');

        // Incluir generatedService e originalBudget, se desejar exibi-los na listagem
        $budgetsQuery = Budget::with('items', 'generatedService', 'originalBudget', 'procedures.user')
            ->orderByDesc('budget_date');

        if ($search) {
            $budgetsQuery->where(function ($query) use ($search) {
                $query->where('client_name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('total_value', 'like', '%' . $search . '%')
                      ->orWhere('client_cpf_cnpj', 'like', '%' . $search . '%') // Novo campo na busca
                      ->orWhere('client_cep', 'like', '%' . $search . '%') // Novo campo na busca
                      ->orWhere('payment_method_description', 'like', '%' . $search . '%') // Novo campo na busca
                      ->orWhere('bank_info_description', 'like', '%' . $search . '%') // Novo campo na busca
                      ->orWhereHas('items', function ($q) use ($search) { // Buscar nos itens
                          $q->where('item_name', 'like', '%' . $search . '%')
                            ->orWhere('item_description', 'like', '%' . $search . '%');
                      });
            });
        }

        if ($statusFilter && in_array($statusFilter, ['Rascunho', 'Enviado', 'Aprovado', 'Rejeitado', 'Cancelado'])) {
            $budgetsQuery->where('status', $statusFilter);
        }

        if ($clientFilter) {
            $budgetsQuery->where('client_name', 'like', '%' . $clientFilter . '%');
        }

        $budgets = $budgetsQuery->withTrashed()->orderByDesc('id')->paginate($perPage);

        return Inertia::render('Budgets/Index', [
            'budgets_list' => $budgets,
            'filters' => $request->only(['search', 'status', 'client']),
            'page' => Department::find(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
     public function create(Request $request)
    {
        $originalBudget = null;
        $originalBudgetItems = [];

        // Verifica se há um orçamento para duplicar
        if ($request->has('duplicate_from')) {
            $originalBudget = Budget::with('items')->find($request->input('duplicate_from'));

            if ($originalBudget) {
                // Prepara os itens do orçamento original para o frontend
                $originalBudgetItems = $originalBudget->items->map(function ($item) {
                    return [
                        'id' => $item->id, // Pode ser útil se você precisar de um identificador único no front, embora não seja salvo para novos itens
                        'item_name' => $item->item_name,
                        'item_description' => $item->item_description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ];
                })->toArray();
            }
        }

        return Inertia::render('Budgets/Create', [
            'page' => Department::find(15),
            'originalBudget' => $originalBudget, // Passa o orçamento original se existir
            'originalBudgetItems' => $originalBudgetItems, // Passa os itens do orçamento original
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Autorização: Ex: $this->authorize('create', Budget::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'validity' => ['required', 'numeric'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['required', 'string', 'max:20'],
            'client_address' => ['nullable', 'string', 'max:255'],
            'client_cpf_cnpj' => ['required', 'string', 'max:20'], // NOVO CAMPO
            'client_cep' => ['required', 'string', 'max:10'],      // NOVO CAMPO
            'description' => ['nullable', 'string'],
            'budget_date' => ['required', 'date_format:Y-m-d'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'], // NOVO CAMPO
            'additional_amount' => ['nullable', 'numeric', 'min:0'], // NOVO CAMPO
            'contracted_responsibility' => ['nullable', 'string'], // NOVO CAMPO
            'contractor_responsibility' => ['nullable', 'string'], // NOVO CAMPO
            'deadline' => ['required', 'integer', 'min:0'], // Assumindo que deadline é o número de dias
            'deadline_start_description' => ['nullable', 'string', 'max:255'], // NOVO CAMPO
            'deadline_type' => ['required', 'in:dias úteis,dias corridos'], // NOVO CAMPO
            'payment_method_description' => ['nullable', 'string'], // NOVO CAMPO
            'bank_info_description' => ['nullable', 'string'],      // NOVO CAMPO
            'budget_type' => ['required', 'in:Original,Correção'],  // NOVO CAMPO
            'original_budget_id' => ['nullable', 'exists:budgets,id'], // NOVO CAMPO, se for correção
        ]);

        DB::transaction(function () use ($validated, $request) {
            $totalValue = 0;
            foreach ($validated['items'] as $item) {
                $totalValue += $item['quantity'] * $item['unit_price'];
            }

            // Aplicar desconto e acréscimo no total_value ANTES de criar o orçamento
            $finalTotalValue = $totalValue - ($validated['discount_amount'] ?? 0) + ($validated['additional_amount'] ?? 0);
            if ($finalTotalValue < 0) $finalTotalValue = 0; // Garantir que não seja negativo

            $budget = Budget::create([
                'title' => $validated['title'],
                'validity' => $validated['validity'],
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'client_phone' => $validated['client_phone'],
                'client_address' => $validated['client_address'],
                'client_cpf_cnpj' => $validated['client_cpf_cnpj'], // NOVO CAMPO
                'client_cep' => $validated['client_cep'],          // NOVO CAMPO
                'description' => $validated['description'],
                'budget_date' => $validated['budget_date'],
                'total_value' => $finalTotalValue, // Total já com descontos/acréscimos
                'discount_amount' => $validated['discount_amount'] ?? 0, // NOVO CAMPO
                'additional_amount' => $validated['additional_amount'] ?? 0, // NOVO CAMPO
                'deadline' => $validated['deadline'], // Campo já existente na migração de orçamentos, mas sem validação
                'contracted_responsibility' => $validated['contracted_responsibility'] ?? 'Fornecer todo material de fabricação e consumo', // NOVO CAMPO
                'contractor_responsibility' => $validated['contractor_responsibility'] ?? null, // NOVO CAMPO
                'deadline_start_description' => $validated['deadline_start_description'] ?? 'a partir do pagamento de entrada', // NOVO CAMPO
                'deadline_type' => $validated['deadline_type'], // NOVO CAMPO
                'payment_method_description' => $validated['payment_method_description'] ?? '50% entrada' . PHP_EOL . '50% no ato da entrega do serviço', // NOVO CAMPO
                'bank_info_description' => $validated['bank_info_description'] ?? 'BANCO DO BRASIL' . PHP_EOL . 'AG: 3175-5 | C/C: 20.439-0' . PHP_EOL . 'PIX: (73) 9 8855-9571', // NOVO CAMPO
                'budget_type' => $validated['budget_type'], // NOVO CAMPO
                'original_budget_id' => $validated['original_budget_id'] ?? null, // NOVO CAMPO
                'status' => 'Rascunho', // Status inicial sempre 'Rascunho'
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 15,
                'budget_id' => $budget->id,
            ]);

            foreach ($validated['items'] as $itemData) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['item_description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            // Opcional: Registrar uma Procedure para a criação do orçamento
            // Procedure::create([...]);
        });

        return redirect()->route('budgets.index')->with('success', 'Orçamento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budget = Budget::with([
            'items',
            'generatedService',
            'originalBudget', // Carrega o orçamento pai, se existir
            'procedures.user',
        ])->withTrashed()->findOrFail($id);

        $rootOfChain = $budget;
        
        while ($rootOfChain->original_budget_id) {
            $rootOfChain = Budget::withTrashed()->findOrFail($rootOfChain->original_budget_id);
        }

        $allChainBudgets = $this->collectAllBudgetsInChain($rootOfChain);
   
        return Inertia::render('Budgets/Show', [
            'budget' => $budget, // O orçamento principal já vem com os relacionamentos necessários
            'page' => Department::find(15),
            'budgetChain' => $allChainBudgets, // Passa a cadeia COMPLETA para o frontend
            'currentBudgetId' => $budget->id,
        ]);
    }

    /**
     * Helper recursivo para coletar todos os orçamentos em uma cadeia,
     * partindo de um orçamento pai e incluindo todos os seus descendentes.
     */
    private function collectAllBudgetsInChain($currentBudget)
    {
        $collection = collect();

        $collection->push($currentBudget);
        
        $currentBudget = $currentBudget->correctionBudget()->withTrashed()->first();
        
        while($currentBudget){
            $collection->push($currentBudget);
            $currentBudget = $currentBudget->correctionBudget()->withTrashed()->first();
        }

        return $collection;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Ao carregar para edição, inclua todos os dados do orçamento
        $budget = Budget::with('items')->findOrFail($id);

        if (!in_array($budget->status, ['Rascunho', 'Enviado']) || $budget->generated_service_id !== null) {
            return redirect()->route('budgets.show', $budget->id)
                             ->with('error', 'Este orçamento não pode ser editado. Considere duplicá-lo para uma correção.');
        }

        return Inertia::render('Budgets/Edit', [
            'budget' => $budget,
            'page' => Department::find(15),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $budget = Budget::findOrFail($id);
        // Autorização: Ex: $this->authorize('update', $budget);

        if (!in_array($budget->status, ['Rascunho', 'Enviado']) || $budget->generated_service_id !== null) {
            return redirect()->route('budgets.show', $budget->id)
                             ->with('error', 'Este orçamento não pode ser atualizado.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'validity' => ['required', 'numeric'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['required', 'string', 'max:20'],
            'client_address' => ['nullable', 'string', 'max:255'],
            'client_cpf_cnpj' => ['required', 'string', 'max:20'], // NOVO CAMPO
            'client_cep' => ['required', 'string', 'max:10'],      // NOVO CAMPO
            'description' => ['nullable', 'string'],
            'budget_date' => ['required', 'date_format:Y-m-d'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'], // NOVO CAMPO
            'additional_amount' => ['nullable', 'numeric', 'min:0'], // NOVO CAMPO
            'contracted_responsibility' => ['nullable', 'string'], // NOVO CAMPO
            'contractor_responsibility' => ['nullable', 'string'], // NOVO CAMPO
            'deadline' => ['required', 'integer', 'min:0'], // Assumindo que deadline é o número de dias
            'deadline_start_description' => ['nullable', 'string', 'max:255'], // NOVO CAMPO
            'deadline_type' => ['required', 'in:dias úteis,dias corridos'], // NOVO CAMPO
            'payment_method_description' => ['nullable', 'string'], // NOVO CAMPO
            'bank_info_description' => ['nullable', 'string'],      // NOVO CAMPO
            'budget_type' => ['required', 'in:Original,Correção'],  // NOVO CAMPO
            'original_budget_id' => ['nullable', 'exists:budgets,id'], // NOVO CAMPO, se for correção
        ]);

        DB::transaction(function () use ($validated, $budget) {
            $totalValue = 0;
            $existingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $totalValue += $itemData['quantity'] * $itemData['unit_price'];

                if (isset($itemData['id'])) {
                    $item = BudgetItem::findOrFail($itemData['id']);
                    $item->update([
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['item_description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);
                    $existingItemIds[] = $item->id;
                } else {
                    $newItem = BudgetItem::create([
                        'budget_id' => $budget->id,
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['item_description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);
                    $existingItemIds[] = $newItem->id;
                }
            }

            $budget->items()->whereNotIn('id', $existingItemIds)->delete();

            // Aplicar desconto e acréscimo no total_value ANTES de atualizar
            $finalTotalValue = $totalValue - ($validated['discount_amount'] ?? 0) + ($validated['additional_amount'] ?? 0);
            if ($finalTotalValue < 0) $finalTotalValue = 0; // Garantir que não seja negativo

            $budget->update([
                'title' => $validated['title'],
                'validity' => $validated['validity'],
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'client_phone' => $validated['client_phone'],
                'client_address' => $validated['client_address'],
                'client_cpf_cnpj' => $validated['client_cpf_cnpj'], // NOVO CAMPO
                'client_cep' => $validated['client_cep'],          // NOVO CAMPO
                'description' => $validated['description'],
                'budget_date' => $validated['budget_date'],
                'total_value' => $finalTotalValue, // Total já com descontos/acréscimos
                'discount_amount' => $validated['discount_amount'] ?? 0, // NOVO CAMPO
                'additional_amount' => $validated['additional_amount'] ?? 0, // NOVO CAMPO
                'deadline' => $validated['deadline'], // Campo já existente na migração de orçamentos, mas sem validação
                'contracted_responsibility' => $validated['contracted_responsibility'] ?? 'Fornecer todo material de fabricação e consumo', // NOVO CAMPO
                'contractor_responsibility' => $validated['contractor_responsibility'] ?? null, // NOVO CAMPO
                'deadline_start_description' => $validated['deadline_start_description'] ?? 'a partir do pagamento de entrada', // NOVO CAMPO
                'deadline_type' => $validated['deadline_type'], // NOVO CAMPO
                'payment_method_description' => $validated['payment_method_description'] ?? '50% entrada' . PHP_EOL . '50% no ato da entrega do serviço', // NOVO CAMPO
                'bank_info_description' => $validated['bank_info_description'] ?? 'BANCO DO BRASIL' . PHP_EOL . 'AG: 3175-5 | C/C: 20.439-0' . PHP_EOL . 'PIX: (73) 9 8855-9571', // NOVO CAMPO
                // 'budget_type' e 'original_budget_id' não são atualizados aqui
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 2,
                'department_id' => 15,
                'budget_id' => $budget->id,
            ]);
        });

        return redirect()->route('budgets.index')->with('success', 'Orçamento atualizado com sucesso!');
    }

    /**
     * Cancela um orçamento (soft delete e muda status para 'Cancelado').
     * Permite cancelamento apenas se não tiver gerado um serviço.
     */
    public function cancel(Request $request, string $id)
    {
        $budget = Budget::findOrFail($id);
        // Autorização: Ex: $this->authorize('cancel', $budget);

        if ($budget->generated_service_id !== null) {
            return back()->with('error', 'Este orçamento não pode ser cancelado pois já gerou um serviço.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:10'],
        ]);

        DB::transaction(function () use ($budget, $validated) {
            $budget->update([
                'status' => 'Cancelado',
                'rejection_reason' => $validated['cancellation_reason'], // Reutiliza campo ou cria um 'cancellation_reason'
                'approval_rejection_date' => Carbon::now()->format('Y-m-d'),
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 14,
                'department_id' => 15,
                'budget_id' => $budget->id,
            ]);

            $budget->delete(); // Soft delete

            // Opcional: Registrar uma Procedure para o cancelamento
            // Procedure::create([...]);            
        });

        return back()->with('success', 'Orçamento cancelado com sucesso!');
    }

    /**
     * Remove o orçamento fisicamente (evitar ao máximo).
     */
    public function destroy(string $id)
    {
        // Autorização: Ex: $this->authorize('forceDelete', $budget); // Apenas administradores
        $budget = Budget::withTrashed()->findOrFail($id); // Busca mesmo se soft-deleted

        if ($budget->generated_service_id !== null) {
             return back()->with('error', 'Este orçamento não pode ser excluído fisicamente pois já gerou um serviço. Cancele-o primeiro.');
        }
        // Se este orçamento for uma "Correção", deve-se ter cuidado para não quebrar a FK do original
        // ou remover explicitamente o original_budget_id antes de deletar se a FK não for CASCADE.
        // Já definimos onDelete('set null') na migração, então isso é tratado automaticamente.

        // Remover itens associados primeiro (se onDelete('cascade') não for suficiente ou se houver arquivos)
        $budget->items()->forceDelete(); // Remove fisicamente os itens

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 3,
            'department_id' => 15,
            'budget_id' => $budget->id,
        ]);

        $budget->forceDelete(); // Exclui fisicamente o orçamento

        return back()->with('success', 'Orçamento excluído permanentemente!');
    }


    /**
     * Aprova um orçamento e gera um serviço a partir dele.
     */
    public function approve(Request $request, string $id)
    {
        $budget = Budget::with('items')->findOrFail($id);
        // Autorização: Ex: $this->authorize('approve', $budget);

        if (!in_array($budget->status, ['Rascunho', 'Enviado']) || $budget->generated_service_id !== null) {
            return back()->with('error', 'Este orçamento não pode ser aprovado. Verifique seu status ou se já gerou um serviço.');
        }

        DB::transaction(function () use ($budget) {
            // 1. Criar Accounting para o novo serviço
            $accounting = Accounting::create([
                'estimated_value' => $budget->total_value,
                'total_value' => $budget->total_value,
                'partial_value' => $budget->total_value, // Começa com valor total
            ]);

            // 2. Criar Movement (Serviço)
            $service = Movement::create([
                'type' => 1, // Tipo 'serviço'
                'accounting_id' => $accounting->id,
                'motive' =>  $budget->title, // Corrected missing comma
                'deadline' => $this->calculateDeadline($budget->deadline, $budget->deadline_type), // Use a helper function
                'entity_name' => $budget->client_name,
                'observations' => 'Gerado a partir do orçamento #' . $budget->id . '. ' . $budget->description,
                'previous_id' => null, // Ou algum relacionamento anterior se for o caso
                'date' => Carbon::now()->format('Y-m-d'),
                'budget_id' => $budget->id, // Conecta o serviço ao orçamento de origem
                'status' => 'Não Iniciado', // Status inicial do serviço
            ]);

            // 3. Atualizar o Orçamento
            $budget->update([
                'status' => 'Aprovado',
                'approval_rejection_date' => Carbon::now()->format('Y-m-d'),
                'generated_service_id' => $service->id, // Conecta o orçamento ao serviço gerado
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 12,
                'department_id' => 15,
                'budget_id' => $budget->id,
            ]);
            // Opcional: Registrar Procedures para aprovação do orçamento e criação do serviço
            // Procedure::create([...]);
        });

        return redirect()->route('budgets.show', $budget->id)->with('success', 'Orçamento aprovado e serviço gerado com sucesso!');
    }

    /**
     * Calculates the deadline based on business days or calendar days.
     *
     * @param int $days The number of days to add.
     * @param string $type 'dias úteis' for business days, any other string for calendar days.
     * @return string The calculated deadline in 'Y-m-d' format.
     */
    protected function calculateDeadline(int $days, string $type): string
    {
        $currentDate = Carbon::now();

        if ($type === 'dias úteis') {
            $addedDays = 1;
            $deadline = clone $currentDate;

            while ($addedDays < $days) {
                $deadline->addDay();

                // Check if it's a weekday and not a holiday
                if ($deadline->isWeekday() && !$this->isHoliday($deadline)) {
                    $addedDays++;
                }
            }
            return $deadline->format('Y-m-d');

        } else {
            // 'dias corridos' or any other type
            return $currentDate->addDays($days)->format('Y-m-d');
        }
    }

    /**
     * Checks if a given date is a holiday.
     * YOU WILL NEED TO IMPLEMENT THIS METHOD.
     * This is a placeholder example. You might fetch holidays from a database, API, or config file.
     *
     * @param Carbon $date The date to check.
     * @return bool True if it's a holiday, false otherwise.
     */
    protected function isHoliday(Carbon $date): bool
    {
        // Example placeholder for holidays.
        // In a real application, you would fetch these dynamically.
        $holidays = [
            '2025-01-01', // Confraternização Universal
            '2025-02-25', // Carnaval (example, often variable)
            '2025-02-26', // Quarta-feira de Cinzas (example)
            '2025-04-21', // Tiradentes
            '2025-05-01', // Dia do Trabalho
            '2025-09-07', // Independência do Brasil
            '2025-10-12', // Nossa Senhora Aparecida
            '2025-11-02', // Finados
            '2025-11-15', // Proclamação da República
            '2025-12-25', // Natal
        ];

        return in_array($date->format('Y-m-d'), $holidays);
    }

    /**
     * Rejeita um orçamento.
     */
    public function reject(Request $request, string $id)
    {
        $budget = Budget::findOrFail($id);
        // Autorização: Ex: $this->authorize('reject', $budget);

        if (!in_array($budget->status, ['Rascunho', 'Enviado'])) {
            return back()->with('error', 'Este orçamento não pode ser rejeitado. Verifique seu status.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10'],
        ]);

        DB::transaction(function () use ($budget, $validated) {
            $budget->update([
                'status' => 'Rejeitado',
                'rejection_reason' => $validated['rejection_reason'],
                'approval_rejection_date' => Carbon::now()->format('Y-m-d'),
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 13,
                'department_id' => 15,
                'budget_id' => $budget->id,
            ]);

            // Opcional: Registrar uma Procedure para a rejeição
            // Procedure::create([...]);
        });

        return back()->with('success', 'Orçamento rejeitado com sucesso!');
    }

    /**
     * Duplica um orçamento existente.
     */
    public function duplicate(Request $request, string $id)
    {
        $originalBudget = Budget::with('items')->findOrFail($id);
        // Autorização: Ex: $this->authorize('duplicate', $originalBudget);

        $validated = $request->validate([
            'client_name' => ['nullable', 'string', 'max:255'], // Permitir novo nome de cliente
            'new_description' => ['nullable', 'string'], // Nova descrição para a cópia
            'budget_type' => ['required', 'in:Original,Correção'], // Se o tipo é passado no request
            'original_budget_id' => ['nullable', 'exists:budgets,id'], // Será o ID do orçamento original
        ]);


        DB::transaction(function () use ($originalBudget, $validated) {
            $duplicatedBudget = $originalBudget->replicate(); // Copia todos os atributos

            // Sobrescreve campos importantes para a duplicação
            $duplicatedBudget->budget_date = Carbon::now()->format('Y-m-d');
            $duplicatedBudget->status = 'Rascunho'; // Sempre Rascunho ao duplicar
            $duplicatedBudget->approval_rejection_date = null;
            $duplicatedBudget->rejection_reason = null;
            $duplicatedBudget->generated_service_id = null; // Não gerou serviço ainda

            // Campos que podem ser alterados na duplicação (se fornecidos no request)
            $duplicatedBudget->client_name = $validated['client_name'] ?? $originalBudget->client_name;
            $duplicatedBudget->description = $validated['new_description'] ?? $originalBudget->description . ' (Cópia)';
            $duplicatedBudget->budget_type = $validated['budget_type']; // 'Original' ou 'Correção'
            $duplicatedBudget->original_budget_id = $validated['original_budget_id'] ?? $originalBudget->id; // Guarda o ID do original se for correção

            // Para um orçamento de 'Correção', o original_budget_id deve ser o ID do orçamento que está sendo duplicado
            if ($validated['budget_type'] === 'Correção') {
                $duplicatedBudget->original_budget_id = $originalBudget->id;
            } else {
                $duplicatedBudget->original_budget_id = null; // Se for um novo "Original", não tem original_budget_id
            }


            $duplicatedBudget->save();

            foreach ($originalBudget->items as $item) {
                $duplicatedItem = $item->replicate();
                $duplicatedItem->budget_id = $duplicatedBudget->id;
                $duplicatedItem->save();
            }

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 15,
                'budget_id' => $duplicatedBudget->id,
            ]);

            // Opcional: Registrar uma Procedure para a duplicação
            // Procedure::create([...]);
        });

        // Retorna para a página de edição do orçamento duplicado para que o usuário possa revisá-lo
        return redirect()->route('budgets.edit', $duplicatedBudget->id)->with('success', 'Orçamento duplicado com sucesso! Edite-o agora.');
    }

    public function recreate(Request $request, Budget $budget) // <-- AQUI É ONDE O LARAVEL BUSCA PELO ID E INJETA O OBJETO
    {
        // $this->authorize('recreate', $budget); // Exemplo de autorização
        // Garante que os itens do orçamento original estão carregados para a duplicação
        // Isso é importante porque o replicate() não carrega relacionamentos automaticamente
        $budget->loadMissing('items');

        $newlyCreatedBudget = null; // Declarado fora da transação para ser acessível no redirect

        DB::transaction(function () use ($budget, &$newlyCreatedBudget) {
            // 1. Duplicar o orçamento original
            $duplicatedBudget = $budget->replicate()->fill([
                'id' => null,
                'title' => $budget->title,
                'budget_date' => Carbon::now()->format('Y-m-d'),
                'status' => 'Rascunho',
                'approval_rejection_date' => null,
                'rejection_reason' => null,
                'generated_service_id' => null,
                'budget_type' => 'Correção',
                'original_budget_id' => $budget->id,
            ]);

            // Salva o novo orçamento. Isso vai gerar um novo ID.
            $duplicatedBudget->save();

            // Atribui o orçamento duplicado à variável externa para uso no redirect
            $newlyCreatedBudget = $duplicatedBudget;

            // 3. Atualizar o orçamento original para 'Cancelado'
            $budget->status = 'Cancelado';
            $budget->rejection_reason = 'Orçamento cancelado para recriação/revisão (novo ID: ' . $newlyCreatedBudget->id . ')';
            $budget->approval_rejection_date = Carbon::now()->format('Y-m-d');
            $budget->save();

            // 4. Duplicar os itens do orçamento
            foreach ($budget->items as $item) {
                $duplicatedItem = $item->replicate()->fill([
                    'id' => null,
                    'budget_id' => $newlyCreatedBudget->id
                ]);
                $duplicatedItem->save();
            }

            // 5. Registrar Procedures para o cancelamento do original e criação do novo
            Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 14, // ID da ação "Cancelado" (ajuste conforme seu banco)
                'department_id' => 15,
                'budget_id' => $budget->id,
                'rejection_reason' => $budget->rejection_reason, // Armazenar o motivo da procedure
            ]);

            $budget->delete();

            Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1, // ID da ação "Criação" (ajuste conforme seu banco)
                'department_id' => 15,
                'budget_id' => $newlyCreatedBudget->id,
            ]);
        });

        // Redireciona para a página de edição do orçamento recém-criado
        return redirect()->route('budgets.edit', $newlyCreatedBudget->id)->with('success', 'Orçamento cancelado e recriado com sucesso! Edite o novo orçamento agora.');
    }

    /**
     * Imprimir um orçamento.
     */
    public function print(string $id)
    {
        $budget = Budget::with('items')->withTrashed()->findOrFail($id);
        // Autorização: Ex: $this->authorize('view', $budget);

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 7, // Action ID for "Printed"
            'department_id' => 15,
            'budget_id' => $budget->id,
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Enable loading remote assets like images if needed
        $options->set('defaultFont', 'sans-serif'); // Set a default font

        $dompdf = new Dompdf($options);

        // Render the Blade view to get HTML content
        // Make sure to pass the budget data to the view
        $html = view('pdfs.budget_print', compact('budget'))->render();

        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Stream the PDF to the browser
        return $dompdf->stream("#{$budget->id} | {$budget->title} - {$budget->client_name}.pdf", ["Attachment" => false]);
    }

    // Você também pode precisar de um método para "Enviar Orçamento"
    public function send(Request $request, string $id)
    {
        $budget = Budget::findOrFail($id);
        // Autorização: $this->authorize('send', $budget);

        if ($budget->status !== 'Rascunho') {
            return back()->with('error', 'Somente orçamentos em rascunho podem ser enviados.');
        }

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 11,
            'department_id' => 15,
            'budget_id' => $budget->id,
        ]);

        $budget->update(['status' => 'Enviado']);

        return back()->with('success', 'Orçamento marcado como enviado!');
    }
}