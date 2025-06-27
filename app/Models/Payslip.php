<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'employee_id',
        'description',
        'value',
        'detail',
        'month',
        'year',
    ];

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    protected $doNotLogFields = [];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Define o ID do departamento para este modelo.
     * @return int
     */
    public function getDepartmentIdForAudit(): int
    {
        return 12; // ID do departamento 'Almoxarifado'
    }
}
