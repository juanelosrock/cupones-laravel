<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointOfSale extends Model
{
    use SoftDeletes;

    protected $table = 'points_of_sale';
    protected $fillable = [
        'city_id', 'zone_id', 'name', 'code', 'address',
        'phone', 'contact_name', 'latitude', 'longitude', 'status',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function campaigns()
    {
        return $this->morphToMany(Campaign::class, 'locatable', 'campaign_locations');
    }
}