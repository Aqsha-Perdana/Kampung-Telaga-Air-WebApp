<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm ai-hero-card mb-4">
        <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="mb-2 fw-bold text-white">
                    <i class="ti ti-sparkles me-2"></i>AI Center Blueprint
                </h2>
                <p class="mb-0 text-white-50">
                    Fondasi fitur AI Admin untuk Auto Package Composer dan Admin AI Chat Query.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge ai-badge">Feature 3: Composer</span>
                <span class="badge ai-badge">Feature 5: Admin Chat</span>
                <span class="badge ai-badge">Status: Planning Ready</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <ul class="nav nav-pills ai-flow-tabs" id="aiFlowTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#composer-flow" type="button" role="tab">
                                <i class="ti ti-box-model me-1"></i>Auto Package Composer
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#chat-flow" type="button" role="tab">
                                <i class="ti ti-message-chatbot me-1"></i>Admin AI Chat Query
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="composer-flow" role="tabpanel">
                            <div class="flow-list">
                                @foreach($composerFlow as $item)
                                    <div class="flow-item">
                                        <div class="flow-step">{{ $item['step'] }}</div>
                                        <div class="flow-content">
                                            <h6 class="mb-1">{{ $item['title'] }}</h6>
                                            <p class="mb-0 text-muted">{{ $item['description'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="chat-flow" role="tabpanel">
                            <div class="flow-list">
                                @foreach($chatFlow as $item)
                                    <div class="flow-item">
                                        <div class="flow-step">{{ $item['step'] }}</div>
                                        <div class="flow-content">
                                            <h6 class="mb-1">{{ $item['title'] }}</h6>
                                            <p class="mb-0 text-muted">{{ $item['description'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold"><i class="ti ti-database me-2 text-primary"></i>Schema Log and Insight</h5>
                    <p class="mb-0 text-muted small">Rancangan tabel awal untuk auditability, review, dan execution trace.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @foreach($schemaTables as $table)
                            <div class="col-12">
                                <div class="schema-card">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                        <h6 class="mb-0"><code>{{ $table['name'] }}</code></h6>
                                        <span class="badge bg-light text-dark border">core table</span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ $table['purpose'] }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($table['columns'] as $columnGroup)
                                            <span class="schema-chip">{{ $columnGroup }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-shield-lock me-2 text-success"></i>Guardrails</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="guardrail-item">
                        <strong>Human in the loop</strong>
                        <p class="text-muted mb-0 small">Semua aksi sensitif wajib konfirmasi admin.</p>
                    </div>
                    <div class="guardrail-item">
                        <strong>Audit trail wajib</strong>
                        <p class="text-muted mb-0 small">Setiap prompt, insight, dan action disimpan untuk audit.</p>
                    </div>
                    <div class="guardrail-item mb-0">
                        <strong>Fallback aman</strong>
                        <p class="text-muted mb-0 small">Jika confidence rendah, AI hanya memberi rekomendasi tanpa eksekusi.</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-0 fw-bold"><i class="ti ti-list-check me-2 text-primary"></i>Next Sprint MVP</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="check-item"><i class="ti ti-circle-check text-success"></i> Simpan log chat + intent</div>
                    <div class="check-item"><i class="ti ti-circle-check text-success"></i> Generate 3 rekomendasi paket draft</div>
                    <div class="check-item"><i class="ti ti-circle-check text-success"></i> Action button dari chat ke draft</div>
                    <div class="check-item mb-0"><i class="ti ti-circle-check text-success"></i> Dashboard confidence & feedback</div>
                </div>
            </div>
        </div>
    </div>
</div>