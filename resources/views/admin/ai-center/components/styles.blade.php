@section('styles')
<style>
.ai-hero-card {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 45%, #0ea5e9 100%);
}
.ai-badge {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.28);
    font-weight: 500;
}
.ai-flow-tabs .nav-link {
    border-radius: 10px;
    color: #475569;
}
.ai-flow-tabs .nav-link.active {
    background: #2563eb;
    color: #fff;
}
.flow-list {
    display: grid;
    gap: 0.85rem;
}
.flow-item {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.85rem;
    background: #fff;
}
.flow-step {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.schema-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.9rem;
    background: #fff;
}
.schema-chip {
    display: inline-flex;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
}
.guardrail-item {
    padding: 0.75rem 0;
    border-bottom: 1px dashed #e2e8f0;
}
.check-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e2e8f0;
}
@media (max-width: 991.98px) {
    .ai-hero-card .card-body {
        padding: 1.25rem;
    }
}
</style>
@endsection