<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Inertia\Inertia;

use App\Models\Client;
use App\Models\Department;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('trade_name', 'like', "%{$search}%")
                ->orWhere('document', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only('search'),
            'page' => Department::find(16) // Assumindo 16 como ID de Clientes
        ]);
    }

    public function search(Request $request)
    {
        $term = $request->input('term');

        if (empty($term)) {
            return response()->json([]);
        }

        $clients = Client::query()
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                      ->orWhere('trade_name', 'LIKE', "%{$term}%")
                      ->orWhere('document', 'LIKE', "%{$term}%");
            })
            ->select('id', 'name', 'document')
            ->limit(10)
            ->get();
            
        return response()->json($clients);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clients/Create', [
            'page' => Department::find(16)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => ['required', Rule::in(['fisica', 'juridica'])],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255', 'required_if:type,juridica'],
            'document' => ['nullable', 'string', 'max:18', 'unique:clients,document'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email'],
            'contacts' => ['nullable', 'array'],
            'contacts.*' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:9'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'address_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:2'],
            'observations' => ['nullable', 'string'],
        ]);

        if(strlen($validatedData['document']) === 0) $validatedData['document'] = "Sem documento";

        // O Trait Auditable cuidará de registrar esta criação automaticamente.
        Client::create($validatedData);

        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Client $client)
    {
        // Se a requisição for AJAX (feita pelo axios do ClientSelector), retorna JSON.
        if ($request->wantsJson()) {
            return response()->json(['client' => $client]);
        }

        // Caso contrário, carrega os relacionamentos e renderiza a página de visualização normal.
        $client->load([
            'movements' => function ($query) {
                $query->with('accounting')->orderBy('date', 'desc');
            },
            'budgets' => function ($query) {
                $query->orderBy('budget_date', 'desc');
            }
        ]);

        return Inertia::render('Clients/Show', [
            'page' => Department::find(16),
            'client' => $client,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'page' => Department::find(16),
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'type' => ['required', Rule::in(['fisica', 'juridica'])],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255', 'required_if:type,juridica'],
            'document' => ['nullable', 'string', 'max:18', Rule::unique('clients')->ignore($client->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clients')->ignore($client->id)],
            'contacts' => ['nullable', 'array'],
            'contacts.*' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:9'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'address_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:2'],
            'observations' => ['nullable', 'string'],
        ]);

        if(strlen($validatedData['document']) === 0) $validatedData['document'] = "Sem documento";

        // O Trait Auditable cuidará de registrar esta atualização automaticamente.
        $client->update($validatedData);

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // A autorização pode ser adicionada aqui, se necessário
        // $this->authorize('delete', $client);

        DB::transaction(function () use ($client) {
            // O modelo usa SoftDeletes, então isso irá "arquivar" o cliente.
            // O trait Auditable registrará a exclusão automaticamente.
            $client->delete();
        });

        return redirect()->route('clients.index')->with('success', 'Cliente excluído com sucesso.');
    }
}
