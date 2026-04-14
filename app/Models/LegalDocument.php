<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $fillable = ['type', 'title', 'content', 'version', 'is_active', 'published_at', 'created_by_user_id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function acceptances()
    {
        return $this->hasMany(DocumentAcceptance::class);
    }

    public static function getActive(string $type): ?self
    {
        return static::where('type', $type)->where('is_active', true)->latest()->first();
    }
}