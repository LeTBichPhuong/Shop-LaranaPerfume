<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helpers;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_name', 'product_image', 'quantity', 'price'];

    /**
     * Mối quan hệ: Một OrderItem thuộc về một Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Accessor: Định dạng lại giá hiển thị theo kiểu "1.200.000 ₫"
     * - Luôn đảm bảo giá trị price được convert về số trước khi hiển thị
     */
    public function getPriceFormattedAttribute(): string
    {
        $price = Helpers::parse($this->price);
        return Helpers::format($price);
    }

    /**
     * Accessor: Tính thành tiền từng sản phẩm (price * quantity)
     */
    public function getSubtotalFormattedAttribute(): string
    {
        $price = Helpers::parse($this->price);
        $subtotal = $price * (int) $this->quantity;
        return Helpers::format($subtotal);
    }

    /**
     * Mutator: Trước khi lưu vào DB, luôn chuyển giá tiền thành số thực
     * (fix lỗi PostgreSQL invalid input syntax for numeric)
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = Helpers::parse($value); 
    }
}
