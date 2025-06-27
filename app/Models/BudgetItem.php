<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory, Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    protected $doNotLogFields = [];
    
    protected $fillable = [
        'budget_id',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relacionamento com o orçamento pai
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Define o ID do departamento para este modelo.
     * @return int
     */
    public function getDepartmentIdForAudit(): int
    {
        return 15; // ID do departamento 'Almoxarifado'
    }
}