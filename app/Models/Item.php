<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'profile_img',
        'name',
        'category_id',
        'price',
        'quantity',
        'min_quantity',
        'max_quantity',
        'measurement_unit_id',
        'unit_equivalent',
        'list_in_uses'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function measurement_unit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
