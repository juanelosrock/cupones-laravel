<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppRecipient extends Model
{
    public $timestamps  = false;
    protected $fillable = [
        'whatsapp_campaign_id', 'customer_id', 'phone',
        'assigned_coupon_code', 'status', 'message_sent',
        'sent_at', 'error_message', 'provider_response', 'created_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function campaign() { return $this->belongsTo(WhatsAppCampaign::class, 'whatsapp_campaign_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
