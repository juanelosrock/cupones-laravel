<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'name', 'sku', 'description', 'price', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}