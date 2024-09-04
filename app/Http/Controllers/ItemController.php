<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Item;
use App\Models\Procedure;
use App\Models\Record;
use App\Models\Category;
use App\Models\MeasurementUnit;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($category_id = null)
    {
        $page = Department::where('type', 'warehouse')->first();
        $categories = Category::select('id', 'name', 'name_plural')->get();

        if ($category_id)
            $items = array($categories->find($category_id)->load(['items' => fn($query) => $query->orderBy('name')->get()]));
        else
            $items = $categories->load(['items' => fn($query) => $query->orderBy('name')->get()]);

        $measurement_units = MeasurementUnit::get();

        return Inertia::render('Warehouse/Items', [
            'page' => $page,
            'page_options' => $categories,
            'items_list' => $items,
            'categories_list' => $categories,
            'measurement_units_list' => $measurement_units,
        ]);
    }

    /**
     * Display a listing of the resource to print.
     *
     * @return \Inertia\Response
     */
    public function print_items($buy_option = 0)
    {
        $page = Department::where('type', 'warehouse')->first();
        $categories = Category::select('id', 'name_plural')->get();

        if ($buy_option)
            $items = Item::with('measurement_unit:id,name,name_plural', 'category:id,name')->whereColumn('items.quantity', '<', 'items.min_quantity');
        else
            $items = Item::with('measurement_unit:id,name,name_plural', 'category:id,name');

        $items = $items->orderBy('category_id')->orderBy('name')->get();

        return Inertia::render('Warehouse/PrintItems', [
            'page' => $page,
            'items_list' => $items,
            'categories_list' => $categories,
            'buy_option' => (Boolean) $buy_option,
        ]);
    }

    public function use_table($month = null)
    {
        $page = Department::where('type', 'warehouse')->first();
        if (!$month)
        {
            $month = date('Y-m');
        }

        // Carregar todos os itens e funcionários
        $items = Item::where('list_in_uses', true)->orderBy('name')->get();
        $employees = Employee::orderBy('name')->orderBy('surname')->get();

        // Assumindo que você tem um relacionamento adequado 'procedure' e 'movement' em Record
        // e que a data está no formato correto (yyyy-mm)
        $records = Record::whereHas('procedure.movement', function ($query) {
            $query->where('type', 3);
        })
            ->where('register_date', 'like', "{$month}%")
            ->with('procedure.movement') // Carregar os relacionamentos necessários
            ->get();

        // Contar o uso de itens por funcionários
        $itemCounts = [];
        foreach ($items as $item)
        {
            $itemCounts[$item->id] = [];
            foreach ($employees as $employee)
            {
                $itemCounts[$item->id][$employee->id] = 0;
            }
        }

        foreach ($records as $record)
        {
            $employeeId = $record->employee_id;
            $itemId = $record->item_id;
            $quantity = $record->movement_quantity;

            if (array_key_exists($itemId, $itemCounts) && array_key_exists($employeeId, $itemCounts[$itemId]))
            {
                $itemCounts[$itemId][$employeeId] += $quantity;
            } else
            {
                $itemCounts[$itemId][$employeeId] = $quantity;
            }
        }

        // Passe os dados para a visualização Inertia
        return Inertia::render('Warehouse/UseTable', [
            'page' => $page,
            'items' => $items,
            'employees' => $employees,
            'itemCounts' => $itemCounts,
            'selectedMonth' => $month, // Você pode usar isso na sua visualização para exibir o mês selecionado
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $validate = (object) $request->validate([
                'profile_img_url' => ['nullable', 'string'],
                'profile_img' => ['nullable', 'file', 'max:2097152',],
                'name' => ['required', 'string', 'max:255', 'unique:items'],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'price' => ['required', 'numeric', 'min:0'],
                'quantity' => ['required', 'numeric', 'min:0'],
                'min_quantity' => ['nullable', 'numeric', 'lte:max_quantity', 'min:0'],
                'max_quantity' => ['nullable', 'numeric', 'min:0'],
                'measurement_unit_id' => ['required', 'integer', 'exists:measurement_units,id'],
                'unit_equivalent' => ['required', 'numeric', 'min:0'],
                'list_in_uses' => ['required', 'boolean'],
            ]);

            $profile_img = $validate->profile_img ?: null;
            $profile_img = $validate->profile_img ?: null;

            if ($profile_img)
            {
                $profile_img = $profile_img->store('public/img/items');
            } else if ($validate->profile_img_url)
            {
                $original_img = explode('/', $validate->profile_img_url)[4];

                // Verifica se a imagem original existe
                if (Storage::disk('public')->exists('img/items/' . $original_img))
                {
                    $profile_img = "copy_" . $original_img;
                    while (Storage::disk('public')->exists('img/items/' . $profile_img))
                    {
                        $profile_img = 'copy_' . $profile_img;
                    }
                    Storage::disk('public')->copy('img/items/' . $original_img, 'img/items/' . $profile_img);
                    $profile_img = "public/img/items/" . $profile_img;
                } else
                {
                    // Se a imagem não for encontrada, armazena uma nova imagem (se existir uma nova imagem)
                    if ($profile_img)
                    {
                        $profile_img = $profile_img->store('public/img/items');
                    } else
                    {
                        // Defina um comportamento alternativo se não houver imagem nova para salvar
                        $profile_img = null;
                    }
                }
            }

            $item = Item::create([
                'profile_img' => $profile_img,
                'name' => $validate->name,
                'category_id' => $validate->category_id,
                'price' => $validate->price,
                'quantity' => $validate->quantity,
                'min_quantity' => $validate->min_quantity,
                'max_quantity' => $validate->max_quantity,
                'measurement_unit_id' => $validate->measurement_unit_id,
                'unit_equivalent' => $validate->unit_equivalent,
                'list_in_uses' => $validate->list_in_uses
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 2,
                'movement_id' => null,
            ]);

            $measuremtent_unit = MeasurementUnit::find($item->measurement_unit_id);
            $measuremtent_unit = $measuremtent_unit->abbreviation;

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => $item->id,
                'name' => $item->name,
                'quantity' => 0,
                'measurement_unit' => $measuremtent_unit,
                'price' => $item->price,
                'movement_quantity' => $item->quantity,
                'amount' => $item->price * $item->quantity,
                'past' => true,
                'content' => json_encode($item),
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            return back();
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            // dd($request);
            $item = Item::findOrFail($id);
            $request->validate([
                'profile_img_url' => ['nullable', 'string'],
                'profile_img' => ['nullable', 'file', 'max:2097152'],
                'name' => ['required', 'string', 'max:255', 'unique:items,name,' . $id],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'price' => ['required', 'numeric', 'min:0'],
                'quantity' => ['required', 'numeric', 'min:0'],
                'min_quantity' => ['nullable', 'numeric', 'lte:max_quantity', 'min:0'],
                'max_quantity' => ['nullable', 'numeric', 'gte:max_quantity', 'min:0'],
                'measurement_unit_id' => ['required', 'integer', 'exists:measurement_units,id'],
                'unit_equivalent' => ['required', 'numeric', 'min:0'],
                'list_in_uses' => ['required', 'boolean'],
            ]);

            $profile_img = $request->profile_img !== null ? $request->file('profile_img') : null;

            if ($profile_img)
            {
                // Verifica se a imagem atual existe antes de tentar excluí-la
                if ($item->profile_img && file_exists(storage_path('app/' . $item->profile_img)))
                {
                    unlink(storage_path('app/' . $item->profile_img));
                }
                // Armazena a nova imagem
                $item->profile_img = $profile_img->store('public/img/items');
            } else
            {
                if ($request->profile_img_url === null)
                {
                    // Verifica se a imagem atual existe antes de tentar excluí-la
                    if ($item->profile_img && file_exists(storage_path('app/' . $item->profile_img)))
                    {
                        unlink(storage_path('app/' . $item->profile_img));
                    }
                    // Remove a imagem do item
                    $item->profile_img = null;
                }
            }


            $item->name = $request->name;
            $item->category_id = $request->category_id;
            $item->price = $request->price;
            $item->quantity = $request->quantity;
            $item->min_quantity = $request->min_quantity;
            $item->max_quantity = $request->max_quantity;
            $item->measurement_unit_id = $request->measurement_unit_id;
            $item->unit_equivalent = $request->unit_equivalent;
            $item->list_in_uses = $request->list_in_uses;
            $item->save();

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 2,
                'department_id' => 2,
                'movement_id' => null,
            ]);

            $measuremtent_unit = MeasurementUnit::find($item->measurement_unit_id);
            $measuremtent_unit = $measuremtent_unit->abbreviation;

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_quantity' => $item->quantity,
                'item_measurement_unit' => $measuremtent_unit,
                'price' => $item->price,
                'amount' => $item->price * $item->quantity,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            return back();
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $item = Item::findOrFail($id);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 3,
                'department_id' => 2,
                'movement_id' => null,
            ]);

            $measuremtent_unit = MeasurementUnit::find($item->measurement_unit_id);
            $measuremtent_unit = $measuremtent_unit->abbreviation;

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_quantity' => $item->quantity,
                'item_measurement_unit' => $measuremtent_unit,
                'price' => $item->price,
                'amount' => $item->price * $item->quantity,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            $item->delete();
            return back();
        });
    }
}
