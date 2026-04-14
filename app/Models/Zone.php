<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['city_id', 'name', 'description', 'is_active'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function pointsOfSale()
    {
        return $this->hasMany(PointOfSale::class);
    }

    public function campaigns()
    {
        return $this->morphToMany(Campaign::class, 'locatable', 'campaign_locations');
    }
}