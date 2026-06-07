<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'photo', 'desc','country','year','url_web'
    ];

    // Связь "один ко многим" (у бренда много товаров)
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

}