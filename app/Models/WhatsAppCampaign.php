<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppCampaign extends Model
{
    use SoftDeletes;

    protected $table    = 'whatsapp_campaigns';
    protected $fillable = [
        'name', 'campaign_id', 'coupon_batch_id',
        'message_template', 'filters',
        'total_recipients', 'sent_count', 'failed_count', 'status',
        'scheduled_at', 'started_at', 'finished_at', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'filters'      => 'array',
            'scheduled_at' => 'datetime',
            'started_at'   => 'datetime',
            'finished_at'  => 'datetime',
        ];
    }

    public function campaign()    { return $this->belongsTo(Campaign::class); }
    public function couponBatch() { return $this->belongsTo(CouponBatch::class); }
    public function recipients()  { return $this->hasMany(WhatsAppRecipient::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
