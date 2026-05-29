<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'product_title', 'product_brand',
        'color', 'size', 'price', 'quantity', 'total'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // Аксессоры для форматирования
    public function getColorLabelAttribute()
    {
        if (!$this->color) {
            return '—';
        }
        
        $colors = [
            'черный' => 'Черный',
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
        ];
        
        return $colors[$this->color] ?? ucfirst($this->color);
    }
    
    public function getSizeLabelAttribute()
    {
        if (!$this->size) {
            return '—';
        }
        
        $sizes = [
            'XS' => 'XS (Extra Small)',
            'S' => 'S (Small)',
            'M' => 'M (Medium)',
            'L' => 'L (Large)',
            'XL' => 'XL (Extra Large)',
            'XXL' => 'XXL (Double Extra Large)',
            'ONE_SIZE' => 'One Size',
        ];
        
        return $sizes[$this->size] ?? $this->size;
    }
    
    // Полное название товара с опциями
    public function getFullTitleAttribute()
    {
        $title = $this->product_title;
        $options = [];
        
        if ($this->color) {
            $options[] = $this->color_label;
        }
        
        if ($this->size) {
            $options[] = $this->size_label;
        }
        
        if (!empty($options)) {
            $title .= ' (' . implode(', ', $options) . ')';
        }
        
        return $title;
    }
}