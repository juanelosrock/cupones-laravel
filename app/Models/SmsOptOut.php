<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsOptOut extends Model
{
    public $timestamps = false;
    protected $table = 'sms_opt_outs';
    protected $fillable = ['phone', 'reason', 'created_at'];

    public static function isOptedOut(string $phone): bool
    {
        return static::where('phone', $phone)->exists();
    }
}