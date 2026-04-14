<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Campaign;
use App\Models\CouponBatch;

class SmsCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id', 'landing_config_id', 'name', 'coupon_batch_id', 'send_consent_link',
        'message_template', 'filters',
        'total_recipients', 'sent_count', 'failed_count', 'status',
        'scheduled_at', 'started_at', 'finished_at', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'filters'            => 'array',
            'send_consent_link'  => 'boolean',
            'scheduled_at'       => 'datetime',
            'started_at'         => 'datetime',
            'finished_at'        => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function couponBatch()
    {
        return $this->belongsTo(CouponBatch::class);
    }

    public function recipients()
    {
        return $this->hasMany(SmsRecipient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function landingConfig()
    {
        return $this->belongsTo(LandingPageConfig::class, 'landing_config_id');
    }
}