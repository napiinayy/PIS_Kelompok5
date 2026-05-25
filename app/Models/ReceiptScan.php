<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReceiptScan extends Model
{
    protected $fillable = ['bill_id', 'image_path', 'raw_ocr_result', 'status', 'error_message'];
    protected $casts    = ['raw_ocr_result' => 'array'];

    public function bill() { return $this->belongsTo(Bill::class); }
}
