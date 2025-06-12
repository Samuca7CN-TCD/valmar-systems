<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'payslip_id',
        'description',
        'month',
        'year',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
