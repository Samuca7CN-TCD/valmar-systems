<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movement extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    protected $doNotLogFields = [];
    
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
        'client_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Define o ID do departamento para este modelo.
     * @return int
     */
    public function getDepartmentIdForAudit(): int
    {
        // Mapeamento de 'type' para 'department_id'.
        // Os IDs de departamento (ex: 4, 5, 6) são exemplos e devem ser
        // ajustados para corresponder aos IDs reais na sua tabela 'departments'.
        switch ($this->type) {
            case 0: // Pagamento
                return 7;
            case 1: // Serviço
                return 8;
            case 2: // Venda
                return 5;
            case 3: // Uso
                return 4;
            case 4: // Entrada
                return 3;
            default:
                return 1; // Departamento Genérico/Padrão como fallback
        }
    }
}
