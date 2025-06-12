<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotivoAtraso extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description',
        'movement_id'
    ];

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }
}
