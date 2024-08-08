<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public function measurement_unit()
    {
        return $this->hasOne(MeasurementUnit::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
