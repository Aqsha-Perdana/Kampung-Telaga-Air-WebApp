<?php

namespace App\Services\AI;

use Illuminate\Support\Str;

class AdminAIToolOrchestrator
{
    public function __construct(private readonly EntityResolverService $entityResolver)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $history
     * @param array<string, mixed> $sessionMemory
     * @return array<string, mixed>
     */
    public function orchestrate(string $message, array $history = [], array $sessionMemory = []): array
    {
        $normalized = $this->normalize($message);
        $resolvedEntity = $this->entityResolver->resolve($message);
        $topicReference = $this->detectTopicReference($normalized);
        $referencedTopic = $this->resolveTopicMemory($sessionMemory, $topicReference);
        $days = $this->extractDays($normalized);

        if ($this->containsAny($normalized, ['database', 'db', 'schema', 'skema', 'tabel', 'domain data', 'cakupan ai', 'apa saja domain', 'apa yang kamu tahu'])) {
            return $this->buildRoute(
                intent: 'domain_scope',
                tool: 'domain_registry.overview',
                confidence: 96.0,
                topicKey: 'domain_scope',
                topicLabel: 'Cakupan AI Admin'
            );
        }

        if ($this->containsAny($normalized, ['keuangan', 'finance', 'cash flow', 'cashflow', 'beban operasional', 'profit bersih', 'gross profit', 'net profit', 'arus kas'])) {
            return $this->buildFinanceRoute($days, $referencedTopic, $topicReference);
        }

        if ($resolvedEntity !== null && $this->shouldUseEntityDetailIntent($normalized, $resolvedEntity)) {
            return $this->buildEntityRoute($resolvedEntity, $topicReference);
        }

        if ($this->containsAny($normalized, ['customer', 'pelanggan', 'wisatawan terbaik', 'top customer', 'customer paling', 'pelanggan paling'])) {
            return $this->buildRoute(
                intent: 'top_customer',
                tool: 'customer.top',
                confidence: 92.0,
                topicKey: 'top_customer',
                topicLabel: 'Top Customer'
            );
        }

        if ($this->containsAny($normalized, ['bottleneck', 'kapasitas', 'capacity', 'overbook', 'overbooking', 'padat', 'penuh', 'utilisasi'])) {
            return $this->buildRoute(
                intent: 'resource_bottleneck',
                tool: 'resource.bottleneck',
                confidence: 91.0,
                topicKey: 'resource_bottleneck',
                topicLabel: 'Resource Bottleneck'
            );
        }

        if ($this->containsAny($normalized, ['trend', 'tren', 'naik', 'turun', 'penjualan', 'sales'])) {
            return $this->buildRoute(
                intent: 'sales_trend',
                tool: 'sales.trend',
                confidence: 91.0,
                topicKey: 'sales_trend',
                topicLabel: 'Sales Trend'
            );
        }

        if ($this->containsAny($normalized, ['profit', 'untung', 'margin', 'revenue', 'omzet'])) {
            return $this->buildProfitRoute($days, $referencedTopic, $topicReference);
        }

        if ($this->containsAny($normalized, ['refund', 'cancel', 'dibatalkan', 'gagal bayar'])) {
            return $this->buildRefundRoute($days, $referencedTopic, $topicReference);
        }

        if ($this->containsAny($normalized, ['idle', 'sepi', 'tidak laku', 'jarang', 'resource', 'boat', 'homestay'])) {
            return $this->buildRoute(
                intent: 'idle_resources',
                tool: 'resource.idle',
                confidence: 86.0,
                topicKey: 'idle_resources',
                topicLabel: 'Idle Resources'
            );
        }

        if ($this->isFollowUpMessage($normalized)) {
            $followUpRoute = $this->buildRouteFromTopic($referencedTopic, $topicReference);
            if ($followUpRoute !== null) {
                return $followUpRoute;
            }

            $lastIntent = $this->lastAssistantIntent($history) ?? ($sessionMemory['latest_intent'] ?? null);
            if ($lastIntent !== null) {
                return $this->buildRoute(
                    intent: (string) $lastIntent,
                    tool: $this->defaultToolForIntent((string) $lastIntent),
                    confidence: 82.0,
                    topicKey: (string) ($sessionMemory['active_topic_key'] ?? $lastIntent),
                    topicLabel: Str::headline(str_replace('_', ' ', (string) $lastIntent)),
                    topicReference: 'active'
                );
            }
        }

        return $this->buildRoute(
            intent: 'operations_summary',
            tool: 'ops.summary',
            confidence: 75.0,
            topicKey: 'operations_summary',
            topicLabel: 'Ringkasan Operasional'
        );
    }

    /**
     * @param array<string, mixed>|null $topic
     * @return array<string, mixed>
     */
    private function buildFinanceRoute(?int $days, ?array $topic, string $topicReference): array
    {
        $resolvedDays = $days ?: $this->resolveTopicDays($topic, 30);

        return $this->buildRoute(
            intent: 'finance_overview',
            tool: 'finance.overview',
            confidence: 93.0,
            topicKey: 'finance_overview:' . $resolvedDays . 'd',
            topicLabel: 'Finance Overview ' . $resolvedDays . ' Hari',
            toolArgs: ['days' => $resolvedDays],
            topicReference: $topicReference,
            topicContext: $topic['latest_context'] ?? []
        );
    }

    /**
     * @param array<string, mixed>|null $topic
     * @return array<string, mixed>
     */
    private function buildProfitRoute(?int $days, ?array $topic, string $topicReference): array
    {
        $resolvedDays = $days ?: $this->resolveTopicDays($topic, 30);

        return $this->buildRoute(
            intent: 'profit',
            tool: 'package.profitability',
            confidence: 90.0,
            topicKey: 'profit:' . $resolvedDays . 'd',
            topicLabel: 'Profit Paket ' . $resolvedDays . ' Hari',
            toolArgs: ['days' => $resolvedDays],
            topicReference: $topicReference,
            topicContext: $topic['latest_context'] ?? []
        );
    }

    /**
     * @param array<string, mixed>|null $topic
     * @return array<string, mixed>
     */
    private function buildRefundRoute(?int $days, ?array $topic, string $topicReference): array
    {
        $resolvedDays = $days ?: $this->resolveTopicDays($topic, 30);

        return $this->buildRoute(
            intent: 'refund',
            tool: 'refund.monitor',
            confidence: 88.0,
            topicKey: 'refund:' . $resolvedDays . 'd',
            topicLabel: 'Refund Watch ' . $resolvedDays . ' Hari',
            toolArgs: ['days' => $resolvedDays],
            topicReference: $topicReference,
            topicContext: $topic['latest_context'] ?? []
        );
    }

    /**
     * @param array<string, mixed> $resolvedEntity
     * @return array<string, mixed>
     */
    private function buildEntityRoute(array $resolvedEntity, string $topicReference): array
    {
        $label = (string) ($resolvedEntity['label'] ?? Str::headline((string) ($resolvedEntity['type'] ?? 'Entity')));
        $topicLabel = 'Detail ' . $label;

        return $this->buildRoute(
            intent: 'entity_detail',
            tool: 'entity.detail',
            confidence: 92.0,
            topicKey: 'entity_detail:' . ($resolvedEntity['type'] ?? 'entity') . ':' . ($resolvedEntity['entity_id'] ?? Str::slug($label)),
            topicLabel: $topicLabel,
            toolArgs: [
                'entity_type' => (string) ($resolvedEntity['type'] ?? ''),
                'entity_id' => (string) ($resolvedEntity['entity_id'] ?? ''),
            ],
            topicReference: $topicReference
        );
    }

    /**
     * @param array<string, mixed>|null $topic
     * @return array<string, mixed>|null
     */
    private function buildRouteFromTopic(?array $topic, string $topicReference): ?array
    {
        if (!$topic || empty($topic['intent'])) {
            return null;
        }

        $intent = (string) $topic['intent'];

        return $this->buildRoute(
            intent: $intent,
            tool: (string) ($topic['tool'] ?? $this->defaultToolForIntent($intent)),
            confidence: $topicReference === 'previous' ? 84.0 : 87.0,
            topicKey: (string) ($topic['key'] ?? $intent),
            topicLabel: (string) ($topic['label'] ?? Str::headline(str_replace('_', ' ', $intent))),
            toolArgs: is_array($topic['tool_args'] ?? null) ? $topic['tool_args'] : [],
            topicReference: $topicReference,
            topicContext: is_array($topic['latest_context'] ?? null) ? $topic['latest_context'] : []
        );
    }

    /**
     * @param array<string, mixed> $sessionMemory
     * @return array<string, mixed>|null
     */
    private function resolveTopicMemory(array $sessionMemory, string $topicReference): ?array
    {
        $topicMemories = collect(is_array($sessionMemory['topic_memories'] ?? null) ? $sessionMemory['topic_memories'] : [])
            ->filter(fn ($topic) => is_array($topic))
            ->sortByDesc(fn (array $topic) => $topic['last_activity_at'] ?? '')
            ->values();

        if ($topicMemories->isEmpty()) {
            return null;
        }

        $activeTopicKey = (string) ($sessionMemory['active_topic_key'] ?? '');
        $activeTopic = $topicMemories->first(fn (array $topic) => (string) ($topic['key'] ?? '') === $activeTopicKey)
            ?: $topicMemories->first();

        if ($topicReference === 'previous') {
            return $topicMemories->first(fn (array $topic) => (string) ($topic['key'] ?? '') !== (string) ($activeTopic['key'] ?? ''))
                ?: $activeTopic;
        }

        return $activeTopic;
    }

    private function resolveTopicDays(?array $topic, int $defaultDays): int
    {
        if (!$topic) {
            return $defaultDays;
        }

        $toolArgs = is_array($topic['tool_args'] ?? null) ? $topic['tool_args'] : [];
        $days = (int) ($toolArgs['days'] ?? 0);

        return $days > 0 ? $days : $defaultDays;
    }

    private function detectTopicReference(string $message): string
    {
        if ($this->containsAny($message, ['balik ke topik tadi', 'balik ke topik sebelumnya', 'topik sebelumnya', 'bahasan sebelumnya', 'sebelumnya yang mana'])) {
            return 'previous';
        }

        if ($this->containsAny($message, ['topik ini', 'yang tadi', 'itu', 'tadi', 'lanjut', 'terus', 'detailnya', 'kenapa', 'berapa', 'siapa', 'kapan', 'mana'])) {
            return 'active';
        }

        return 'new';
    }

    private function extractDays(string $message): ?int
    {
        if (preg_match('/(\d+)\s*(hari|day|days)/u', $message, $matches) === 1) {
            $days = (int) ($matches[1] ?? 0);
            return $days > 0 ? min($days, 365) : null;
        }

        return null;
    }

    private function defaultToolForIntent(string $intent): string
    {
        return match ($intent) {
            'domain_scope' => 'domain_registry.overview',
            'finance_overview' => 'finance.overview',
            'entity_detail' => 'entity.detail',
            'sales_trend' => 'sales.trend',
            'top_customer' => 'customer.top',
            'resource_bottleneck' => 'resource.bottleneck',
            'profit' => 'package.profitability',
            'refund' => 'refund.monitor',
            'idle_resources' => 'resource.idle',
            default => 'ops.summary',
        };
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

    private function shouldUseEntityDetailIntent(string $message, array $resolvedEntity): bool
    {
        return ($resolvedEntity['score'] ?? 0) >= 90
            || $this->containsAny($message, ['detail', 'info', 'kapasitas', 'harga', 'status', 'berapa', 'siapa', 'paket ini', 'resource ini', 'isi', 'deskripsi']);
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
            'balik ke',
        ]);
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

    private function normalize(string $message): string
    {
        return mb_strtolower(trim($message));
    }

    /**
     * @param array<string, mixed> $toolArgs
     * @param array<string, mixed> $topicContext
     * @return array<string, mixed>
     */
    private function buildRoute(
        string $intent,
        string $tool,
        float $confidence,
        string $topicKey,
        string $topicLabel,
        array $toolArgs = [],
        string $topicReference = 'new',
        array $topicContext = []
    ): array {
        return [
            'intent' => $intent,
            'tool' => $tool,
            'confidence' => $confidence,
            'topic_key' => $topicKey,
            'topic_label' => $topicLabel,
            'tool_args' => $toolArgs,
            'topic_reference' => $topicReference,
            'topic_context' => $topicContext,
        ];
    }
}
