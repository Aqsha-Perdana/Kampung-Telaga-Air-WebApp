<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('GROQ_API_URL'), [
                'model' => 'llama-3.3-70b-versatile', // atau model lain: mixtral-8x7b-32768
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten virtual yang membantu pengunjung website travel dan homestay. Jawab dengan ramah dan informatif dalam Bahasa Indonesia.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $request->message
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => $data['choices'][0]['message']['content'] ?? 'Maaf, tidak ada respons.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Groq API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Maaf, layanan chatbot sedang bermasalah.'
            ], 500);
        }
    }
}
