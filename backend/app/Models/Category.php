<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 
        'image', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Связи
    
    // Связь many-to-many с книгами
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category')
                    ->withTimestamps()
                    ->orderBy('title');
    }
    
    // Иерархические связи
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    // Аксессоры
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' → ' . $this->name;
        }
        return $this->name;
    }
    
    // Аксессор для получения полного URL изображения
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        
        return Storage::url($this->image);
    }
    
    // Получить все товары категории и подкатегории
    public function getAllBooksAttribute()
    {
        $categoryIds = $this->getAllChildrenIds();
        $categoryIds[] = $this->id;
        
        return Product::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $genreIds);
        })->get();
    }
    
    // Вспомогательный метод для получения ID всех подкатегорий
    private function getAllChildrenIds()
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
    }
    
    // Скопы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_id');
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}