<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsRecipient extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'sms_campaign_id', 'customer_id', 'phone',
        'consent_token', 'consent_accepted_at', 'assigned_coupon_code', 'acceptance_ip',
        'status', 'message_sent', 'sent_at', 'error_message', 'provider_response', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'             => 'datetime',
            'consent_accepted_at' => 'datetime',
        ];
    }

    public function hasAcceptedConsent(): bool
    {
        return $this->consent_accepted_at !== null;
    }

    public function campaign()
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}