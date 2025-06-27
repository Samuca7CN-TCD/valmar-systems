<?php

namespace App\Models;

// use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use HasFactory, SoftDeletes; // Auditable;

    protected $fillable = [
        'user_id',
        'action_id',
        'department_id',
        'movement_id',
        'budget_id',
        'previous_id',
        'remaked',
    ];

    /**
     * Define quais campos não devem ser logados.
     * @var array
     */
    // protected $doNotLogFields = [];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
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
