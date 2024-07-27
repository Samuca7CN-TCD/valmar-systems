<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Record extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'item_id',
        'name',
        'quantity',
        'measurement_unit',
        'price',
        'movement_quantity',
        'amount',
        'content',
        'register_date',
        'filepath',
        'past',
    ];
    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
