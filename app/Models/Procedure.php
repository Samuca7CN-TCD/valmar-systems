<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'action_id',
        'department_id',
        'movement_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function action()
    {
        return $this->hasOne(Action::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}