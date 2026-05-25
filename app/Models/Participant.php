<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = ['bill_id', 'user_id', 'name', 'is_paid'];
    protected $casts    = ['is_paid' => 'boolean'];

    public function bill()        { return $this->belongsTo(Bill::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function assignments() { return $this->hasMany(ItemAssignment::class); }
    public function splitResult() { return $this->hasOne(SplitResult::class); }
}
