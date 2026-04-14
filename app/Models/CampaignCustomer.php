<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampaignCustomer extends Pivot
{
    protected $table = 'campaign_customers';

    public $timestamps = false;

    protected $fillable = ['campaign_id', 'customer_id', 'source', 'import_batch'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
