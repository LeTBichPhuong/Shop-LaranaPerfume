<?php

if (!function_exists('parse_price')) {
    /**
     * Chuyển chuỗi tiền tệ Việt Nam thành số thực.
     *
     * @param string $priceString
     * @return float
     */
    function parse_price(string $priceString): float
    {
        // Loại bỏ dấu chấm, khoảng trắng, ký hiệu tiền tệ
        $number = str_replace(['.', ' ', '₫'], '', $priceString);
        return (float) $number;
    }
}

if (!function_exists('format_price')) {
    /**
     * Định dạng số thành chuỗi tiền tệ Việt Nam.
     *
     * @param float|int $number
     * @param bool $includeSymbol - có hiển thị ký hiệu ₫ hay không
     * @return string
     */
    function format_price(float|int $number, bool $includeSymbol = true): string
    {
        $formatted = number_format($number, 0, ',', '.');
        return $includeSymbol ? $formatted . ' ₫' : $formatted;
    }
}

/**
 * Ví dụ helper đơn giản để kiểm tra autoload có hoạt động không.
 */
if (!function_exists('example_helper')) {
    function example_helper(): string
    {
        return 'Laravel helper ready!';
    }
}
