<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    protected $fillable = ['name', 'driver', 'config', 'is_active', 'priority'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}