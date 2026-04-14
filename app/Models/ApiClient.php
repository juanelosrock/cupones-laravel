<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'description', 'environment',
        'client_id', 'client_secret',
        'allowed_ips', 'rate_limit_per_minute', 'permissions',
        'status', 'last_used_at', 'expires_at',
    ];

    protected $hidden = ['client_secret'];

    protected function casts(): array
    {
        return [
            'allowed_ips' => 'array',
            'permissions' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestLogs()
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    public static function generateCredentials(): array
    {
        $secret = Str::random(64);
        return [
            'client_id' => 'ch_' . Str::random(32),
            'client_secret' => $secret,
            'client_secret_hashed' => bcrypt($secret),
        ];
    }

    public function isAllowedIp(?string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }
        return in_array($ip, $this->allowed_ips);
    }

    public function hasPermission(string $perm): bool
    {
        if (empty($this->permissions)) {
            return false;
        }
        return in_array($perm, $this->permissions) || in_array('*', $this->permissions);
    }
}