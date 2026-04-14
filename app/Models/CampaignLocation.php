<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignLocation extends Model
{
    protected $fillable = ['campaign_id', 'locatable_type', 'locatable_id'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function locatable()
    {
        return $this->morphTo();
    }
}
