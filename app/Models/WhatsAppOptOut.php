<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppOptOut extends Model
{
    protected $table    = 'whatsapp_opt_outs';
    protected $fillable = ['phone', 'reason'];

    public static function isOptedOut(string $phone): bool
    {
        return static::where('phone', $phone)->exists();
    }
}
