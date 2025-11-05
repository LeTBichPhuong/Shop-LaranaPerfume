<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'payment_method',
        'transaction_code',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Quan hệ với OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor để format tổng tiền 
    public function getTotalFormattedAttribute()
    {
        $parsedTotal = $this->parsePrice($this->total);
        return number_format($parsedTotal, 0, ',', '.') . ' ₫';  
    }

    private function parsePrice($priceString)
    {
        $cleanPrice = preg_replace('/[^\d]/', '', (string) $priceString);
        $price = (float) $cleanPrice;

        if ($price >= 10000000 && $price % 100 === 0) {
            $price /= 100;
        }

        return $price;
    }
}