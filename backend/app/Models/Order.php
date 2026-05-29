<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'total_amount',
        'delivery_method', 'delivery_price', 'delivery_address', 'delivery_date',
        'payment_method', 'payment_status',
        'customer_name', 'customer_email', 'customer_phone',
        'comment', 'admin_comment'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_price' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    protected $with = ['items'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Статусы
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    public static function generateOrderNumber()
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}