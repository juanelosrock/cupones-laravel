<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'api_client_id',
        'phone', 'email', 'email_subject',
        'sms_status', 'email_status',
        'sms_message_id', 'email_message_id',
        'sms_error', 'email_error',
        'ip_address', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class);
    }
}
