<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accounting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'estimated_value',
        'total_value',
        'partial_value',
    ];

    public function movement()
    {
        return $this->hasOne(Movement::class);
    }
}