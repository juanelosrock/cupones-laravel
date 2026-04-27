<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouponBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id', 'name', 'description', 'code_type', 'general_code',
        'prefix', 'quantity', 'discount_type', 'discount_value',
        'min_purchase_amount', 'max_purchase_amount', 'max_discount_amount',
        'max_uses_total', 'max_uses_per_user', 'max_uses_per_day',
        'start_date', 'end_date', 'is_combinable', 'applicable_to',
        'status', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'discount_value' => 'decimal:2',
            'min_purchase_amount'  => 'decimal:2',
            'max_purchase_amount'  => 'decimal:2',
            'max_discount_amount'  => 'decimal:2',
            'is_combinable' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'batch_id');
    }

    public function restrictions()
    {
        return $this->hasMany(CouponRestriction::class, 'batch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $this->status === 'active'
            && $this->start_date->toDateString() <= $today
            && $this->end_date->toDateString() >= $today;
    }

    public function getActiveCoupon(string $code): ?Coupon
    {
        return $this->coupons()
            ->where('code', $code)
            ->where('status', 'active')
            ->first();
    }
}