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
    ];

    public function accounting()
    {
        return $this->belongsTo(Accounting::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}
