<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusUser extends Model
{
    use HasFactory;

    protected $table = 'bonus_user';

    protected $fillable = [
        'user_id',
        'bonus'
    ];

    protected $casts = [
        'bonus' => 'integer'
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Добавить бонусы
    public function addBonus(int $amount): bool
    {
        if ($amount <= 0) return false;
        
        $this->bonus += $amount;
        return $this->save();
    }

    // Списать бонусы
    public function deductBonus(int $amount): bool
    {
        if ($amount <= 0 || $this->bonus < $amount) return false;
        
        $this->bonus -= $amount;
        return $this->save();
    }

    // Проверить, хватает ли бонусов
    public function hasEnoughBonus(int $amount): bool
    {
        return $this->bonus >= $amount;
    }
}