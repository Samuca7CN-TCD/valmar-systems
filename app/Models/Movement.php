<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'remaked',
        'ready',
        'accounting_id',
        'motive',
        'employee_id',
        'entity_name',
        'deadline',
        'observations',
        'date',
        'delay_reason',
        'completion_date',
        'delayed',
        'previous_id',
        'service_status',
        'cancellation_reason',
        'budget_id',
    ];

    public function sell()
    {
        return $this->hasOne(Movement::class);
    }

    public function accounting()
    {
        return $this->belongsTo(Accounting::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function motivosAtrasos()
    {
        return $this->hasMany(MotivoAtraso::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
