<?php

namespace App\Models;

// use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasurementUnit extends Model
{
    use HasFactory, SoftDeletes; // Auditable;

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    // protected $doNotLogFields = [];
    
    public function categories()
    {
        return $this->belongsTo(Category::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
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
