<?php

namespace App\Http\Controllers;

use App\Services\ChatbotKnowledgeService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const HISTORY_SESSION_KEY = 'chatbot.history';

    public function __construct(
        private readonly ChatbotKnowledgeService $knowledgeService
    ) {
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'current_path' => 'nullable|string|max:500',
        ]);

        try {
            $message = trim((string) $request->input('message'));
            $currentPath = $request->input('current_path');

            if ($quickReply = $this->quickReply($message)) {
                return response()->json([
                    'success' => true,
                    'message' => $quickReply,
                ]);
            }

            if ($featureReply = $this->featureQuickReply($message)) {
                return response()->json([
                    'success' => true,
                    'message' => $featureReply,
                ]);
            }

            $apiKey = (string) env('GROQ_API_KEY');
            $apiUrl = (string) config('chatbot.api_url');

            if ($apiKey === '' || $apiUrl === '') {
                Log::warning('Groq chatbot is not configured properly.');

                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, konfigurasi chatbot belum lengkap.',
                ], 500);
            }

            $knowledge = $this->knowledgeService->buildVisitorKnowledge($message, $currentPath);
            $history = $this->getHistoryMessages();
            $userPrompt = $this->buildUserPrompt(
                $message,
                $currentPath
            );

            $messages = array_merge(
                [[
                    'role' => 'system',
                    'content' => $this->buildSystemPrompt($knowledge),
                ]],
                $history,
                [[
                    'role' => 'user',
                    'content' => $userPrompt,
                ]]
            );

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout((int) config('chatbot.timeout_seconds', 12))
                ->post($apiUrl, [
                    'model' => (string) config('chatbot.model', 'llama-3.1-8b-instant'),
                    'messages' => $messages,
                    'temperature' => (float) config('chatbot.temperature', 0.2),
                    'max_tokens' => (int) config('chatbot.max_tokens', 300),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $assistantMessage = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

                if ($assistantMessage === '') {
                    $assistantMessage = 'Maaf, saya belum bisa memberikan jawaban yang tepat saat ini.';
                }

                $this->storeHistory($history, $userPrompt, $assistantMessage);

                return response()->json([
                    'success' => true,
                    'message' => $assistantMessage,
                ]);
            }

            Log::error('Groq API response error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

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

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function getHistoryMessages(): array
    {
        $history = session(self::HISTORY_SESSION_KEY, []);
        if (!is_array($history)) {
            return [];
        }

        $maxMessages = max(0, (int) config('chatbot.history.max_messages', 6));
        $normalized = array_values(array_filter($history, function ($item): bool {
            return is_array($item)
                && isset($item['role'], $item['content'])
                && is_string($item['role'])
                && is_string($item['content'])
                && in_array($item['role'], ['user', 'assistant'], true);
        }));

        if ($maxMessages === 0) {
            return [];
        }

        if (count($normalized) > $maxMessages) {
            $normalized = array_slice($normalized, -$maxMessages);
        }

        return $normalized;
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function storeHistory(array $history, string $userPrompt, string $assistantMessage): void
    {
        $history[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];
        $history[] = [
            'role' => 'assistant',
            'content' => $assistantMessage,
        ];

        $maxMessages = max(2, (int) config('chatbot.history.max_messages', 6));
        if (count($history) > $maxMessages) {
            $history = array_slice($history, -$maxMessages);
        }

        session()->put(self::HISTORY_SESSION_KEY, $history);
    }

    private function buildSystemPrompt(string $knowledge): string
    {
        return implode("\n", [
            'Anda adalah asisten virtual website Kampung Telaga Air untuk pengguna wisatawan.',
            'Gunakan Bahasa Indonesia yang jelas, ramah, dan faktual.',
            'Jawab ringkas, maksimal 5 kalimat kecuali pengguna meminta detail.',
            'Fokus hanya pada fitur wisatawan. Jika ditanya area admin, jawab bahwa chatbot ini hanya untuk wisatawan.',
            'Gunakan konteks website di bawah ini sebagai sumber utama.',
            'Jika jawaban tidak ada di konteks, jelaskan keterbatasan dan arahkan pengguna ke halaman terkait.',
            '',
            $knowledge,
        ]);
    }

    private function buildUserPrompt(string $message, ?string $currentPath): string
    {
        $lines = [
            'Pertanyaan pengguna: ' . trim($message),
        ];

        $path = trim((string) $currentPath);
        if ($path !== '') {
            $lines[] = 'Halaman aktif pengguna: ' . $path;
        }

        return implode("\n", $lines);
    }

    private function quickReply(string $message): ?string
    {
        $lower = $this->normalizeText($message);

        if (in_array($lower, ['halo', 'hai', 'hi', 'hello', 'pagi', 'siang', 'sore', 'malam'], true)) {
            return 'Halo, saya siap bantu info seputar paket wisata, destinasi, homestay, culinary, kiosk, checkout, dan order Anda.';
        }

        if (in_array($lower, ['terima kasih', 'makasih', 'thanks', 'thx'], true)) {
            return 'Sama-sama. Kalau mau, saya bisa lanjut bantu pilih paket yang paling cocok.';
        }

        return null;
    }

    private function featureQuickReply(string $message): ?string
    {
        $normalized = $this->normalizeText($message);

        if ($this->containsAny($normalized, ['checkout', 'cara checkout', 'cara bayar', 'pembayaran', 'payment'])) {
            return implode("\n", [
                'Cara checkout:',
                '1. Login akun wisatawan.',
                '2. Pilih paket lalu tambah ke keranjang di /cart.',
                '3. Buka /checkout, isi data pemesan.',
                '4. Lanjutkan pembayaran dengan kartu melalui Stripe.',
                '5. Lihat status pesanan di /orders.',
            ]);
        }

        if ($this->containsAny($normalized, ['keranjang', 'cart', 'tambah paket'])) {
            return 'Untuk tambah paket ke keranjang, login dulu lalu klik tombol add to cart di detail paket. Keranjang bisa dicek di /cart.';
        }

        if ($this->containsAny($normalized, ['metode bayar', 'metode pembayaran', 'bayar apa aja', 'payment method'])) {
            return 'Metode pembayaran yang tersedia saat ini adalah kartu kredit/debit melalui Stripe.';
        }

        if ($this->containsAny($normalized, ['refund', 'pengembalian dana'])) {
            return 'Refund bisa diajukan dari detail order, tetapi hanya untuk order dengan status paid. Setelah diajukan, status menunggu persetujuan admin.';
        }

        if ($this->containsAny($normalized, ['riwayat order', 'order history', 'pesanan saya'])) {
            return 'Riwayat pesanan bisa dilihat di /orders setelah login.';
        }

        if ($this->containsAny($normalized, ['login', 'daftar', 'register akun'])) {
            return 'Login wisatawan: /visitor/login-visitor. Daftar akun baru: /visitor/register.';
        }

        return null;
    }

    private function normalizeText(string $message): string
    {
        $normalized = Str::of($message)
            ->lower()
            ->replaceMatches('/[^\pL\pN\s]/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        return $normalized;
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
