<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'salary',
        'agreement',
        'contacts',
        'function_name',
        'transportation_voucher',
        'payment_method_id',
        'overtime_payment_method_id',
        'bank_id',
        'pix_cpf',
        'pix_email',
        'pix_phone_number',
        'pix_token',
        'bank_ag',
        'fired',
        'account_type_id',
        'account_number',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function overtimePaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'overtime_payment_method_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
