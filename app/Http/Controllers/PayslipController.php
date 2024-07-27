<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use \Inertia\Inertia;

use App\Models\Payslip;

class PayslipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($employee_id = null)
    {
        $page = Department::where('type', 'payslip')->first();
        $payslips = Payslip::all();
        $employees = Employee::where('id', '>', 1)->get();

        if ($employee_id)
        {
            $employee = Employee::findOrFail($employee_id);
        } else
        {
            $employee = Employee::first();
        }

        $payslips = Payslip::where('employ_id', $employee->id);

        return Inertia::render('Employees/Payslips', [
            'page' => $page,
            'page_options' => $employees,
            'payslips_list' => $payslips,
            'employee' => $employee,
        ]);
    }

    public function inkdex($category_id = null)
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
    public function destroy(string $id)
    {
        //
    }
}
