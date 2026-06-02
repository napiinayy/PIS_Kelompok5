<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['user_id', 'name', 'color', 'icon', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bills()
    {
        return $this->belongsToMany(Bill::class, 'bill_category');
    }

    public function getBillsCountAttribute(): int
    {
        return $this->bills()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->bills->sum(fn($b) => $b->grand_total);
    }
}
