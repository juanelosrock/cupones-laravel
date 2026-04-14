<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMeta extends Model
{
    protected $table = 'customer_meta';
    protected $fillable = ['customer_id', 'key', 'value'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}