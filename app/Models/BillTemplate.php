<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'restaurant_name',
        'tax_percent', 'service_percent', 'notes', 'times_used',
    ];

    protected $casts = [
        'tax_percent'     => 'float',
        'service_percent' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BillTemplateItem::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->price * $i->quantity);
    }

    public function getEstimatedTotalAttribute(): float
    {
        $subtotal = $this->subtotal;
        return round(
            $subtotal
            + ($subtotal * $this->tax_percent / 100)
            + ($subtotal * $this->service_percent / 100),
            2
        );
    }
}
