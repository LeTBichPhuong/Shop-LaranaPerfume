<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_name', 'product_image', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessor price formatted (fix thừa 00)
    public function getPriceFormattedAttribute()
    {
        $parsedPrice = $this->parsePrice($this->price);
        return number_format($parsedPrice, 0, ',', '.') . ' ₫';
    }

    // Accessor subtotal formatted
    public function getSubtotalFormattedAttribute()
    {
        $parsedPrice = $this->parsePrice($this->price);
        $subtotal = $parsedPrice * $this->quantity;
        return number_format($subtotal, 0, ',', '.') . ' ₫';
    }

    // Helper parse price
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