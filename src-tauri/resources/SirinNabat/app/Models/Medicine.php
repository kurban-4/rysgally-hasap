<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WholesaleStorage;
use App\Models\WholesaleInvoice;
use App\Models\WholesaleItem;
use App\Models\Storage;
class Medicine extends Model
{
    protected $fillable = [
        'name',
        'discount',
        'barcode',
        'description',
        'manufacturer',
        'price',
        'category',
        'produced_date',
        'expiry_date',
        'units_per_box',
        'price_box',
        'price_unit',
        'total_quantity_units'
    ];
    public function storage()
    {
        return $this->hasOne(Storage::class);
    }

public function wholesaleStorage()
{
    return $this->hasMany(\App\Models\WholesaleStorage::class, 'medicine_id', 'id');
}
    public function getTotalwholesaleStorageAttribute()
    {
        return $this->wholesaleStorage()->sum('quantity');
    }
    public function getDiscountedPriceAttribute()
    {
        if ($this->discount > 0) {
            $discounted = $this->price - ($this->price * $this->discount / 100);
            return number_format($discounted, 2);
        }

        return number_format($this->price, 2);
    }
    public function getFinalPriceAttribute()
    {
        return $this->discount > 0 
            ? $this->price * (1 - $this->discount / 100) 
            : $this->price;
    }

}