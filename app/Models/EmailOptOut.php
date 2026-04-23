<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOptOut extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'reason', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public static function isOptedOut(string $email): bool
    {
        return static::where('email', strtolower(trim($email)))->exists();
    }
}
