<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helpers;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'product_image',
        'quantity',
        'price', // Lưu dạng string
    ];

    // Không cast price thành integer/float, giữ nguyên string
    protected $casts = [
        'quantity' => 'integer',
    ];

    // Accessor để lấy giá dạng số khi cần tính toán
    public function getPriceNumericAttribute()
    {
        return Helpers::parse($this->price);
    }

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Tính subtotal
    public function getSubtotalAttribute()
    {
        return $this->price_numeric * $this->quantity;
    }
}