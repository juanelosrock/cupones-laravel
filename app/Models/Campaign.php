<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'type', 'start_date',
        'end_date', 'budget', 'status', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function couponBatches()
    {
        return $this->hasMany(CouponBatch::class);
    }

    public function smsCampaigns()
    {
        return $this->hasManyThrough(SmsCampaign::class, CouponBatch::class, 'campaign_id', 'coupon_batch_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'campaign_customers')
            ->withPivot('source', 'import_batch', 'created_at')
            ->using(CampaignCustomer::class);
    }

    public function campaignCustomers()
    {
        return $this->hasMany(CampaignCustomer::class);
    }

    public function locations()
    {
        return $this->hasMany(CampaignLocation::class);
    }

    public function zones()
    {
        return $this->morphedByMany(Zone::class, 'locatable', 'campaign_locations');
    }

    public function pointsOfSale()
    {
        return $this->morphedByMany(PointOfSale::class, 'locatable', 'campaign_locations');
    }
}