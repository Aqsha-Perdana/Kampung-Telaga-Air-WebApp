<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaketWisata;
use App\Services\AI\AdminAIToolOrchestrator;
use App\Services\AI\EntityResolverService;
use App\Services\AI\FinanceInsightService;
use App\Services\AI\PackageInsightService;
use App\Services\AI\ResourceInsightService;
use App\Support\AI\AdminAIDomainRegistry;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAIChatService
{
    public function __construct(
        private readonly AdminAIDomainRegistry $domainRegistry,
        private readonly EntityResolverService $entityResolver,
        private readonly ResourceInsightService $resourceInsightService,
        private readonly PackageInsightService $packageInsightService,
        private readonly FinanceInsightService $financeInsightService,
        private readonly AdminAIToolOrchestrator $toolOrchestrator
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @param array<string, mixed> $sessionMemory
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    public function reply(string $message, array $history = [], array $sessionMemory = []): array
    {
        $route = $this->toolOrchestrator->orchestrate($message, $history, $sessionMemory);

        $response = match ($route['intent']) {
            'domain_scope' => $this->domainScopeInsight($message, $history, $sessionMemory, $route),
            'finance_overview' => $this->financeOverviewInsight($message, $history, $sessionMemory, $route),
            'entity_detail' => $this->entityDetailInsight($message, $history, $sessionMemory, $route),
            'sales_trend' => $this->salesTrendInsight($message, $history, $sessionMemory, $route),
            'top_customer' => $this->topCustomerInsight($message, $history, $sessionMemory, $route),
            'resource_bottleneck' => $this->resourceBottleneckInsight($message, $history, $sessionMemory, $route),
            'profit' => $this->profitInsight($message, $history, $sessionMemory, $route),
            'refund' => $this->refundInsight($message, $history, $sessionMemory, $route),
            'idle_resources' => $this->idleResourceInsight($message, $history, $sessionMemory, $route),
            default => $this->operationsSummary($message, $history, $sessionMemory, $route),
        };

        return $this->attachOrchestrationContext($response, $route);
    }

    /**
     * @return array<int, array{label:string,prompt:string,hint:string}>
     */
    public function promptSuggestions(): array
    {
        $salesTrend = $this->salesTrendSnapshot();
        $topCustomer = $this->topCustomerSnapshot();
        $finance = $this->financeInsightService->overview();
        $trendHint = ($salesTrend['revenue_change_percent'] ?? 0) >= 0
            ? 'Revenue 7 hari terakhir sedang membaik'
            : 'Revenue 7 hari terakhir sedang melemah';

        $prompts = [
            [
                'label' => 'Sales Trend',
                'prompt' => 'bagaimana tren penjualan 7 hari terakhir dibanding 7 hari sebelumnya?',
                'hint' => $trendHint,
            ],
            [
                'label' => 'Top Customer',
                'prompt' => 'siapa customer paling bernilai dalam 90 hari terakhir?',
                'hint' => $topCustomer !== null
                    ? 'Saat ini dipimpin oleh ' . $topCustomer['customer_name']
                    : 'Cari pelanggan dengan kontribusi omzet terbesar',
            ],
            [
                'label' => 'Bottleneck',
                'prompt' => 'resource mana yang paling berisiko bottleneck dalam 7 hari ke depan?',
                'hint' => 'Cocok untuk cek kapasitas sebelum tanggal padat',
            ],
            [
                'label' => 'Profit',
                'prompt' => 'paket mana yang paling profit 30 hari terakhir?',
                'hint' => 'Lihat paket dengan margin paling sehat',
            ],
            [
                'label' => 'Finance',
                'prompt' => 'ringkas kondisi keuangan 30 hari terakhir',
                'hint' => 'Net profit saat ini RM ' . number_format((float) ($finance['net_profit'] ?? 0), 2),
            ],
            [
                'label' => 'AI Scope',
                'prompt' => 'apa saja domain data yang dipahami admin ai chat saat ini?',
                'hint' => 'Cakupan data aman untuk booking, resource, package, finance, dan audit chat',
            ],
        ];

        return array_slice($prompts, 0, 6);
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @param array<string, mixed> $sessionMemory
     */
    private function detectIntent(string $message, array $history = [], array $sessionMemory = []): string
    {
        $normalized = $this->normalize($message);
        $resolvedEntity = $this->entityResolver->resolve($message);

        if ($this->containsAny($normalized, ['database', 'db', 'schema', 'skema', 'tabel', 'domain data', 'cakupan ai', 'apa saja domain', 'apa yang kamu tahu'])) {
            return 'domain_scope';
        }

        if ($this->containsAny($normalized, ['keuangan', 'finance', 'cash flow', 'cashflow', 'beban operasional', 'profit bersih', 'gross profit', 'net profit', 'arus kas'])) {
            return 'finance_overview';
        }

        if ($resolvedEntity !== null && $this->shouldUseEntityDetailIntent($normalized, $resolvedEntity)) {
            return 'entity_detail';
        }

        if ($this->containsAny($normalized, ['customer', 'pelanggan', 'wisatawan terbaik', 'top customer', 'customer paling', 'pelanggan paling'])) {
            return 'top_customer';
        }

        if ($this->containsAny($normalized, ['bottleneck', 'kapasitas', 'capacity', 'overbook', 'overbooking', 'padat', 'penuh', 'utilisasi'])) {
            return 'resource_bottleneck';
        }

        if ($this->containsAny($normalized, ['trend', 'tren', 'naik', 'turun', 'penjualan', 'sales'])) {
            return 'sales_trend';
        }

        if ($this->containsAny($normalized, ['profit', 'untung', 'margin', 'revenue', 'omzet'])) {
            return 'profit';
        }

        if ($this->containsAny($normalized, ['refund', 'cancel', 'dibatalkan', 'gagal bayar'])) {
            return 'refund';
        }

        if ($this->containsAny($normalized, ['idle', 'sepi', 'tidak laku', 'jarang', 'resource', 'boat', 'homestay'])) {
            return 'idle_resources';
        }

        $lastIntent = $this->lastAssistantIntent($history) ?? ($sessionMemory['latest_intent'] ?? null);
        if ($lastIntent !== null && $this->isFollowUpMessage($normalized)) {
            return (string) $lastIntent;
        }

        return 'operations_summary';
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

    /**
     * @param array<string, mixed> $resolvedEntity
     */
    private function shouldUseEntityDetailIntent(string $message, array $resolvedEntity): bool
    {
        return ($resolvedEntity['score'] ?? 0) >= 90
            || $this->containsAny($message, ['detail', 'info', 'kapasitas', 'harga', 'status', 'berapa', 'siapa', 'paket ini', 'resource ini', 'isi', 'deskripsi']);
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function domainScopeInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $overview = $this->domainRegistry->overview();
        $labels = collect($overview['domains'] ?? [])->map(fn ($domain) => $domain['label'] ?? null)->filter()->values()->all();

        $answer = sprintf(
            'Admin AI Chat saat ini memahami %d domain data utama: %s. Saya membaca data melalui layer aman, bukan akses bebas ke seluruh database. Cakupan aman yang aktif sekarang mencakup %d tabel yang di-whitelist untuk insight admin.',
            (int) ($overview['total_domains'] ?? 0),
            implode(', ', $labels),
            count($overview['safe_tables'] ?? [])
        );

        return [
            'intent' => 'domain_scope',
            'answer' => $answer,
            'context' => $overview,
            'confidence' => 93.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function financeOverviewInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        [$startDate, $endDate] = $this->resolvePeriodWindow($route, 30);
        $overview = $this->financeInsightService->overview($startDate, $endDate);
        $expenseSegment = !empty($overview['top_expense_category'])
            ? ' Beban operasional terbesar ada di kategori ' . $overview['top_expense_category']['name']
                . ' sebesar RM ' . number_format((float) $overview['top_expense_category']['amount'], 2) . '.'
            : '';

        $answer = sprintf(
            'Ringkasan keuangan %s sampai %s: revenue RM %s, cost of sales RM %s, gross profit RM %s, operating expenses RM %s, dan net profit RM %s. Pergerakan kas bersih periode ini sekitar RM %s.%s',
            $overview['period_start'],
            $overview['period_end'],
            number_format((float) $overview['revenue'], 2),
            number_format((float) $overview['cost_of_sales'], 2),
            number_format((float) $overview['gross_profit'], 2),
            number_format((float) $overview['operating_expenses'], 2),
            number_format((float) $overview['net_profit'], 2),
            number_format((float) $overview['net_cash_movement'], 2),
            $expenseSegment
        );

        return [
            'intent' => 'finance_overview',
            'answer' => $answer,
            'context' => $overview,
            'confidence' => 91.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function entityDetailInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $resolved = $this->entityResolver->resolve($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'entity_detail');
        $topicContext = is_array($route['topic_context'] ?? null) ? $route['topic_context'] : [];

        if ($resolved === null) {
            $previousDetail = $previousContext['context']['detail'] ?? null;
            if (!is_array($previousDetail)) {
                $previousDetail = $topicContext['detail'] ?? null;
            }
            if (!is_array($previousDetail)) {
                $previousDetail = $sessionMemory['active_topic']['latest_context']['detail'] ?? null;
            }
            if (!is_array($previousDetail)) {
                $previousDetail = $sessionMemory['latest_context']['detail'] ?? null;
            }

            if (is_array($previousDetail)) {
                return [
                    'intent' => 'entity_detail',
                    'answer' => $this->formatEntityFollowUpAnswer($previousDetail, $message),
                    'context' => [
                        'detail' => $previousDetail,
                        'follow_up_from' => 'entity_detail',
                    ],
                    'confidence' => 86.0,
                    'model' => 'internal-ops-v1',
                ];
            }

            return [
                'intent' => 'entity_detail',
                'answer' => 'Saya belum berhasil mengenali entity yang kamu maksud. Coba sebutkan nama atau ID resource/package dengan lebih spesifik.',
                'context' => [],
                'confidence' => 40.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $detail = $resolved['type'] === 'package'
            ? $this->packageInsightService->packageDetail((string) $resolved['entity_id'])
            : $this->resourceInsightService->entityDetail((string) $resolved['type'], (string) $resolved['entity_id']);

        if ($detail === null) {
            return [
                'intent' => 'entity_detail',
                'answer' => 'Entity berhasil dikenali sebagai ' . $resolved['label'] . ', tetapi detail datanya belum berhasil dimuat.',
                'context' => $resolved,
                'confidence' => 46.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $answer = $this->formatEntityDetailAnswer($detail);

        return [
            'intent' => 'entity_detail',
            'answer' => $answer,
            'context' => array_merge($resolved, ['detail' => $detail]),
            'confidence' => 89.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function salesTrendInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $snapshot = $this->salesTrendSnapshot();
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'sales_trend');

        if (($snapshot['current_orders'] + $snapshot['previous_orders']) === 0) {
            return [
                'intent' => 'sales_trend',
                'answer' => 'Belum ada data order yang cukup untuk membaca tren penjualan dua minggu terakhir.',
                'context' => $snapshot,
                'confidence' => 48.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['kenapa', 'apa penyebab', 'sebab', 'alasan'])) {
            $drivers = [];

            if (($snapshot['current_orders'] ?? 0) < ($snapshot['previous_orders'] ?? 0)) {
                $drivers[] = 'jumlah order turun dari ' . $snapshot['previous_orders'] . ' menjadi ' . $snapshot['current_orders'];
            }

            if (($snapshot['current_participants'] ?? 0) < ($snapshot['previous_participants'] ?? 0)) {
                $drivers[] = 'jumlah peserta ikut turun dari ' . $snapshot['previous_participants'] . ' ke ' . $snapshot['current_participants'];
            }

            if (!empty($snapshot['top_package_name'])) {
                $drivers[] = 'penjualan periode sekarang paling banyak ditopang oleh ' . $snapshot['top_package_name'];
            }

            $answer = 'Penyebab utamanya terlihat dari perubahan volume. ' . implode(', ', $drivers) . '. Jadi penurunan atau kenaikan tren saat ini lebih banyak datang dari perubahan jumlah order dan peserta, bukan hanya dari satu transaksi besar.';

            return [
                'intent' => 'sales_trend',
                'answer' => $answer,
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'sales_trend',
                    'follow_up_mode' => 'diagnosis',
                ]),
                'confidence' => 89.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['detail', 'rinci', 'angka', 'berapa order', 'berapa revenue', 'berapa peserta'])) {
            $answer = sprintf(
                'Detail tren 7 hari terakhir: %d order, revenue RM %s, dan %d peserta. Periode 7 hari sebelumnya: %d order, revenue RM %s, dan %d peserta. Perubahan revenue tercatat %.1f%%.',
                (int) $snapshot['current_orders'],
                number_format((float) $snapshot['current_revenue'], 2),
                (int) $snapshot['current_participants'],
                (int) $snapshot['previous_orders'],
                number_format((float) $snapshot['previous_revenue'], 2),
                (int) $snapshot['previous_participants'],
                (float) $snapshot['revenue_change_percent']
            );

            return [
                'intent' => 'sales_trend',
                'answer' => $answer,
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'sales_trend',
                    'follow_up_mode' => 'detail',
                ]),
                'confidence' => 90.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $trendDirection = ($snapshot['revenue_change_percent'] ?? 0) >= 0 ? 'naik' : 'turun';
        $topPackageSegment = !empty($snapshot['top_package_name'])
            ? ' Paket terlaris pada periode sekarang adalah ' . $snapshot['top_package_name'] . '.'
            : '';

        $answer = sprintf(
            'Dibanding 7 hari sebelumnya, tren penjualan 7 hari terakhir %s %.1f%% untuk revenue dan %.1f%% untuk jumlah order. Periode sekarang mencatat %d order dengan revenue RM %s dan %d peserta.%s',
            $trendDirection,
            abs((float) $snapshot['revenue_change_percent']),
            abs((float) $snapshot['order_change_percent']),
            (int) $snapshot['current_orders'],
            number_format((float) $snapshot['current_revenue'], 2),
            (int) $snapshot['current_participants'],
            $topPackageSegment
        );

        return [
            'intent' => 'sales_trend',
            'answer' => $answer,
            'context' => $snapshot,
            'confidence' => 87.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function topCustomerInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $snapshot = $this->topCustomerSnapshot();
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'top_customer');

        if ($snapshot === null) {
            return [
                'intent' => 'top_customer',
                'answer' => 'Belum ada data customer yang cukup untuk menentukan pelanggan paling bernilai dalam 90 hari terakhir.',
                'context' => [
                    'period_start' => Carbon::now()->subDays(90)->toDateString(),
                    'period_end' => Carbon::now()->toDateString(),
                ],
                'confidence' => 44.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['email', 'kontak', 'hubungi'])) {
            return [
                'intent' => 'top_customer',
                'answer' => 'Kontak customer paling bernilai saat ini adalah ' . $snapshot['customer_email'] . '. Jika ingin, saya bisa lanjutkan dengan total order atau paket favoritnya.',
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'top_customer',
                    'follow_up_mode' => 'contact',
                ]),
                'confidence' => 92.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['paket', 'favorit', 'sering diambil'])) {
            return [
                'intent' => 'top_customer',
                'answer' => !empty($snapshot['favorite_package'])
                    ? 'Paket yang paling sering diambil oleh ' . $snapshot['customer_name'] . ' adalah ' . $snapshot['favorite_package'] . '.'
                    : 'Belum ada pola paket favorit yang cukup kuat untuk customer ini.',
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'top_customer',
                    'follow_up_mode' => 'favorite_package',
                ]),
                'confidence' => 90.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['berapa order', 'berapa kali', 'berapa revenue', 'berapa peserta', 'detail'])) {
            return [
                'intent' => 'top_customer',
                'answer' => sprintf(
                    '%s tercatat punya %d order, revenue RM %s, dan %d peserta dalam 90 hari terakhir.',
                    $snapshot['customer_name'],
                    (int) $snapshot['total_orders'],
                    number_format((float) $snapshot['total_revenue'], 2),
                    (int) $snapshot['total_participants']
                ),
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'top_customer',
                    'follow_up_mode' => 'detail',
                ]),
                'confidence' => 91.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $answer = sprintf(
            'Dalam 90 hari terakhir, customer paling bernilai adalah %s dengan %d order, total revenue RM %s, dan %d peserta. Kontak utama yang tersimpan adalah %s.%s',
            $snapshot['customer_name'],
            (int) $snapshot['total_orders'],
            number_format((float) $snapshot['total_revenue'], 2),
            (int) $snapshot['total_participants'],
            $snapshot['customer_email'],
            !empty($snapshot['favorite_package']) ? ' Paket yang paling sering diambil: ' . $snapshot['favorite_package'] . '.' : ''
        );

        return [
            'intent' => 'top_customer',
            'answer' => $answer,
            'context' => $snapshot,
            'confidence' => 85.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function resourceBottleneckInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $snapshot = $this->resourceBottleneckSnapshot();
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'resource_bottleneck');

        if (($snapshot['highest_utilization_percent'] ?? 0) <= 0) {
            return [
                'intent' => 'resource_bottleneck',
                'answer' => 'Dalam 7 hari ke depan belum ada bottleneck resource yang menonjol. Jadwal masih relatif longgar di semua kelompok resource.',
                'context' => $snapshot,
                'confidence' => 74.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['tanggal', 'kapan', 'hari apa'])) {
            return [
                'intent' => 'resource_bottleneck',
                'answer' => 'Tanggal paling padat saat ini adalah ' . $snapshot['date_label'] . ' untuk resource ' . $snapshot['resource_type'] . ' dengan utilisasi ' . number_format((float) $snapshot['highest_utilization_percent'], 1) . '%.',
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'resource_bottleneck',
                    'follow_up_mode' => 'date_focus',
                ]),
                'confidence' => 91.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['berapa persen', 'berapa util', 'berapa kapasitas', 'detail'])) {
            return [
                'intent' => 'resource_bottleneck',
                'answer' => sprintf(
                    'Puncak bottleneck ada di %s pada %s: %d dari %d resource aktif sudah terpakai, jadi utilisasinya %s%%.',
                    $snapshot['resource_type'],
                    $snapshot['date_label'],
                    (int) $snapshot['booked_resources'],
                    (int) $snapshot['total_resources'],
                    number_format((float) $snapshot['highest_utilization_percent'], 1)
                ),
                'context' => array_merge($snapshot, [
                    'follow_up_from' => $previousContext['intent'] ?? 'resource_bottleneck',
                    'follow_up_mode' => 'detail',
                ]),
                'confidence' => 90.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $secondarySegment = !empty($snapshot['secondary_warning'])
            ? ' Peringatan berikutnya ada di ' . $snapshot['secondary_warning']['resource_type']
                . ' tanggal ' . $snapshot['secondary_warning']['date_label']
                . ' dengan utilisasi ' . $snapshot['secondary_warning']['utilization_percent'] . '%.'
            : '';

        $answer = sprintf(
            'Bottleneck terkuat dalam 7 hari ke depan ada di %s pada %s, dengan utilisasi %s%% (%d dari %d resource aktif sudah terpakai).%s',
            $snapshot['resource_type'],
            $snapshot['date_label'],
            number_format((float) $snapshot['highest_utilization_percent'], 1),
            (int) $snapshot['booked_resources'],
            (int) $snapshot['total_resources'],
            $secondarySegment
        );

        return [
            'intent' => 'resource_bottleneck',
            'answer' => $answer,
            'context' => $snapshot,
            'confidence' => 81.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function profitInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $days = $this->resolveDaysFromRoute($route, 30);
        $since = Carbon::now()->subDays(max($days - 1, 0))->startOfDay();
        $periodLabel = $days . ' hari terakhir';
        $recognizedStatuses = $this->recognizedStatuses();
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'profit');

        $bestPackage = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $recognizedStatuses)
            ->where('orders.created_at', '>=', $since)
            ->select(
                'order_items.id_paket',
                DB::raw('MAX(order_items.nama_paket) as nama_paket'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0))) as total_profit'),
                DB::raw('COUNT(DISTINCT order_items.id_order) as total_orders')
            )
            ->groupBy('order_items.id_paket')
            ->orderByDesc(DB::raw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0)))'))
            ->first();

        $totalProfit = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $recognizedStatuses)
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('SUM(COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0))) as aggregate_profit')
            ->value('aggregate_profit') ?? 0;

        if (!$bestPackage) {
            return [
                'intent' => 'profit',
                'answer' => 'Belum ada data order yang cukup untuk menghitung paket paling profit dalam ' . $periodLabel . '.',
                'context' => [
                    'period_start' => $since->toDateString(),
                    'period_end' => Carbon::now()->toDateString(),
                    'total_profit' => (float) $totalProfit,
                ],
                'confidence' => 45.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $context = [
            'period_start' => $since->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'top_package_id' => $bestPackage->id_paket,
            'top_package_name' => $bestPackage->nama_paket,
            'top_package_profit' => (float) $bestPackage->total_profit,
            'top_package_revenue' => (float) $bestPackage->total_revenue,
            'top_package_orders' => (int) $bestPackage->total_orders,
            'total_profit' => (float) $totalProfit,
        ];

        if ($this->containsAny($normalized, ['berapa total', 'total profit', 'profit total'])) {
            return [
                'intent' => 'profit',
                'answer' => 'Total profit keseluruhan ' . $periodLabel . ' sekitar RM ' . number_format((float) $totalProfit, 2) . '.',
                'context' => array_merge($context, [
                    'follow_up_from' => $previousContext['intent'] ?? 'profit',
                    'follow_up_mode' => 'total_profit',
                ]),
                'confidence' => 92.0,
                'model' => 'internal-ops-v1',
            ];
        }

        if ($this->containsAny($normalized, ['berapa order', 'detail', 'paketnya apa'])) {
            return [
                'intent' => 'profit',
                'answer' => sprintf(
                    'Paket paling profit adalah %s dengan profit RM %s, revenue RM %s, dan %d order.',
                    (string) $bestPackage->nama_paket,
                    number_format((float) $bestPackage->total_profit, 2),
                    number_format((float) $bestPackage->total_revenue, 2),
                    (int) $bestPackage->total_orders
                ),
                'context' => array_merge($context, [
                    'follow_up_from' => $previousContext['intent'] ?? 'profit',
                    'follow_up_mode' => 'detail',
                ]),
                'confidence' => 92.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $answer = sprintf(
            'Dalam %s, paket paling profit adalah %s dengan estimasi profit RM %s dari %d order. Total profit keseluruhan periode ini sekitar RM %s.',
            $periodLabel,
            (string) $bestPackage->nama_paket,
            number_format((float) $bestPackage->total_profit, 2),
            (int) $bestPackage->total_orders,
            number_format((float) $totalProfit, 2)
        );

        return [
            'intent' => 'profit',
            'answer' => $answer,
            'context' => $context,
            'confidence' => 88.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function refundInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $days = $this->resolveDaysFromRoute($route, 30);
        $since = Carbon::now()->subDays(max($days - 1, 0))->startOfDay();
        $periodLabel = $days . ' hari terakhir';
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'refund');

        $requested = Order::where('created_at', '>=', $since)->where('status', 'refund_requested')->count();
        $refunded = Order::where('created_at', '>=', $since)->where('status', 'refunded')->count();
        $failed = Order::where('created_at', '>=', $since)->where('status', 'failed')->count();
        $cancelled = Order::where('created_at', '>=', $since)->where('status', 'cancelled')->count();

        $context = [
            'period_start' => $since->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'refund_requested' => $requested,
            'refunded' => $refunded,
            'failed' => $failed,
            'cancelled' => $cancelled,
        ];

        if ($this->containsAny($normalized, ['yang paling tinggi', 'mana paling tinggi', 'paling besar'])) {
            $counts = [
                'refund request' => $requested,
                'refunded' => $refunded,
                'pembayaran gagal' => $failed,
                'order dibatalkan' => $cancelled,
            ];
            arsort($counts);
            $topLabel = (string) array_key_first($counts);
            $topCount = (int) current($counts);

            return [
                'intent' => 'refund',
                'answer' => 'Kasus yang paling tinggi dalam ' . $periodLabel . ' adalah ' . $topLabel . ' sebanyak ' . $topCount . ' order.',
                'context' => array_merge($context, [
                    'follow_up_from' => $previousContext['intent'] ?? 'refund',
                    'follow_up_mode' => 'top_issue',
                ]),
                'confidence' => 90.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $answer = sprintf(
            'Dalam %s ada %d refund request, %d order refunded, %d pembayaran gagal, dan %d order dibatalkan. Fokus pertama sebaiknya cek pola di order gagal dan refund request karena dua titik itu paling sering memicu friksi operasional.',
            $periodLabel,
            $requested,
            $refunded,
            $failed,
            $cancelled
        );

        return [
            'intent' => 'refund',
            'answer' => $answer,
            'context' => $context,
            'confidence' => 82.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function idleResourceInsight(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $packageCount = PaketWisata::count();
        $recommendations = app(PackageRecommendationService::class)->getRecommendations();
        $normalized = $this->normalize($message);
        $previousContext = $this->lastAssistantContextForIntent($history, 'idle_resources');

        $idleCounts = [
            'boats_never_used' => (int) $recommendations['boats']['never_used']->count(),
            'homestays_never_used' => (int) $recommendations['homestays']['never_used']->count(),
            'destinations_never_used' => (int) $recommendations['destinations']['never_used']->count(),
            'culinaries_never_used' => (int) $recommendations['culinaries']['never_used']->count(),
            'kiosks_never_used' => (int) $recommendations['kiosks']['never_used']->count(),
        ];

        arsort($idleCounts);
        $topIdleType = (string) array_key_first($idleCounts);
        $topIdleCount = (int) current($idleCounts);
        $humanLabel = str_replace('_never_used', '', $topIdleType);

        $context = array_merge($idleCounts, [
            'package_count' => $packageCount,
            'top_idle_type' => $humanLabel,
            'top_idle_count' => $topIdleCount,
        ]);

        if ($this->containsAny($normalized, ['berapa item', 'berapa jumlah', 'detail'])) {
            return [
                'intent' => 'idle_resources',
                'answer' => 'Kelompok resource paling idle saat ini adalah ' . $humanLabel . ' dengan ' . $topIdleCount . ' item yang belum pernah dipakai di paket.',
                'context' => array_merge($context, [
                    'follow_up_from' => $previousContext['intent'] ?? 'idle_resources',
                    'follow_up_mode' => 'detail',
                ]),
                'confidence' => 90.0,
                'model' => 'internal-ops-v1',
            ];
        }

        $answer = sprintf(
            'Dari %d paket yang ada, resource paling idle saat ini ada di kelompok %s dengan %d item yang belum pernah dipakai di paket. Ini kandidat paling bagus untuk tindakan optimasi resource berikutnya.',
            $packageCount,
            $humanLabel,
            $topIdleCount
        );

        return [
            'intent' => 'idle_resources',
            'answer' => $answer,
            'context' => $context,
            'confidence' => 80.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array{intent:string,answer:string,context:array<string,mixed>,confidence:float,model:string}
     */
    private function operationsSummary(string $message, array $history = [], array $sessionMemory = [], array $route = []): array
    {
        $since = Carbon::now()->subDays(7);
        $recognizedStatuses = $this->recognizedStatuses();
        $lastIntent = $this->lastAssistantIntent($history) ?? ($sessionMemory['latest_intent'] ?? null);
        $activeTopicLabel = $sessionMemory['active_topic']['label'] ?? null;

        $paidOrders = Order::where('created_at', '>=', $since)->whereIn('status', $recognizedStatuses)->count();
        $revenue = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.created_at', '>=', $since)
            ->whereIn('orders.status', $recognizedStatuses)
            ->sum('order_items.subtotal');
        $participants = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('orders.created_at', '>=', $since)
            ->whereIn('orders.status', $recognizedStatuses)
            ->sum('order_items.jumlah_peserta');

        $context = [
            'period_start' => $since->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'paid_orders' => $paidOrders,
            'revenue' => (float) $revenue,
            'participants' => (int) $participants,
            'last_session_intent' => $lastIntent,
        ];

        $answer = sprintf(
            'Ringkasan 7 hari terakhir: %d order berhasil, estimasi revenue RM %s, dan %d peserta terlayani. Jika mau, saya bisa bantu fokuskan ke tren penjualan, top customer, bottleneck resource, profit, refund, resource idle, detail package/resource, atau kondisi keuangan.',
            $paidOrders,
            number_format((float) $revenue, 2),
            (int) $participants
        );

        if ($activeTopicLabel !== null) {
            $answer .= ' Fokus sesi aktif yang masih saya pegang adalah ' . $activeTopicLabel . ', jadi kamu bisa lanjut tanya tanpa mengulang dari awal.';
        } elseif ($lastIntent !== null) {
            $answer .= ' Saya juga masih mengingat topik sesi sebelumnya tentang ' . str_replace('_', ' ', $lastIntent) . ', jadi kamu bisa lanjut tanya tanpa mengulang dari awal.';
        }

        if (!empty($sessionMemory['summary_text'])) {
            $answer .= ' Ringkasan memori sesi: ' . $sessionMemory['summary_text'];
        }

        return [
            'intent' => 'operations_summary',
            'answer' => $answer,
            'context' => $context,
            'confidence' => 78.0,
            'model' => 'internal-ops-v1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function salesTrendSnapshot(): array
    {
        $today = Carbon::today();
        $currentStart = $today->copy()->subDays(6)->startOfDay();
        $currentEnd = $today->copy()->endOfDay();
        $previousStart = $currentStart->copy()->subDays(7)->startOfDay();
        $previousEnd = $currentStart->copy()->subDay()->endOfDay();

        $current = $this->aggregateSalesWindow($currentStart, $currentEnd);
        $previous = $this->aggregateSalesWindow($previousStart, $previousEnd);

        $topPackage = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $this->recognizedStatuses())
            ->whereBetween('orders.created_at', [$currentStart, $currentEnd])
            ->select(
                DB::raw('MAX(order_items.nama_paket) as nama_paket'),
                DB::raw('COUNT(DISTINCT order_items.id_order) as total_orders')
            )
            ->groupBy('order_items.id_paket')
            ->orderByDesc(DB::raw('COUNT(DISTINCT order_items.id_order)'))
            ->first();

        return [
            'period_start' => $currentStart->toDateString(),
            'period_end' => $currentEnd->toDateString(),
            'comparison_start' => $previousStart->toDateString(),
            'comparison_end' => $previousEnd->toDateString(),
            'current_orders' => (int) $current['orders'],
            'current_revenue' => (float) $current['revenue'],
            'current_participants' => (int) $current['participants'],
            'previous_orders' => (int) $previous['orders'],
            'previous_revenue' => (float) $previous['revenue'],
            'previous_participants' => (int) $previous['participants'],
            'order_change_percent' => $this->percentChange((float) $current['orders'], (float) $previous['orders']),
            'revenue_change_percent' => $this->percentChange((float) $current['revenue'], (float) $previous['revenue']),
            'participant_change_percent' => $this->percentChange((float) $current['participants'], (float) $previous['participants']),
            'top_package_name' => $topPackage?->nama_paket,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function aggregateSalesWindow(Carbon $start, Carbon $end): array
    {
        return [
            'orders' => (int) Order::query()
                ->whereIn('status', $this->recognizedStatuses())
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'revenue' => (float) (OrderItem::query()
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->whereIn('orders.status', $this->recognizedStatuses())
                ->whereBetween('orders.created_at', [$start, $end])
                ->sum('order_items.subtotal') ?? 0),
            'participants' => (int) (OrderItem::query()
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->whereIn('orders.status', $this->recognizedStatuses())
                ->whereBetween('orders.created_at', [$start, $end])
                ->sum('order_items.jumlah_peserta') ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function topCustomerSnapshot(): ?array
    {
        $since = Carbon::now()->subDays(90);

        $topCustomer = Order::query()
            ->join('order_items', 'orders.id_order', '=', 'order_items.id_order')
            ->whereIn('orders.status', $this->recognizedStatuses())
            ->where('orders.created_at', '>=', $since)
            ->select(
                'orders.customer_name as raw_customer_name',
                'orders.customer_email as raw_customer_email',
                DB::raw("COALESCE(NULLIF(orders.customer_name, ''), 'Customer tanpa nama') as customer_name"),
                DB::raw("COALESCE(NULLIF(orders.customer_email, ''), 'tanpa-email@local') as customer_email"),
                DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.jumlah_peserta) as total_participants'),
                DB::raw('MAX(orders.created_at) as last_order_at')
            )
            ->groupBy('orders.customer_name', 'orders.customer_email')
            ->orderByDesc(DB::raw('SUM(order_items.subtotal)'))
            ->first();

        if (!$topCustomer) {
            return null;
        }

        $favoritePackage = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $this->recognizedStatuses())
            ->where('orders.created_at', '>=', $since)
            ->where('orders.customer_name', $topCustomer->raw_customer_name)
            ->where('orders.customer_email', $topCustomer->raw_customer_email)
            ->select(
                DB::raw('MAX(order_items.nama_paket) as nama_paket'),
                DB::raw('COUNT(*) as total_lines')
            )
            ->groupBy('order_items.id_paket')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->first();

        return [
            'period_start' => $since->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'customer_name' => (string) $topCustomer->customer_name,
            'customer_email' => $topCustomer->customer_email === 'tanpa-email@local'
                ? 'email belum tersedia'
                : (string) $topCustomer->customer_email,
            'total_orders' => (int) $topCustomer->total_orders,
            'total_revenue' => (float) $topCustomer->total_revenue,
            'total_participants' => (int) $topCustomer->total_participants,
            'last_order_at' => (string) $topCustomer->last_order_at,
            'favorite_package' => $favoritePackage?->nama_paket,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resourceBottleneckSnapshot(): array
    {
        $today = Carbon::today();
        $resourceTotals = [
            'boat' => $this->activeResourceCount('boats'),
            'homestay' => $this->activeResourceCount('homestays'),
            'culinary' => $this->activeResourceCount('culinaries'),
            'kiosk' => $this->activeResourceCount('kiosks'),
        ];

        $highest = [
            'resource_type' => 'boat',
            'date' => $today->toDateString(),
            'date_label' => $today->format('d M Y'),
            'booked_resources' => 0,
            'total_resources' => (int) ($resourceTotals['boat'] ?? 0),
            'highest_utilization_percent' => 0.0,
        ];
        $warnings = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $dayMetrics = [
                'boat' => $this->bookedBoatCountForDate($date),
                'homestay' => $this->bookedHomestayCountForDate($date),
                'culinary' => $this->bookedCulinaryCountForDate($date),
                'kiosk' => $this->bookedKioskCountForDate($date),
            ];

            foreach ($dayMetrics as $type => $bookedCount) {
                $totalCount = (int) ($resourceTotals[$type] ?? 0);
                $utilization = $totalCount > 0 ? round(($bookedCount / $totalCount) * 100, 1) : 0.0;
                $candidate = [
                    'resource_type' => $type,
                    'date' => $date,
                    'date_label' => Carbon::parse($date)->format('d M Y'),
                    'booked_resources' => (int) $bookedCount,
                    'total_resources' => $totalCount,
                    'utilization_percent' => $utilization,
                ];

                if ($utilization > (float) $highest['highest_utilization_percent']) {
                    $highest = [
                        'resource_type' => $type,
                        'date' => $candidate['date'],
                        'date_label' => $candidate['date_label'],
                        'booked_resources' => $candidate['booked_resources'],
                        'total_resources' => $candidate['total_resources'],
                        'highest_utilization_percent' => $candidate['utilization_percent'],
                    ];
                }

                if ($utilization >= 60) {
                    $warnings[] = $candidate;
                }
            }
        }

        usort($warnings, function (array $left, array $right) {
            return $right['utilization_percent'] <=> $left['utilization_percent'];
        });

        return array_merge($highest, [
            'window_start' => $today->toDateString(),
            'window_end' => $today->copy()->addDays(6)->toDateString(),
            'resource_totals' => $resourceTotals,
            'secondary_warning' => $warnings[1] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $detail
     */
    private function formatEntityDetailAnswer(array $detail): string
    {
        return match ($detail['type'] ?? null) {
            'package' => sprintf(
                'Package %s berstatus %s, durasi %d hari, range peserta %s, harga jual RM %s, modal RM %s, dan margin %s%%. Resource yang terhubung: %d boat, %d homestay, %d culinary, %d kiosk, dan %d destinasi. Performa historisnya mencatat %d order dengan revenue RM %s.',
                $detail['name'],
                $detail['status'],
                (int) $detail['duration_days'],
                $detail['participant_range'],
                number_format((float) $detail['selling_price'], 2),
                number_format((float) $detail['cost_price'], 2),
                number_format((float) $detail['margin_percent'], 1),
                count($detail['resources']['boats'] ?? []),
                count($detail['resources']['homestays'] ?? []),
                count($detail['resources']['culinaries'] ?? []),
                count($detail['resources']['kiosks'] ?? []),
                count($detail['resources']['destinations'] ?? []),
                (int) ($detail['performance']['total_orders'] ?? 0),
                number_format((float) ($detail['performance']['total_revenue'] ?? 0), 2)
            ),
            'boat' => sprintf(
                'Boat %s memiliki kapasitas %d pax, harga sewa RM %s, status %s, terhubung ke %d paket, dan sudah muncul di %d order aktif.',
                $detail['name'],
                (int) $detail['capacity'],
                number_format((float) $detail['price'], 2),
                $detail['status'],
                (int) $detail['package_count'],
                (int) $detail['order_count']
            ),
            'homestay' => sprintf(
                'Homestay %s memiliki kapasitas %d pax, harga per malam RM %s, status %s, terhubung ke %d paket, dan sudah muncul di %d order aktif.',
                $detail['name'],
                (int) $detail['capacity'],
                number_format((float) $detail['price'], 2),
                $detail['status'],
                (int) $detail['package_count'],
                (int) $detail['order_count']
            ),
            'culinary' => sprintf(
                'Culinary %s berlokasi di %s, punya %d paket culinary terkait, dan sudah muncul di %d order aktif.',
                $detail['name'],
                $detail['location'] !== '' ? $detail['location'] : 'lokasi belum diisi',
                (int) $detail['package_count'],
                (int) $detail['order_count']
            ),
            'kiosk' => sprintf(
                'Kiosk %s memiliki kapasitas %d pax, harga per paket RM %s, terhubung ke %d paket, dan sudah muncul di %d order aktif.',
                $detail['name'],
                (int) $detail['capacity'],
                number_format((float) $detail['price'], 2),
                (int) $detail['package_count'],
                (int) $detail['order_count']
            ),
            default => 'Detail entity berhasil dimuat, tetapi format jawaban untuk tipe ini belum tersedia.',
        };
    }

    /**
     * @param array<string, mixed> $detail
     */
    private function formatEntityFollowUpAnswer(array $detail, string $message): string
    {
        $normalized = $this->normalize($message);

        if ($this->containsAny($normalized, ['harga', 'price', 'biaya'])) {
            $price = $detail['price'] ?? ($detail['selling_price'] ?? null);
            return $price !== null
                ? 'Harga untuk ' . $detail['name'] . ' adalah RM ' . number_format((float) $price, 2) . '.'
                : 'Data harga untuk ' . $detail['name'] . ' belum tersedia.';
        }

        if ($this->containsAny($normalized, ['kapasitas', 'capacity', 'pax'])) {
            return isset($detail['capacity'])
                ? $detail['name'] . ' memiliki kapasitas ' . (int) $detail['capacity'] . ' pax.'
                : 'Data kapasitas untuk ' . $detail['name'] . ' tidak tersedia.';
        }

        if ($this->containsAny($normalized, ['status'])) {
            return isset($detail['status'])
                ? 'Status ' . $detail['name'] . ' saat ini adalah ' . $detail['status'] . '.'
                : 'Data status untuk ' . $detail['name'] . ' tidak tersedia.';
        }

        if ($this->containsAny($normalized, ['order', 'dipakai', 'terhubung', 'paket'])) {
            if (($detail['type'] ?? null) === 'package') {
                return $detail['name'] . ' sudah mencatat ' . (int) ($detail['performance']['total_orders'] ?? 0) . ' order aktif dan terhubung ke ' . count($detail['resources']['boats'] ?? []) . ' boat, ' . count($detail['resources']['homestays'] ?? []) . ' homestay, ' . count($detail['resources']['culinaries'] ?? []) . ' culinary, dan ' . count($detail['resources']['kiosks'] ?? []) . ' kiosk.';
            }

            return $detail['name'] . ' saat ini terhubung ke ' . (int) ($detail['package_count'] ?? 0) . ' paket dan sudah muncul di ' . (int) ($detail['order_count'] ?? 0) . ' order aktif.';
        }

        return $this->formatEntityDetailAnswer($detail);
    }

    /**
     * @param array<string, mixed> $response
     * @param array<string, mixed> $route
     * @return array<string, mixed>
     */
    private function attachOrchestrationContext(array $response, array $route): array
    {
        $context = is_array($response['context'] ?? null) ? $response['context'] : [];

        $context['tool'] = (string) ($route['tool'] ?? ($context['tool'] ?? 'ops.summary'));
        $context['tool_args'] = is_array($route['tool_args'] ?? null) ? $route['tool_args'] : ($context['tool_args'] ?? []);
        $context['topic_key'] = (string) ($route['topic_key'] ?? ($context['topic_key'] ?? $response['intent']));
        $context['topic_label'] = (string) ($route['topic_label'] ?? ($context['topic_label'] ?? Str::headline(str_replace('_', ' ', (string) ($response['intent'] ?? 'operations_summary')))));
        $context['topic_reference'] = (string) ($route['topic_reference'] ?? ($context['topic_reference'] ?? 'new'));

        $response['context'] = $context;

        return $response;
    }

    /**
     * @param array<string, mixed> $route
     * @return array{0:string,1:string}
     */
    private function resolvePeriodWindow(array $route, int $defaultDays): array
    {
        $days = $this->resolveDaysFromRoute($route, $defaultDays);
        $endDate = Carbon::now()->toDateString();
        $startDate = Carbon::now()->subDays(max($days - 1, 0))->toDateString();

        return [$startDate, $endDate];
    }

    /**
     * @param array<string, mixed> $route
     */
    private function resolveDaysFromRoute(array $route, int $defaultDays): int
    {
        $toolArgs = is_array($route['tool_args'] ?? null) ? $route['tool_args'] : [];
        $days = (int) ($toolArgs['days'] ?? 0);

        return $days > 0 ? $days : $defaultDays;
    }

    private function activeResourceCount(string $table): int
    {
        $query = DB::table($table);

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', 1);
        }

        return (int) $query->count();
    }

    private function bookedBoatCountForDate(string $date): int
    {
        return (int) $this->baseBookingQueryForDate($date)
            ->join('paket_wisata_boat', 'order_items.id_paket', '=', 'paket_wisata_boat.id_paket')
            ->distinct()
            ->count('paket_wisata_boat.id_boat');
    }

    private function bookedHomestayCountForDate(string $date): int
    {
        return (int) $this->baseBookingQueryForDate($date)
            ->join('paket_wisata_homestay', 'order_items.id_paket', '=', 'paket_wisata_homestay.id_paket')
            ->distinct()
            ->count('paket_wisata_homestay.id_homestay');
    }

    private function bookedCulinaryCountForDate(string $date): int
    {
        return (int) $this->baseBookingQueryForDate($date)
            ->join('paket_wisata_culinary', 'order_items.id_paket', '=', 'paket_wisata_culinary.id_paket')
            ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
            ->distinct()
            ->count('paket_culinaries.id_culinary');
    }

    private function bookedKioskCountForDate(string $date): int
    {
        return (int) $this->baseBookingQueryForDate($date)
            ->join('paket_wisata_kiosk', 'order_items.id_paket', '=', 'paket_wisata_kiosk.id_paket')
            ->distinct()
            ->count('paket_wisata_kiosk.id_kiosk');
    }

    private function baseBookingQueryForDate(string $date)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->whereIn('orders.status', $this->recognizedStatuses())
            ->where(function ($query) use ($date) {
                $query->whereRaw(
                    '? BETWEEN order_items.tanggal_keberangkatan AND DATE_ADD(order_items.tanggal_keberangkatan, INTERVAL (order_items.durasi_hari - 1) DAY)',
                    [$date]
                );
            });
    }

    /**
     * @return array<int, string>
     */
    private function recognizedStatuses(): array
    {
        return ['paid', 'confirmed', 'completed'];
    }

    private function percentChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param array<int, array<string, mixed>> $history
     */
    private function lastAssistantIntent(array $history): ?string
    {
        for ($index = count($history) - 1; $index >= 0; $index--) {
            $entry = $history[$index];
            if (($entry['role'] ?? null) === 'assistant' && !empty($entry['intent'])) {
                return (string) $entry['intent'];
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @return array<string, mixed>|null
     */
    private function lastAssistantContextForIntent(array $history, string $intent): ?array
    {
        for ($index = count($history) - 1; $index >= 0; $index--) {
            $entry = $history[$index];
            if (($entry['role'] ?? null) === 'assistant' && ($entry['intent'] ?? null) === $intent) {
                return [
                    'intent' => (string) $entry['intent'],
                    'context' => is_array($entry['context'] ?? null) ? $entry['context'] : [],
                    'message' => (string) ($entry['message'] ?? ''),
                ];
            }
        }

        return null;
    }

    private function normalize(string $message): string
    {
        return mb_strtolower(trim($message));
    }

    private function isFollowUpMessage(string $message): bool
    {
        if ($message === '') {
            return false;
        }

        $wordCount = count(array_filter(preg_split('/\s+/u', $message) ?: []));
        if ($wordCount <= 5) {
            return true;
        }

        return $this->containsAny($message, [
            'itu',
            'tadi',
            'sebelumnya',
            'yang tadi',
            'lebih detail',
            'detailnya',
            'kenapa',
            'mengapa',
            'lalu',
            'terus',
            'bagaimana kalau',
            'berapa',
            'siapa',
            'kapan',
            'mana',
            'emailnya',
            'paketnya',
        ]);
    }
}
