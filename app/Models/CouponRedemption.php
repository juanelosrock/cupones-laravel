<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'coupon_id', 'customer_id', 'user_id', 'order_reference',
        'original_amount', 'discount_applied', 'final_amount',
        'channel', 'ip_address', 'user_agent', 'metadata',
        'redeemed_at', 'reversed_at', 'reversed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'discount_applied' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'metadata' => 'array',
            'redeemed_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }
}