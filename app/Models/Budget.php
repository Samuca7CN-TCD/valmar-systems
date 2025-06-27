<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    protected $doNotLogFields = [];
    
    protected $fillable = [
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'description',
        'total_value',
        'budget_date',
        'status',
        'approval_rejection_date',
        'rejection_reason',
        'generated_service_id',
        'discount_amount',  // Certifique-se que esses campos estão na migração e aqui
        'additional_amount',// Certifique-se que esses campos estão na migração e aqui
        'cancellation_reason', // Se você adicionou este campo diretamente no modelo anteriormente

        // NOVOS CAMPOS:
        'title',
        'deadline',
        'client_cpf_cnpj',        // Adicionado: Campo para CPF/CNPJ
        'client_cep',             // Adicionado: Campo para CEP
        'contracted_responsibility', // Adicionado: Responsabilidade da Contratada
        'contractor_responsibility', // Adicionado: Responsabilidade do Contratante
        'deadline_start_description',// Adicionado: Descrição do início do prazo
        'deadline_type',          // Adicionado: Tipo de prazo (dias úteis/corridos)
        'payment_method_description',// Adicionado: Descrição da forma de pagamento
        'bank_info_description',  // Adicionado: Informações bancárias
        'budget_type',            // Adicionado: Tipo de orçamento (Original/Correção)
        'original_budget_id',     // Adicionado: ID do orçamento original (para correções)
        'discount_amount',
        'additional_amount',
        'client_id',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'budget_date' => 'date',
        'approval_rejection_date' => 'date',
        // 'status' => \App\Enums\BudgetStatusEnum::class, // Opcional, se você usar Enums no Laravel
        // 'deadline_type' => \App\Enums\DeadlineTypeEnum::class, // Exemplo se criar um Enum para deadline_type
        // 'budget_type' => \App\Enums\BudgetTypeEnum::class, // Exemplo se criar um Enum para budget_type
        'discount_amount' => 'decimal:2',   // Casting para os novos campos monetários
        'additional_amount' => 'decimal:2', // Casting para os novos campos monetários
    ];

    // Relacionamento com os itens do orçamento (existente)
    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    // Relacionamento com o serviço gerado a partir deste orçamento (existente)
    public function generatedService()
    {
        return $this->belongsTo(Movement::class, 'generated_service_id');
    }

    // NOVO RELACIONAMENTO: Orçamento original (para orçamentos do tipo "Correção")
    public function originalBudget()
    {
        return $this->belongsTo(Budget::class, 'original_budget_id');
    }

    public function correctionBudget()
    {
        return $this->hasOne(Budget::class, 'original_budget_id');
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
        return 15; // ID do departamento 'Almoxarifado'
    }
}