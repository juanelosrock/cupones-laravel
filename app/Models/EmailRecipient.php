<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailRecipient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_campaign_id', 'customer_id', 'email',
        'status', 'subject_sent', 'message_sent', 'assigned_coupon_code',
        'sent_at', 'error_message', 'provider_response', 'created_at',
    ];

    protected $casts = [
        'sent_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function emailCampaign()
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
