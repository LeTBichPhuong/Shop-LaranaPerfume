<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Routing\Controller;

class AdminOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc');

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            $result = $orders->get();
            return response()->json(['data' => $result]);
        }

        $orders = $orders->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:chờ xử lý,đã xác nhận,đang giao,đã giao,đã hủy',
        ], [
            'status.required' => 'Trạng thái là trường bắt buộc.',
            'status.in' => 'Trạng thái phải là một trong các giá trị: chờ xử lý, đã xác nhận, đang giao, đã giao, đã hủy.',
        ]);
        
        $order->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!',
            'data' => $order->load('user')
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa đơn hàng thành công!'
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Xóa đơn hàng thành công!');
    }

    public function show(Order $order)
    {
        $order->load('items', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function export(Request $request)
    {
        $orders = Order::with('user')->get();
        $filename = 'don-hang-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Khách Hàng', 'Tổng Tiền', 'Trạng Thái', 'Ngày Tạo']);
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->user->name ?? 'N/A',
                    number_format((float)$order->total) . ' ₫',
                    ucfirst($order->status),
                    $order->created_at->format('d/m/Y H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}