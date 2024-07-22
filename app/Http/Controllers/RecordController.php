<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Record;
use App\Models\Procedure;
use App\Models\Movement;
use App\Models\Accounting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $record = Record::findOrFail($id);
            $procedure = Procedure::findOrFail($record->procedure_id);
            $movement = Movement::findOrFail($procedure->movement_id);
            $accounting = Accounting::findOrFail($movement->accounting_id);

            $accounting->update([
                'partial_value' => $accounting->partial_value + $record->amount,
            ]);

            $new_procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 9,
                'department_id' => 7,
                'movement_id' => $movement->id,
            ]);

            $record->update([
                'procedure_id' => $new_procedure->id,
            ]);

            $record->delete();

            return back();
        });
    }
}
