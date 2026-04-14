<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'is_active'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function pointsOfSale()
    {
        return $this->hasMany(PointOfSale::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}