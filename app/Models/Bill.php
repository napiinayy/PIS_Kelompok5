<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id', 'created_by', 'name', 'restaurant_name',
        'date', 'tax_percent', 'service_percent', 'notes', 'status',
    ];

    protected $casts = [
        'date'            => 'date',
        'tax_percent'     => 'float',
        'service_percent' => 'float',
    ];

    public function group()       { return $this->belongsTo(Group::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function items()       { return $this->hasMany(BillItem::class); }
    public function participants(){ return $this->hasMany(Participant::class); }
    public function scans()       { return $this->hasMany(ReceiptScan::class); }
    public function splitResults(){ return $this->hasMany(SplitResult::class); }
    public function sharedLinks() { return $this->hasMany(SharedLink::class); }
    public function exports()     { return $this->hasMany(Export::class); }

    // Fitur 3 — Categories
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'bill_category');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->price * $i->quantity);
    }

    public function getTaxAmountAttribute(): float
    {
        return round($this->subtotal * $this->tax_percent / 100, 2);
    }

    public function getServiceAmountAttribute(): float
    {
        return round($this->subtotal * $this->service_percent / 100, 2);
    }

    public function getGrandTotalAttribute(): float
    {
        return round($this->subtotal + $this->tax_amount + $this->service_amount, 2);
    }
}
