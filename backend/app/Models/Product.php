<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'brand_id', 'description',
        'publication_year', 'country',
        'consist', 'weight', 'price', 'old_price', 'quantity',
        'color', 'size', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'color' => 'array', // Автоматически преобразует JSON в массив PHP
        'size' => 'array',   // Автоматически преобразует JSON в массив PHP
    ];

    // Связи
    
    // Связь many-to-many с категориями
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category')
                    ->withTimestamps()
                    ->orderBy('categories.name');
    }
    
    // Вспомогательный метод для обратной совместимости (если где-то используется $book->genre)
    public function getCategoryAttribute()
    {
        return $this->categories->first();
    }
    
    // Связь с брендом (исправлено с brands() на brand())
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    
    // Для обратной совместимости, если где-то используется $product->brands()
    public function brands()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Аксессоры
    public function getCoverImageAttribute()
    {
        $primary = $this->primaryImage;
        return $primary ? $primary->image_path : '/images/default-product.jpg';
    }

    public function getDiscountPercentAttribute()
    {
        if ($this->old_price && $this->old_price > $this->price) {
            return round((($this->old_price - $this->price) / $this->old_price) * 100);
        }
        return 0;
    }

    public function getIsInStockAttribute()
    {
        return $this->quantity > 0;
    }
    
    // Аксессоры для color и size
    public function getColorListAttribute()
    {
        if (empty($this->color)) {
            return [];
        }
        return is_array($this->color) ? $this->color : json_decode($this->color, true);
    }
    
    public function getSizeListAttribute()
    {
        if (empty($this->size)) {
            return [];
        }
        return is_array($this->size) ? $this->size : json_decode($this->size, true);
    }
    
    public function getColorTextAttribute()
    {
        $colors = $this->getColorListAttribute();
        return implode(', ', $colors);
    }
    
    public function getSizeTextAttribute()
    {
        $sizes = $this->getSizeListAttribute();
        return implode(', ', $sizes);
    }

    // Скопы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
    
    // Скоп для фильтрации по цветам
    public function scopeByColors($query, $colors)
    {
        if (empty($colors)) {
            return $query;
        }
        
        $colors = is_array($colors) ? $colors : [$colors];
        
        return $query->where(function($q) use ($colors) {
            foreach ($colors as $color) {
                $q->orWhereJsonContains('color', $color);
            }
        });
    }
    
    // Скоп для фильтрации по размерам
    public function scopeBySizes($query, $sizes)
    {
        if (empty($sizes)) {
            return $query;
        }
        
        $sizes = is_array($sizes) ? $sizes : [$sizes];
        
        return $query->where(function($q) use ($sizes) {
            foreach ($sizes as $size) {
                $q->orWhereJsonContains('size', $size);
            }
        });
    }

    // Обновленный скоп для фильтрации по категориям (поддерживает массив ID)
    public function scopeByCategories($query, $categoryIds)
    {
        if (empty($categoryIds)) {
            return $query;
        }
        
        $categoryIds = is_array($categoryIds) ? $categoryIds : [$categoryIds];
        
        return $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }
    
    // Вспомогательный метод для получения строки с названиями категорий
    public function getCategoriesListAttribute()
    {
        return $this->categories->pluck('name')->implode(', ');
    }
}