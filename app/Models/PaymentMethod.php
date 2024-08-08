<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    public function employees()
    {
        return $this->hasMany(Employee::class, 'payment_method_id');
    }

    public function overtimeEmployees()
    {
        return $this->hasMany(Employee::class, 'overtime_payment_method_id');
    }
}
