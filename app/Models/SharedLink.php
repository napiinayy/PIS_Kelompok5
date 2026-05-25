<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SharedLink extends Model
{
    protected $fillable = ['bill_id', 'token', 'is_active', 'expires_at'];
    protected $casts    = ['is_active' => 'boolean', 'expires_at' => 'datetime'];

    public function bill() { return $this->belongsTo(Bill::class); }

    public static function generateFor(Bill $bill): self
    {
        $bill->sharedLinks()->update(['is_active' => false]);
        return self::create([
            'bill_id'   => $bill->id,
            'token'     => Str::random(48),
            'is_active' => true,
        ]);
    }

    public function isValid(): bool
    {
        return $this->is_active && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
