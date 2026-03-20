<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['medicine_id',  'quantity', 'total_price', 'sale_type','transaction_id'];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
