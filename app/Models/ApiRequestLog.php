<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'api_client_id', 'endpoint', 'method', 'request_hash',
        'request_body', 'response_code', 'processing_ms', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_body' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class);
    }
}