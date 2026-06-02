<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillTemplateItem extends Model
{
    protected $fillable = ['bill_template_id', 'name', 'price', 'quantity'];

    protected $casts = ['price' => 'float'];

    public function template()
    {
        return $this->belongsTo(BillTemplate::class, 'bill_template_id');
    }

    public function getSubtotalAttribute(): float
    {
        return round($this->price * $this->quantity, 2);
    }
}
