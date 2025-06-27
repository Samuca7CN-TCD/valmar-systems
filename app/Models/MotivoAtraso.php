<?php

namespace App\Models;

// use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotivoAtraso extends Model
{
    use HasFactory, SoftDeletes; // Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    // protected $doNotLogFields = [];
    
    protected $fillable = [
        'description',
        'movement_id'
    ];

    public function movement()
    {
        return $this->belongsTo(Movement::class);
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
