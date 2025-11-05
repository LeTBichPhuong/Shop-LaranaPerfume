<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function send(Request $request)
    {
        $msg = trim($request->message);

        if (!$msg) {
            return response()->json(['reply' => 'Bạn chưa nhập nội dung nào']);
        }

        // Rate limiting
        $ip = $request->ip();
        $key = "chatbot_limit_{$ip}";
        
        if (Cache::has($key) && Cache::get($key) >= 5) {
            return response()->json([
                'reply' => 'Bạn đang gửi tin nhắn quá nhanh. Vui lòng đợi 1 phút.'
            ]);
        }

        Cache::put($key, Cache::get($key, 0) + 1, now()->addMinute());

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Bạn là trợ lý Larana Perfume — chuyên gia tư vấn nước hoa. Giúp khách hàng: chọn mùi hương phù hợp, giới thiệu sản phẩm, tư vấn cách sử dụng. Luôn trả lời bằng tiếng Việt, thân thiện và chuyên nghiệp.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $msg
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'Xin lỗi, tôi không thể trả lời lúc này';
                return response()->json(['reply' => trim($reply)]);
            }

            return response()->json(['reply' => 'Hệ thống đang bận, vui lòng thử lại sau!']);

        } catch (\Exception $e) {
            return response()->json(['reply' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại!']);
        }
    }
}