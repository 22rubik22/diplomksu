<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 'product_id', 'color', 'size', 'quantity', 'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
    
    // Полное название товара с опциями
    public function getFullTitleAttribute()
    {
        $product = $this->product;
        if (!$product) {
            return 'Товар не найден';
        }
        
        $title = $product->title;
        $options = [];
        
        if ($this->color) {
            $options[] = $this->getColorLabelAttribute();
        }
        
        if ($this->size) {
            $options[] = $this->getSizeLabelAttribute();
        }
        
        if (!empty($options)) {
            $title .= ' (' . implode(', ', $options) . ')';
        }
        
        return $title;
    }
    
    // Аксессор для форматирования цвета
    public function getColorLabelAttribute()
    {
        if (!$this->color) {
            return null;
        }
        
        $colors = [
            'черный' => 'Черный',
            'коричневый' => 'Коричневый',
            'бежевый' => 'Бежевый',
            'белый' => 'Белый',
            'красный' => 'Красный',
            'синий' => 'Синий',
            'зеленый' => 'Зеленый',
            'желтый' => 'Желтый',
            'розовый' => 'Розовый',
            'фиолетовый' => 'Фиолетовый',
            'серый' => 'Серый',
            'оранжевый' => 'Оранжевый',
            'голубой' => 'Голубой',
            'бордовый' => 'Бордовый',
            'хаки' => 'Хаки',
        ];
        
        return $colors[$this->color] ?? ucfirst($this->color);
    }
    
    // Аксессор для форматирования размера
    public function getSizeLabelAttribute()
    {
        if (!$this->size) {
            return null;
        }
        
        $sizes = [
            'XS' => 'XS (Extra Small)',
            'S' => 'S (Small)',
            'M' => 'M (Medium)',
            'L' => 'L (Large)',
            'XL' => 'XL (Extra Large)',
            'XXL' => 'XXL (Double Extra Large)',
            'XXXL' => 'XXXL (Triple Extra Large)',
            'ONE_SIZE' => 'One Size',
            'FREE' => 'Free Size',
        ];
        
        return $sizes[$this->size] ?? $this->size;
    }
    
    // Проверка, есть ли опции у товара
    public function getHasOptionsAttribute()
    {
        return !empty($this->color) || !empty($this->size);
    }
    
    // Текстовое представление опций
    public function getOptionsTextAttribute()
    {
        $options = [];
        
        if ($this->color) {
            $options[] = $this->color_label;
        }
        
        if ($this->size) {
            $options[] = $this->size_label;
        }
        
        return implode(', ', $options);
    }
}