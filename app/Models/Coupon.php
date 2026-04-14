<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public $timestamps = false;
    protected $fillable = ['batch_id', 'code', 'status', 'times_used', 'created_at'];

    public function batch()
    {
        return $this->belongsTo(CouponBatch::class, 'batch_id');
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $batch = $this->batch;
        if (!$batch || !$batch->isActive()) {
            return false;
        }
        if ($batch->code_type === 'unique' && $this->times_used >= 1) {
            return false;
        }
        if ($batch->max_uses_total && $batch->coupons()->sum('times_used') >= $batch->max_uses_total) {
            return false;
        }
        return true;
    }
}