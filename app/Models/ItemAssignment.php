<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ItemAssignment extends Model
{
    protected $fillable = ['bill_item_id', 'participant_id', 'qty_portion'];

    public function billItem()    { return $this->belongsTo(BillItem::class); }
    public function participant() { return $this->belongsTo(Participant::class); }
}
