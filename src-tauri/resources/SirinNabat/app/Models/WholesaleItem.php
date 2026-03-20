<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleItem extends Model
{
    protected $fillable = [
        'wholesale_invoice_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'row_total',
        'expiry_date_text', 
        'batch_number_text' 
    ];
    public function invoice()
    {
        return $this->belongsTo(WholesaleInvoice::class, 'wholesale_invoice_id');
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
