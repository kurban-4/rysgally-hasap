<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleStorage extends Model
{
    protected $fillable = [
        'medicine_id',
        'quantity',
        'received_price',
        'selling_price',
        'discount',
        'batch_number',
        'expiry_date'
    ];
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
