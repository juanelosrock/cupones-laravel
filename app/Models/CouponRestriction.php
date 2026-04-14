<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRestriction extends Model
{
    public $timestamps = false;
    protected $fillable = ['batch_id', 'entity_type', 'entity_id', 'created_at'];

    public function batch()
    {
        return $this->belongsTo(CouponBatch::class, 'batch_id');
    }
}