<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public $timestamps      = false;
    public $incrementing    = false;
    protected $primaryKey   = 'key';
    protected $keyType      = 'string';

    protected $fillable = ['key', 'value', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        if (!$row) return $default;

        return $row->is_encrypted
            ? rescue(fn() => Crypt::decryptString($row->value), $default)
            : $row->value;
    }

    public static function set(string $key, mixed $value, bool $encrypt = false): void
    {
        static::updateOrCreate(['key' => $key], [
            'value'        => $encrypt ? Crypt::encryptString((string) $value) : $value,
            'is_encrypted' => $encrypt,
        ]);
    }

    public static function setMany(array $values, array $encryptKeys = []): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value, in_array($key, $encryptKeys));
        }
    }
}
