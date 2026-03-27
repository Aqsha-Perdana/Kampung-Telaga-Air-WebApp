<?php

namespace App\Services\AI;

use App\Models\AiChatLog;
use App\Models\AiChatSessionMemory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SessionMemoryService
{
    /**
     * @return array<string, mixed>
     */
    public function payload(string $sessionId, int $adminId): array
    {
        if (!$this->isReady() || $adminId <= 0) {
            return [];
        }

        $memory = AiChatSessionMemory::query()
            ->where('session_id', $sessionId)
            ->where('admin_id', $adminId)
            ->first();

        if (!$memory) {
            return [];
        }

        $topicMemories = is_array($memory->topic_memories_json) ? $memory->topic_memories_json : [];
        $activeTopicKey = $memory->active_topic_key ? (string) $memory->active_topic_key : null;
        $activeTopic = collect($topicMemories)->first(function ($topic) use ($activeTopicKey) {
            return is_array($topic) && (string) ($topic['key'] ?? '') === (string) $activeTopicKey;
        });

        return [
            'summary_text' => (string) ($memory->summary_text ?? ''),
            'topics' => is_array($memory->topics_json) ? $memory->topics_json : [],
            'entities' => is_array($memory->entities_json) ? $memory->entities_json : [],
            'active_topic_key' => $activeTopicKey,
            'active_topic' => is_array($activeTopic) ? $activeTopic : null,
            'topic_memories' => $topicMemories,
            'latest_intent' => $memory->latest_intent ? (string) $memory->latest_intent : null,
            'latest_context' => is_array($memory->latest_context_json) ? $memory->latest_context_json : [],
            'message_count' => (int) ($memory->message_count ?? 0),
            'last_activity_at' => $memory->last_activity_at?->toIso8601String(),
        ];
    }

    public function refreshFromLogs(string $sessionId, int $adminId): void
    {
        if (!$this->isReady() || $adminId <= 0) {
            return;
        }

        $logs = AiChatLog::query()
            ->where('session_id', $sessionId)
            ->where('admin_id', $adminId)
            ->orderByDesc('created_at')
            ->limit(40)
            ->get()
            ->reverse()
            ->values();

        if ($logs->isEmpty()) {
            return;
        }

        $latestAssistant = $logs->last(function (AiChatLog $log) {
            return $log->role === 'assistant';
        });

        $topicMemories = $this->buildTopicMemories($logs);
        $activeTopic = $topicMemories[0] ?? null;
        $topics = collect($topicMemories)
            ->pluck('intent')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $entities = collect($topicMemories)
            ->flatMap(fn (array $topic) => $topic['entities'] ?? [])
            ->filter(fn ($entity) => is_array($entity))
            ->unique(fn (array $entity) => ($entity['type'] ?? '') . '|' . ($entity['id'] ?? '') . '|' . ($entity['name'] ?? ''))
            ->values()
            ->all();

        AiChatSessionMemory::query()->updateOrCreate(
            [
                'session_id' => $sessionId,
                'admin_id' => $adminId,
            ],
            [
                'summary_text' => $this->buildSummaryText($logs, $topicMemories, $activeTopic, $entities),
                'topics_json' => $topics,
                'entities_json' => $entities,
                'active_topic_key' => $activeTopic['key'] ?? null,
                'topic_memories_json' => $topicMemories,
                'latest_intent' => $latestAssistant?->intent,
                'latest_context_json' => is_array($latestAssistant?->context_json) ? $latestAssistant->context_json : [],
                'message_count' => $logs->count(),
                'last_activity_at' => $logs->last()?->created_at,
            ]
        );
    }

    public function isReady(): bool
    {
        return Schema::hasTable('ai_chat_session_memories');
    }

    /**
     * @param Collection<int, AiChatLog> $logs
     * @return array<int, array<string, mixed>>
     */
    private function buildTopicMemories(Collection $logs): array
    {
        $topics = [];

        foreach ($logs as $index => $log) {
            $context = is_array($log->context_json) ? $log->context_json : [];
            $topicContext = $log->role === 'admin'
                ? ($this->linkedAssistantContext($logs, $index, $log) ?? $context)
                : $context;
            $topicKey = $this->resolveTopicKey($log, $topicContext);

            if ($topicKey === null) {
                continue;
            }

            if (!isset($topics[$topicKey])) {
                $topics[$topicKey] = [
                    'key' => $topicKey,
                    'intent' => (string) ($log->intent ?? 'operations_summary'),
                    'label' => $this->resolveTopicLabel($log, $topicContext),
                    'summary' => '',
                    'entities' => [],
                    'message_count' => 0,
                    'admin_turns' => 0,
                    'assistant_turns' => 0,
                    'last_admin_message' => '',
                    'last_assistant_message' => '',
                    'latest_context' => [],
                    'tool' => (string) ($topicContext['tool'] ?? $this->defaultToolForIntent((string) ($log->intent ?? 'operations_summary'))),
                    'tool_args' => is_array($topicContext['tool_args'] ?? null) ? $topicContext['tool_args'] : [],
                    'last_activity_at' => null,
                ];
            }

            $topics[$topicKey]['message_count']++;
            $topics[$topicKey]['last_activity_at'] = $log->created_at?->toIso8601String();

            foreach ($this->extractEntities($topicContext) as $entity) {
                $topics[$topicKey]['entities'][] = $entity;
            }

            if ($log->role === 'admin') {
                $topics[$topicKey]['admin_turns']++;
                $topics[$topicKey]['last_admin_message'] = (string) $log->message;
                continue;
            }

            $topics[$topicKey]['assistant_turns']++;
            $topics[$topicKey]['last_assistant_message'] = (string) $log->message;
            $topics[$topicKey]['latest_context'] = $context;
            $topics[$topicKey]['tool'] = (string) ($context['tool'] ?? $topics[$topicKey]['tool']);
            $topics[$topicKey]['tool_args'] = is_array($context['tool_args'] ?? null)
                ? $context['tool_args']
                : $topics[$topicKey]['tool_args'];

            if ($topics[$topicKey]['label'] === '') {
                $topics[$topicKey]['label'] = $this->resolveTopicLabel($log, $context);
            }

            if (!empty($context['topic_label'])) {
                $topics[$topicKey]['label'] = (string) $context['topic_label'];
            }

            if (!empty($log->intent)) {
                $topics[$topicKey]['intent'] = (string) $log->intent;
            }

            $previousAdminMessage = $this->findPreviousAdminMessage($logs, $index);
            if ($topics[$topicKey]['last_admin_message'] === '' && $previousAdminMessage !== '') {
                $topics[$topicKey]['last_admin_message'] = $previousAdminMessage;
            }
        }

        $topicMemories = collect($topics)
            ->map(function (array $topic) {
                $topic['entities'] = collect($topic['entities'] ?? [])
                    ->filter(fn ($entity) => is_array($entity))
                    ->unique(fn (array $entity) => ($entity['type'] ?? '') . '|' . ($entity['id'] ?? '') . '|' . ($entity['name'] ?? ''))
                    ->values()
                    ->all();
                $topic['summary'] = $this->buildTopicSummary($topic);

                return $topic;
            })
            ->sortByDesc(fn (array $topic) => $topic['last_activity_at'] ?? '')
            ->values()
            ->all();

        return $topicMemories;
    }

    /**
     * @param Collection<int, AiChatLog> $logs
     * @param array<int, array<string, mixed>> $topicMemories
     * @param array<string, mixed>|null $activeTopic
     * @param array<int, array<string, mixed>> $entities
     */
    private function buildSummaryText(Collection $logs, array $topicMemories, ?array $activeTopic, array $entities): string
    {
        $firstAdminMessage = $logs->firstWhere('role', 'admin');
        $sessionStarter = Str::limit((string) ($firstAdminMessage?->message ?? 'admin conversation'), 80);

        if ($activeTopic === null) {
            return 'The session started with: ' . $sessionStarter . '. Topic memory is not fully formed yet, but the chat history has already been saved.';
        }

        $otherTopics = collect($topicMemories)
            ->skip(1)
            ->pluck('label')
            ->filter()
            ->take(3)
            ->all();

        $entityNames = collect($entities)
            ->pluck('name')
            ->filter()
            ->take(3)
            ->all();

        $parts = [
            'Current focus: ' . ($activeTopic['label'] ?? 'Active topic'),
            'Summary: ' . ($activeTopic['summary'] ?? 'No summary yet'),
        ];

        if (!empty($otherTopics)) {
            $parts[] = 'Other saved topics: ' . implode(', ', $otherTopics);
        }

        if (!empty($entityNames)) {
            $parts[] = 'Key entities in this session: ' . implode(', ', $entityNames);
        }

        $parts[] = 'Conversation started with: ' . $sessionStarter;

        return implode('. ', $parts) . '.';
    }

    /**
     * @param array<string, mixed> $topic
     */
    private function buildTopicSummary(array $topic): string
    {
        $assistantMessage = trim((string) ($topic['last_assistant_message'] ?? ''));
        $adminMessage = trim((string) ($topic['last_admin_message'] ?? ''));
        $entities = collect($topic['entities'] ?? [])->pluck('name')->filter()->take(2)->all();

        $parts = [];

        if ($assistantMessage !== '') {
            $parts[] = Str::limit($assistantMessage, 140);
        }

        if ($adminMessage !== '') {
            $parts[] = 'Triggered by: ' . Str::limit($adminMessage, 80);
        }

        if (!empty($entities)) {
            $parts[] = 'Entity: ' . implode(', ', $entities);
        }

        return implode(' ', $parts);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    private function extractEntities(array $context): array
    {
        $entities = [];
        $detail = $context['detail'] ?? null;

        if (is_array($detail) && !empty($detail['name']) && !empty($detail['type'])) {
            $entities[] = [
                'id' => (string) ($detail['id'] ?? $detail['name']),
                'type' => (string) $detail['type'],
                'name' => (string) $detail['name'],
            ];
        }

        return $entities;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveTopicKey(AiChatLog $log, array $context): ?string
    {
        if (!empty($context['topic_key'])) {
            return (string) $context['topic_key'];
        }

        if ($log->intent === null) {
            return null;
        }

        if (($log->intent ?? null) === 'entity_detail') {
            $detail = $context['detail'] ?? null;
            if (is_array($detail) && !empty($detail['type']) && !empty($detail['id'])) {
                return 'entity_detail:' . $detail['type'] . ':' . $detail['id'];
            }
        }

        if (in_array($log->intent, ['finance_overview', 'profit', 'refund'], true)) {
            $days = $this->resolveDaysFromContext($context);
            return $days !== null ? $log->intent . ':' . $days . 'd' : (string) $log->intent;
        }

        return (string) $log->intent;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveTopicLabel(AiChatLog $log, array $context): string
    {
        if (!empty($context['topic_label'])) {
            return (string) $context['topic_label'];
        }

        if (($log->intent ?? null) === 'entity_detail') {
            $detail = $context['detail'] ?? null;
            if (is_array($detail) && !empty($detail['name'])) {
                return 'Detail ' . $detail['name'];
            }
        }

        if (in_array($log->intent, ['finance_overview', 'profit', 'refund'], true)) {
            $days = $this->resolveDaysFromContext($context);
            if ($days !== null) {
                return Str::headline(str_replace('_', ' ', (string) $log->intent)) . ' ' . $days . ' Hari';
            }
        }

        return Str::headline(str_replace('_', ' ', (string) ($log->intent ?? 'operations_summary')));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveDaysFromContext(array $context): ?int
    {
        $toolArgs = is_array($context['tool_args'] ?? null) ? $context['tool_args'] : [];
        $days = (int) ($toolArgs['days'] ?? 0);

        if ($days > 0) {
            return $days;
        }

        if (!empty($context['period_start']) && !empty($context['period_end'])) {
            return Carbon::parse((string) $context['period_start'])
                ->diffInDays(Carbon::parse((string) $context['period_end'])) + 1;
        }

        return null;
    }

    /**
     * @param Collection<int, AiChatLog> $logs
     */
    private function findPreviousAdminMessage(Collection $logs, int $currentIndex): string
    {
        for ($index = $currentIndex; $index >= 0; $index--) {
            $log = $logs[$index] ?? null;
            if ($log instanceof AiChatLog && $log->role === 'admin') {
                return (string) $log->message;
            }
        }

        return '';
    }

    /**
     * @param Collection<int, AiChatLog> $logs
     * @return array<string, mixed>|null
     */
    private function linkedAssistantContext(Collection $logs, int $currentIndex, AiChatLog $currentLog): ?array
    {
        $nextLog = $logs[$currentIndex + 1] ?? null;

        if (
            $nextLog instanceof AiChatLog
            && $nextLog->role === 'assistant'
            && (string) $nextLog->intent === (string) $currentLog->intent
            && is_array($nextLog->context_json)
        ) {
            return $nextLog->context_json;
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
}
