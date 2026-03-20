<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    
protected $fillable = ['medicine_id',  'quantity', 'category' ];
    public function medicine()
{
    return $this->belongsTo(\App\Models\Medicine::class, 'medicine_id');
}
}