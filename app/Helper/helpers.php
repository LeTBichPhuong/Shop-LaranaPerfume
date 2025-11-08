<?php

namespace App\Helper;

/**
 * Class Helpers
 *
 * Chứa các hàm tiện ích (helper) dùng chung toàn dự án.
 * Cách gọi:
 *    use App\Helper\Helpers;
 *    Helpers::parse('1.200.000 ₫');
 *    Helpers::format(1200000);
 */
class Helpers
{
    /**
     * Chuyển chuỗi giá tiền Việt Nam thành số thực (float)
     *
     *
     * @param string $priceString
     * @return float
     */
    public static function parse(string $priceString): float
    {
        // Loại bỏ các ký tự không cần thiết như dấu chấm, khoảng trắng, ký hiệu tiền
        $number = str_replace(['.', ',', ' ', '₫', 'đ', 'VNĐ', 'vnd'], '', $priceString);

        // Nếu chuỗi rỗng hoặc không phải số, trả về 0.0
        if (!is_numeric($number)) {
            return 0.0;
        }

        return (float) $number;
    }

    /**
     * Định dạng số thành chuỗi giá tiền Việt Nam
     *
     *
     * @param float|int $number
     * @param bool $includeSymbol - có hiển thị ký hiệu tiền hay không
     * @return string
     */
    public static function format(float|int $number, bool $includeSymbol = true): string
    {
        $formatted = number_format($number, 0, ',', '.');
        return $includeSymbol ? "{$formatted} ₫" : $formatted;
    }

    /**
     * Kiểm tra xem chuỗi có chứa ký hiệu tiền tệ hay không
     *
     * @param string $value
     * @return bool
     */
    public static function hasCurrencySymbol(string $value): bool
    {
        return str_contains($value, '₫') || str_contains($value, 'VNĐ') || str_contains($value, 'vnd');
    }

    /**
     * Debug helper — chỉ dùng khi cần test
     *
     * @return string
     */
    public static function test(): string
    {
        return "Helper hoạt động tốt!";
    }
}
