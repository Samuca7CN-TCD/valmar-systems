<?php

namespace App\Models;

// use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accounting extends Model
{
    use HasFactory, SoftDeletes; // Auditable;

    protected $fillable = [
        'estimated_value',
        'total_value',
        'partial_value',
    ];

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    // protected $doNotLogFields = [];

    public function movement()
    {
        return $this->hasOne(Movement::class);
    }

    /**
     * Define o ID do departamento para este modelo.
     * @return int
     */
    /* public function getDepartmentIdForAudit(): int
    {
        return null;
    }*/
}