<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\AiChatSessionMemory;
use App\Services\AI\SessionMemoryService;
use App\Services\AdminAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AICenterController extends Controller
{
    public function __construct(
        private readonly AdminAIChatService $chatService,
        private readonly SessionMemoryService $sessionMemoryService
    ) {
    }

    public function index(): View
    {
        $adminId = Auth::guard('admin')->id();
        $aiTablesReady = $this->aiTablesReady();
        $recentSessions = $aiTablesReady && $adminId ? $this->recentSessionsForAdmin((int) $adminId) : [];
        $activeSessionId = $recentSessions[0]['session_id'] ?? null;
        $activeMessages = $activeSessionId && $adminId
            ? $this->sessionMessagesForAdmin((string) $activeSessionId, (int) $adminId)
            : [];
        $activeSessionMemory = $activeSessionId && $adminId
            ? $this->sessionMemoryService->payload((string) $activeSessionId, (int) $adminId)
            : [];
        $usageOverview = $adminId && $aiTablesReady ? $this->usageOverviewForAdmin((int) $adminId) : [
            'total_sessions' => 0,
            'total_questions' => 0,
            'active_this_week' => 0,
            'top_topic' => 'No data yet',
        ];

        $capabilityGroups = [
            [
                'title' => 'Sales and Orders',
                'description' => 'Useful for checking sales trends, package performance, high-value customers, and refund patterns.',
            ],
            [
                'title' => 'Resources and Capacity',
                'description' => 'Helps review bottlenecks across boats, homestays, culinary vendors, kiosks, and resource details.',
            ],
            [
                'title' => 'Operational Finance',
                'description' => 'Can summarize revenue, profit, operating expenses, and overall financial health in a quick format.',
            ],
        ];

        $usageGuide = [
            'Use the floating AI icon on admin pages to ask quick questions without leaving the current screen.',
            'Come to AI Center when you want to review past conversations or revisit older sessions.',
            'Start with a short question, then continue with follow-ups such as "why did it drop?" or "what is the price?".',
        ];

        $sampleQuestions = [
            'How did sales perform over the last 7 days compared to the previous 7 days?',
            'Who is the highest-value customer in the last 90 days?',
            'Summarize the financial condition for the last 30 days.',
            'Which resource is most likely to hit a bottleneck this week?',
        ];

        return view('admin.ai-center.index', [
            'aiTablesReady' => $aiTablesReady,
            'sampleQuestions' => $sampleQuestions,
            'recentSessions' => $recentSessions,
            'activeSessionId' => $activeSessionId,
            'activeMessages' => $activeMessages,
            'activeSessionMemory' => $activeSessionMemory,
            'usageOverview' => $usageOverview,
            'capabilityGroups' => $capabilityGroups,
            'usageGuide' => $usageGuide,
            'historyUrlTemplate' => route('admin.ai-center.history', ['sessionId' => '__SESSION__']),
            'clearHistoryUrl' => route('admin.ai-center.clear-history'),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:100',
        ]);

        try {
            if (!$this->aiTablesReady()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI chat log table is not ready yet. Please run the migration first.',
                ], 503);
            }

            $admin = Auth::guard('admin')->user();
            $adminId = (int) ($admin?->id ?? 0);
            $message = trim((string) $request->input('message'));
            $sessionId = trim((string) $request->input('session_id', (string) Str::uuid()));
            $startedAt = microtime(true);

            $adminLog = AiChatLog::create([
                'session_id' => $sessionId,
                'admin_id' => $admin?->id,
                'role' => 'admin',
                'message' => $message,
                'intent' => null,
                'context_json' => [
                    'source' => 'ai_center_chat',
                ],
                'model' => 'internal-ops-v1',
            ]);

            $history = $adminId > 0 ? $this->historyPayloadForAdmin($sessionId, $adminId) : [];
            $sessionMemory = $adminId > 0 ? $this->sessionMemoryService->payload($sessionId, $adminId) : [];
            $reply = $this->chatService->reply($message, $history, $sessionMemory);
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            $adminLog->update([
                'intent' => $reply['intent'],
            ]);

            AiChatLog::create([
                'session_id' => $sessionId,
                'admin_id' => $admin?->id,
                'role' => 'assistant',
                'message' => $reply['answer'],
                'intent' => $reply['intent'],
                'context_json' => $reply['context'],
                'model' => $reply['model'],
                'latency_ms' => $latencyMs,
            ]);

            if ($adminId > 0) {
                $this->sessionMemoryService->refreshFromLogs($sessionId, $adminId);
            }

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'message' => $reply['answer'],
                'intent' => $reply['intent'],
                'confidence_score' => $reply['confidence'],
                'context' => $reply['context'],
                'session' => $adminId > 0 ? $this->buildSessionSummary($sessionId, $adminId) : null,
                'session_memory' => $adminId > 0 ? $this->sessionMemoryService->payload($sessionId, $adminId) : [],
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'AI chat could not be processed: ' . $exception->getMessage(),
            ], 500);
        }
    }

    public function history(string $sessionId): JsonResponse
    {
        try {
            if (!$this->aiTablesReady()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI chat log table is not ready yet. Please run the migration first.',
                ], 503);
            }

            $adminId = (int) Auth::guard('admin')->id();
            $summary = $this->buildSessionSummary($sessionId, $adminId);

            if ($summary === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session history was not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'session' => $summary,
                'messages' => $this->sessionMessagesForAdmin($sessionId, $adminId),
                'session_memory' => $this->sessionMemoryService->payload($sessionId, $adminId),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Session history could not be loaded: ' . $exception->getMessage(),
            ], 500);
        }
    }

    public function clearHistory(Request $request): JsonResponse
    {
        try {
            if (!$this->aiTablesReady()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI chat log table is not ready yet. Please run the migration first.',
                ], 503);
            }

            $adminId = (int) Auth::guard('admin')->id();
            if ($adminId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin account was not found.',
                ], 401);
            }

            $deletedLogs = AiChatLog::query()
                ->where('admin_id', $adminId)
                ->delete();

            $deletedMemories = 0;
            if (Schema::hasTable('ai_chat_session_memories')) {
                $deletedMemories = AiChatSessionMemory::query()
                    ->where('admin_id', $adminId)
                    ->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin AI history has been cleared successfully.',
                'deleted' => [
                    'logs' => (int) $deletedLogs,
                    'memories' => (int) $deletedMemories,
                ],
                'usage_overview' => $this->usageOverviewForAdmin($adminId),
                'recent_sessions' => [],
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'AI history could not be cleared: ' . $exception->getMessage(),
            ], 500);
        }
    }

    private function aiTablesReady(): bool
    {
        return Schema::hasTable('ai_chat_logs');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentSessionsForAdmin(int $adminId, int $limit = 6): array
    {
        $sessionIds = AiChatLog::query()
            ->where('admin_id', $adminId)
            ->select('session_id', DB::raw('MAX(created_at) as last_activity_at'))
            ->groupBy('session_id')
            ->orderByDesc('last_activity_at')
            ->limit($limit)
            ->pluck('session_id');

        return $sessionIds
            ->map(fn (string $sessionId) => $this->buildSessionSummary($sessionId, $adminId))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{role:string,message:string,meta:string}>
     */
    private function sessionMessagesForAdmin(string $sessionId, int $adminId): array
    {
        return AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(function (AiChatLog $log) {
                return [
                    'role' => (string) $log->role,
                    'message' => (string) $log->message,
                    'meta' => $this->formatMessageMeta($log),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSessionSummary(string $sessionId, int $adminId): ?array
    {
        $logs = AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $firstAdminMessage = $logs->firstWhere('role', 'admin');
        $lastMessage = $logs->last();
        $memory = $this->sessionMemoryService->payload($sessionId, $adminId);
        $activeTopicLabel = $memory['active_topic']['label'] ?? null;

        return [
            'session_id' => $sessionId,
            'title' => Str::limit((string) ($activeTopicLabel ?: ($firstAdminMessage?->message ?? 'Sesi chat admin')), 64),
            'preview' => Str::limit((string) ($lastMessage?->message ?? ''), 84),
            'message_count' => $logs->count(),
            'last_activity_label' => $lastMessage?->created_at?->diffForHumans() ?? '',
            'last_role' => (string) ($lastMessage?->role ?? 'assistant'),
            'memory_summary' => $memory['summary_text'] ?? '',
        ];
    }

    private function formatMessageMeta(AiChatLog $log): string
    {
        $parts = [];

        if ($log->role === 'assistant' && $log->intent) {
            $parts[] = 'Topic: ' . Str::headline(str_replace('_', ' ', (string) $log->intent));
        }

        if ($log->created_at) {
            $parts[] = $log->created_at->format('d M Y H:i');
        }

        return implode(' | ', $parts);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function historyPayloadForAdmin(string $sessionId, int $adminId, int $limit = 12): array
    {
        return AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('session_id', $sessionId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(function (AiChatLog $log) {
                return [
                    'role' => (string) $log->role,
                    'message' => (string) $log->message,
                    'intent' => $log->intent ? (string) $log->intent : null,
                    'context' => is_array($log->context_json) ? $log->context_json : [],
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, int|string>
     */
    private function usageOverviewForAdmin(int $adminId): array
    {
        $totalSessions = (int) AiChatLog::query()
            ->where('admin_id', $adminId)
            ->distinct('session_id')
            ->count('session_id');

        $totalQuestions = (int) AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('role', 'admin')
            ->count();

        $activeThisWeek = (int) AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->distinct('session_id')
            ->count('session_id');

        $topIntent = AiChatLog::query()
            ->where('admin_id', $adminId)
            ->where('role', 'assistant')
            ->whereNotNull('intent')
            ->select('intent', DB::raw('COUNT(*) as total'))
            ->groupBy('intent')
            ->orderByDesc('total')
            ->value('intent');

        return [
            'total_sessions' => $totalSessions,
            'total_questions' => $totalQuestions,
            'active_this_week' => $activeThisWeek,
            'top_topic' => $topIntent ? Str::headline(str_replace('_', ' ', (string) $topIntent)) : 'No data yet',
        ];
    }
}
