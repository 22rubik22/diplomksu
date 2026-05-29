<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'phone', 
        'address_line', 
        'city',
        'role',
        'yandex_id',      // Добавлено
        'avatar',         // Добавлено
        'email_verified_at' // Добавлено
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    // Связи
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(Book::class, 'favorites')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function bookcrossingBooks()
    {
        return $this->hasMany(BookcrossingBook::class, 'owner_id');
    }

    public function takenBooks()
    {
        return $this->hasMany(BookcrossingBook::class, 'taken_by');
    }
    
    public function bonus()
    {
        return $this->hasOne(BonusUser::class);
    }

    // Проверки ролей
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->isAdmin();
    }
    
    // Получить количество бонусов пользователя
    public function getBonusAttribute()
    {
        return $this->bonus?->bonus ?? 0;
    }
    
    // Проверка, авторизован ли пользователь через Яндекс
    public function isYandexUser(): bool
    {
        return !is_null($this->yandex_id);
    }
    
    // Получить URL аватара (с fallback)
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }
        
        // Генерируем gravatar URL на основе email
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mm&s=200";
    }
    
    // Scope для поиска по yandex_id
    public function scopeWhereYandexId($query, $yandexId)
    {
        return $query->where('yandex_id', $yandexId);
    }
    
    // Scope для верифицированных пользователей
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}