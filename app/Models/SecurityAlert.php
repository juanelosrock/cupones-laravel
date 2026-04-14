<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'type', 'severity', 'description', 'context',
        'ip_address', 'resolved_at', 'resolved_by_user_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}