<?php

namespace App\Exports;  

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;             

class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->orders->map(function ($order) {
            $customer = 'N/A';
            if ($order->relationLoaded('user') && $order->user) {
                $customer = $order->user->username ?? $order->user->email ?? 'N/A';
            } elseif (isset($order->customer_name)) {
                $customer = $order->customer_name;
            }
            return [
                'ID' => $order->id,
                'Khách Hàng' => $customer,
                'Tổng Tiền' => $order->total,
                'Trạng Thái' => $order->status,
                'Ngày Tạo' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Định nghĩa headings cho Excel
     */
    public function headings(): array
    {
        return [
            'ID',
            'Khách Hàng',
            'Tổng Tiền',
            'Trạng Thái',
            'Ngày Tạo',
        ];
    }
}