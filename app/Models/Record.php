<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Record extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'procedure_id',
        'item_id',
        'employee_id',
        'name',
        'quantity',
        'measurement_unit',
        'price',
        'movement_quantity',
        'amount',
        'payment_method',
        'content',
        'register_date',
        'filepath',
        'past',
        'before_state',
        'after_state',
        'auditable_id',
        'auditable_type',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
