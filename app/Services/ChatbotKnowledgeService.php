<?php

namespace App\Services;

use App\Models\Culinary;
use App\Models\Destinasi;
use App\Models\Homestay;
use App\Models\Kiosk;
use App\Models\PaketWisata;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatbotKnowledgeService
{
    /**
     * Build context khusus sesuai topik pertanyaan user agar prompt lebih kecil dan cepat.
     */
    public function buildVisitorKnowledge(?string $question = null, ?string $currentPath = null): string
    {
        $cacheMinutes = max(1, (int) config('chatbot.knowledge.cache_minutes', 5));
        $topics = $this->detectTopics($question, $currentPath);
        sort($topics);
        $cacheKey = 'chatbot.visitor_knowledge.v2.' . md5(implode('|', $topics));

        return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($topics): string {
            try {
                return $this->composeKnowledge($topics);
            } catch (Throwable $exception) {
                Log::warning('Failed to build chatbot visitor knowledge', [
                    'error' => $exception->getMessage(),
                ]);

                return $this->fallbackKnowledge();
            }
        });
    }

    /**
     * @param array<int, string> $topics
     */
    private function composeKnowledge(array $topics): string
    {
        $sections = [
            'KONTEKS WEBSITE WISATAWAN KAMPUNG TELAGA AIR',
            'Data disinkronkan: ' . now()->format('Y-m-d H:i:s'),
            $this->buildNavigationSection(),
            $this->buildFeatureRulesSection(),
        ];

        if (in_array('destinasi', $topics, true)) {
            $sections[] = $this->buildDestinationSection();
        }

        if (in_array('paket', $topics, true)) {
            $sections[] = $this->buildPackageSection();
        }

        if (in_array('homestay', $topics, true)) {
            $sections[] = $this->buildHomestaySection();
        }

        if (in_array('culinary', $topics, true)) {
            $sections[] = $this->buildCulinarySection();
        }

        if (in_array('kiosk', $topics, true)) {
            $sections[] = $this->buildKioskSection();
        }

        return implode("\n\n", array_filter($sections));
    }

    private function fallbackKnowledge(): string
    {
        return implode("\n", [
            'KONTEKS WEBSITE WISATAWAN KAMPUNG TELAGA AIR',
            '- Website ini memiliki menu utama: Home, Tour Package, Culinary, Kiosk, Homestay, Cart, Checkout, dan Order History.',
            '- Fitur admin bukan bagian dari konteks chatbot ini.',
            '- Jika data detail item tidak tersedia, minta pengguna membuka halaman katalog terkait.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function detectTopics(?string $question, ?string $currentPath): array
    {
        $text = strtolower(trim((string) $question . ' ' . (string) $currentPath));
        $topics = [];

        if ($this->containsAny($text, ['paket', 'tour', 'trip', 'wisata', 'itinerary'])) {
            $topics[] = 'paket';
        }

        if ($this->containsAny($text, ['destinasi', 'destination', '360', 'view360', 'galeri'])) {
            $topics[] = 'destinasi';
        }

        if ($this->containsAny($text, ['homestay', 'penginapan', 'kamar'])) {
            $topics[] = 'homestay';
        }

        if ($this->containsAny($text, ['culinary', 'kuliner', 'makan', 'food', 'restaurant'])) {
            $topics[] = 'culinary';
        }

        if ($this->containsAny($text, ['kiosk', 'kedai', 'booth'])) {
            $topics[] = 'kiosk';
        }

        if ($this->containsAny($text, ['/homestay', '/culinary', '/kiosk', '/tour-package', '/package-tour', '/destination'])) {
            if (str_contains($text, '/homestay')) {
                $topics[] = 'homestay';
            }
            if (str_contains($text, '/culinary')) {
                $topics[] = 'culinary';
            }
            if (str_contains($text, '/kiosk')) {
                $topics[] = 'kiosk';
            }
            if (str_contains($text, '/tour-package') || str_contains($text, '/package-tour')) {
                $topics[] = 'paket';
            }
            if (str_contains($text, '/destination') || str_contains($text, '/view360')) {
                $topics[] = 'destinasi';
            }
        }

        if (empty($topics)) {
            // Default ringan: topik paling umum untuk pertanyaan umum.
            $topics = ['paket', 'destinasi'];
        }

        return array_values(array_unique($topics));
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

    private function buildNavigationSection(): string
    {
        $items = [
            'Home: /',
            'Daftar destinasi: /destination dan detail /destination/{id}',
            'Daftar paket wisata: /tour-package dan detail /tour-package/{id}',
            'Halaman paket alternatif: /package-tour',
            'Daftar culinary: /culinary dan detail /culinary/{id_culinary}',
            'Daftar kiosk: /kiosk dan detail /kiosk/{id_kiosk}',
            'Daftar homestay: /homestay dan detail /homestay/{id_homestay}',
            'Galeri 360: /view360/{id_footage360}',
            'Keranjang: /cart',
            'Checkout (perlu login): /checkout',
            'Riwayat pesanan (perlu login): /orders',
        ];

        return "Navigasi wisatawan:\n- " . implode("\n- ", $items);
    }

    private function buildFeatureRulesSection(): string
    {
        $items = [
            'Tambah ke cart memerlukan akun wisatawan login.',
            'Tanggal keberangkatan saat tambah/update cart minimal 3 hari dari hari ini.',
            'Checkout saat ini hanya mendukung pembayaran kartu melalui Stripe.',
            'Mata uang display checkout: MYR, USD, IDR, SGD, EUR, GBP, AUD, JPY, CNY.',
            'Status order yang umum: pending, paid, failed, cancelled, refund_requested, refunded.',
            'Refund hanya dapat diajukan untuk order dengan status paid.',
            'Chatbot ini fokus menjawab fitur wisatawan, bukan admin panel.',
        ];

        return "Aturan fitur wisatawan:\n- " . implode("\n- ", $items);
    }

    private function buildDestinationSection(): string
    {
        try {
            $limit = $this->itemLimit();
            $total = Destinasi::count();
            $destinations = Destinasi::query()
                ->select(['id_destinasi', 'nama', 'lokasi', 'deskripsi'])
                ->withCount([
                    'footage360 as active_360_count' => function ($query) {
                        $query->where('is_active', true);
                    },
                ])
                ->orderBy('nama')
                ->limit($limit)
                ->get();

            $lines = [
                sprintf('Destinasi (%d total, %d ditampilkan):', $total, $destinations->count()),
            ];

            foreach ($destinations as $destination) {
                $lines[] = sprintf(
                    '- %s | %s | lokasi: %s | 360 aktif: %d | deskripsi: %s',
                    $destination->id_destinasi,
                    $destination->nama,
                    $this->safeValue($destination->lokasi),
                    (int) $destination->active_360_count,
                    $this->cleanText($destination->deskripsi)
                );
            }

            if ($total > $destinations->count()) {
                $lines[] = sprintf('- Dan %d destinasi lainnya.', $total - $destinations->count());
            }

            return implode("\n", $lines);
        } catch (Throwable $exception) {
            Log::warning('Failed to load destination section for chatbot', ['error' => $exception->getMessage()]);
            return 'Destinasi: data tidak tersedia sementara.';
        }
    }

    private function buildPackageSection(): string
    {
        try {
            $limit = $this->itemLimit();
            $total = PaketWisata::where('status', 'aktif')->count();
            $packages = PaketWisata::query()
                ->select(['id_paket', 'nama_paket', 'durasi_hari', 'deskripsi', 'harga_jual', 'harga_final', 'status'])
                ->where('status', 'aktif')
                ->with([
                    'destinasis:id_destinasi,nama',
                    'homestays:id_homestay,nama',
                    'boats:id,nama',
                    'kiosks:id_kiosk,nama',
                    'paketCulinaries:id,id_culinary,nama_paket',
                    'paketCulinaries.culinary:id_culinary,nama',
                ])
                ->orderBy('nama_paket')
                ->limit($limit)
                ->get();

            $lines = [
                sprintf('Paket wisata aktif (%d total, %d ditampilkan):', $total, $packages->count()),
            ];

            foreach ($packages as $package) {
                $packageCulinaries = $package->paketCulinaries
                    ->map(function ($item): string {
                        $culinaryName = $item->culinary?->nama;
                        if ($culinaryName) {
                            return $culinaryName . ' (' . $item->nama_paket . ')';
                        }

                        return $item->nama_paket ?? '-';
                    })
                    ->all();

                $price = $package->harga_final ?? $package->harga_jual ?? 0;

                $lines[] = sprintf(
                    '- %s | %s | %d hari | harga: %s | destinasi: %s | homestay: %s | culinary: %s | boat: %s | kiosk: %s',
                    $package->id_paket,
                    $package->nama_paket,
                    (int) $package->durasi_hari,
                    $this->formatMyr($price),
                    $this->implodeLimited($package->destinasis->pluck('nama')->all()),
                    $this->implodeLimited($package->homestays->pluck('nama')->all()),
                    $this->implodeLimited($packageCulinaries),
                    $this->implodeLimited($package->boats->pluck('nama')->all()),
                    $this->implodeLimited($package->kiosks->pluck('nama')->all())
                );
            }

            if ($total > $packages->count()) {
                $lines[] = sprintf('- Dan %d paket aktif lainnya.', $total - $packages->count());
            }

            return implode("\n", $lines);
        } catch (Throwable $exception) {
            Log::warning('Failed to load package section for chatbot', ['error' => $exception->getMessage()]);
            return 'Paket wisata: data tidak tersedia sementara.';
        }
    }

    private function buildHomestaySection(): string
    {
        try {
            $limit = $this->itemLimit();
            $total = Homestay::where('is_active', true)->count();
            $homestays = Homestay::query()
                ->select(['id_homestay', 'nama', 'kapasitas', 'harga_per_malam'])
                ->where('is_active', true)
                ->orderBy('nama')
                ->limit($limit)
                ->get();

            $lines = [
                sprintf('Homestay aktif (%d total, %d ditampilkan):', $total, $homestays->count()),
            ];

            foreach ($homestays as $homestay) {
                $lines[] = sprintf(
                    '- %s | %s | kapasitas: %d | harga/malam: %s',
                    $homestay->id_homestay,
                    $homestay->nama,
                    (int) $homestay->kapasitas,
                    $this->formatMyr($homestay->harga_per_malam)
                );
            }

            if ($total > $homestays->count()) {
                $lines[] = sprintf('- Dan %d homestay aktif lainnya.', $total - $homestays->count());
            }

            return implode("\n", $lines);
        } catch (Throwable $exception) {
            Log::warning('Failed to load homestay section for chatbot', ['error' => $exception->getMessage()]);
            return 'Homestay: data tidak tersedia sementara.';
        }
    }

    private function buildCulinarySection(): string
    {
        try {
            $limit = $this->itemLimit();
            $total = Culinary::count();
            $culinaries = Culinary::query()
                ->select(['id_culinary', 'nama', 'lokasi', 'deskripsi'])
                ->withCount('pakets')
                ->orderBy('nama')
                ->limit($limit)
                ->get();

            $lines = [
                sprintf('Culinary (%d total, %d ditampilkan):', $total, $culinaries->count()),
            ];

            foreach ($culinaries as $culinary) {
                $lines[] = sprintf(
                    '- %s | %s | lokasi: %s | jumlah paket kuliner: %d | deskripsi: %s',
                    $culinary->id_culinary,
                    $culinary->nama,
                    $this->safeValue($culinary->lokasi),
                    (int) $culinary->pakets_count,
                    $this->cleanText($culinary->deskripsi)
                );
            }

            if ($total > $culinaries->count()) {
                $lines[] = sprintf('- Dan %d culinary lainnya.', $total - $culinaries->count());
            }

            return implode("\n", $lines);
        } catch (Throwable $exception) {
            Log::warning('Failed to load culinary section for chatbot', ['error' => $exception->getMessage()]);
            return 'Culinary: data tidak tersedia sementara.';
        }
    }

    private function buildKioskSection(): string
    {
        try {
            $limit = $this->itemLimit();
            $total = Kiosk::count();
            $kiosks = Kiosk::query()
                ->select(['id_kiosk', 'nama', 'kapasitas', 'harga_per_paket', 'deskripsi'])
                ->orderBy('nama')
                ->limit($limit)
                ->get();

            $lines = [
                sprintf('Kiosk (%d total, %d ditampilkan):', $total, $kiosks->count()),
            ];

            foreach ($kiosks as $kiosk) {
                $lines[] = sprintf(
                    '- %s | %s | kapasitas: %d | harga/paket: %s | deskripsi: %s',
                    $kiosk->id_kiosk,
                    $kiosk->nama,
                    (int) $kiosk->kapasitas,
                    $this->formatMyr($kiosk->harga_per_paket),
                    $this->cleanText($kiosk->deskripsi)
                );
            }

            if ($total > $kiosks->count()) {
                $lines[] = sprintf('- Dan %d kiosk lainnya.', $total - $kiosks->count());
            }

            return implode("\n", $lines);
        } catch (Throwable $exception) {
            Log::warning('Failed to load kiosk section for chatbot', ['error' => $exception->getMessage()]);
            return 'Kiosk: data tidak tersedia sementara.';
        }
    }

    private function itemLimit(): int
    {
        return max(3, (int) config('chatbot.knowledge.max_items_per_section', 8));
    }

    private function cleanText(?string $value): string
    {
        $maxChars = max(40, (int) config('chatbot.knowledge.max_description_chars', 90));
        $text = preg_replace('/\s+/', ' ', strip_tags((string) $value));
        $text = trim((string) $text);

        if ($text === '') {
            return '-';
        }

        return Str::limit($text, $maxChars, '...');
    }

    private function safeValue(?string $value): string
    {
        $trimmed = trim((string) $value);
        return $trimmed !== '' ? $trimmed : '-';
    }

    private function formatMyr(float|int|string|null $amount): string
    {
        return 'RM ' . number_format((float) $amount, 2, '.', ',');
    }

    /**
     * @param array<int, string> $items
     */
    private function implodeLimited(array $items, int $maxVisible = 3): string
    {
        $normalized = array_values(array_filter(array_map(function ($item): string {
            return trim((string) $item);
        }, $items), function ($item): bool {
            return $item !== '';
        }));

        if (empty($normalized)) {
            return '-';
        }

        $visible = array_slice($normalized, 0, $maxVisible);
        $remaining = count($normalized) - count($visible);
        $result = implode(', ', $visible);

        if ($remaining > 0) {
            $result .= sprintf(' (+%d lainnya)', $remaining);
        }

        return $result;
    }
}
