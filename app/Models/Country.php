<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'phone_code', 'is_active'];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}