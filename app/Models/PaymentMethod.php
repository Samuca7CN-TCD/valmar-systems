<?php

namespace App\Models;

// use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes; // Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    // protected $doNotLogFields = [];
    
    public function employees()
    {
        return $this->hasMany(Employee::class, 'payment_method_id');
    }

    public function overtimeEmployees()
    {
        return $this->hasMany(Employee::class, 'overtime_payment_method_id');
    }

    /**
     * Define o ID do departamento para este modelo.
     * @return int
     */
    /*public function getDepartmentIdForAudit(): int
    {
        return null;
    }*/
}
