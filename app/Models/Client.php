<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'type',
        'name',
        'trade_name',
        'document',
        'email',
        'contacts',
        'postal_code',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'observations',
    ];

    protected $casts = [
        'contacts' => 'array',
    ];

    /**
     * Define o ID do departamento para fins de auditoria.
     */
    public function getDepartmentIdForAudit(): int
    {
        return 16; // ID do departamento 'Clientes'
    }

    /**
     * Um cliente pode ter muitos movimentos (vendas, serviços, pagamentos).
     */
    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Um cliente pode ter muitos orçamentos.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}