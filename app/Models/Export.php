<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    protected $fillable = ['bill_id', 'created_by', 'type', 'file_path'];

    public function bill()    { return $this->belongsTo(Bill::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
