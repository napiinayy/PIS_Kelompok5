<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['bill_id', 'name', 'price', 'quantity'];
    protected $casts    = ['price' => 'float'];

    public function bill()        { return $this->belongsTo(Bill::class); }
    public function assignments() { return $this->hasMany(ItemAssignment::class); }

    public function getSubtotalAttribute(): float
    {
        return round($this->price * $this->quantity, 2);
    }
}
