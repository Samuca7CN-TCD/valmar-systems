<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class);
    }

    public function bank()
    {
        return $this->hasOne(Bank::class);
    }

    public function accountType()
    {
        return $this->hasOne(AccountType::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
