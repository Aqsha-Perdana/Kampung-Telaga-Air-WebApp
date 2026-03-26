<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AICenterController extends Controller
{
    public function index(): View
    {
        $composerFlow = [
            [
                'step' => '1',
                'title' => 'Set Objective',
                'description' => 'Admin memilih tujuan: naikkan okupansi, maksimalkan profit, atau isi slot tanggal tertentu.',
            ],
            [
                'step' => '2',
                'title' => 'Apply Constraints',
                'description' => 'Admin menetapkan batasan seperti range harga, durasi paket, resource wajib, dan margin minimal.',
            ],
            [
                'step' => '3',
                'title' => 'AI Generate Options',
                'description' => 'AI menghasilkan 3-5 kandidat paket dengan estimasi modal, harga saran, margin, dan confidence score.',
            ],
            [
                'step' => '4',
                'title' => 'Validation Guardrails',
                'description' => 'Sistem cek konflik kapasitas, resource nonaktif, duplikasi kombinasi, dan risiko harga terlalu rendah.',
            ],
            [
                'step' => '5',
                'title' => 'Review and Approve',
                'description' => 'Admin memilih kandidat terbaik lalu simpan sebagai draft atau publish dengan approval step.',
            ],
        ];

        $chatFlow = [
            [
                'step' => '1',
                'title' => 'Ask in Natural Language',
                'description' => 'Admin bertanya langsung seperti: paket paling profit minggu ini atau alasan refund meningkat.',
            ],
            [
                'step' => '2',
                'title' => 'Intent Detection',
                'description' => 'AI mengklasifikasikan query: insight, diagnosis, atau action request.',
            ],
            [
                'step' => '3',
                'title' => 'Data Retrieval',
                'description' => 'Sistem mengambil data terstruktur dari order, calendar, refund, dan resource untuk periode yang relevan.',
            ],
            [
                'step' => '4',
                'title' => 'Answer with Evidence',
                'description' => 'AI memberi jawaban ringkas + angka inti + rekomendasi tindakan + confidence.',
            ],
            [
                'step' => '5',
                'title' => 'Safe Action Confirmation',
                'description' => 'Aksi penting harus melewati konfirmasi admin dan semua langkah dicatat di action log.',
            ],
        ];

        $schemaTables = [
            [
                'name' => 'ai_insights',
                'purpose' => 'Menyimpan insight/rekomendasi hasil AI (Composer atau Chat) untuk review dan tracking status.',
                'columns' => [
                    'id, admin_id, source_channel, insight_type, title',
                    'summary, period_start, period_end',
                    'metrics_json, recommendation_json',
                    'confidence_score, status, reviewed_at',
                    'created_at, updated_at',
                ],
            ],
            [
                'name' => 'ai_chat_logs',
                'purpose' => 'Audit percakapan admin dengan AI, termasuk intent, konteks, dan metrik usage.',
                'columns' => [
                    'id, session_id, admin_id, role, message',
                    'intent, context_json, model',
                    'prompt_tokens, completion_tokens, latency_ms',
                    'created_at, updated_at',
                ],
            ],
            [
                'name' => 'ai_action_logs',
                'purpose' => 'Mencatat aksi yang disarankan/diambil dari AI (mis. generate draft package, publish, export).',
                'columns' => [
                    'id, admin_id, source_channel, action_key',
                    'target_type, target_id, payload_json',
                    'confirmation_state, result_message, executed_at',
                    'created_at, updated_at',
                ],
            ],
        ];

        return view('admin.ai-center.index', [
            'composerFlow' => $composerFlow,
            'chatFlow' => $chatFlow,
            'schemaTables' => $schemaTables,
        ]);
    }
}
