<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SplitResult extends Model
{
    protected $fillable = ['bill_id', 'participant_id', 'subtotal', 'tax_share', 'service_share', 'total'];
    protected $casts    = ['subtotal'=>'float','tax_share'=>'float','service_share'=>'float','total'=>'float'];

    public function bill()        { return $this->belongsTo(Bill::class); }
    public function participant() { return $this->belongsTo(Participant::class); }
}
