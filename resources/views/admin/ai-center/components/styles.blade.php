<style>
.ai-hero-card {
    border-radius: 24px;
    overflow: hidden;
    background:
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.2), transparent 32%),
        linear-gradient(135deg, #4f7df3 0%, #5f89f7 52%, #7aa4ff 100%);
    box-shadow: 0 18px 40px rgba(79, 125, 243, 0.18);
}

.ai-hero-card .btn {
    min-height: 38px;
}

.schema-card {
    border: 1px solid #e8eef8;
    border-radius: 18px;
    padding: 1rem;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.schema-chip {
    display: inline-flex;
    padding: 0.32rem 0.72rem;
    border-radius: 999px;
    font-size: 0.75rem;
    border: 1px solid #dbe7ff;
    background: #f8fbff;
    color: #33518f;
}

.check-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    line-height: 1.5;
}

.chat-board {
    min-height: 420px;
    max-height: 420px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    padding: 0.25rem;
}

.chat-bubble {
    max-width: 88%;
    padding: 0.95rem 1rem;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.chat-bubble.admin {
    align-self: flex-end;
    background: linear-gradient(135deg, #4f7df3 0%, #5f89f7 100%);
    color: #fff;
    border-bottom-right-radius: 8px;
}

.chat-bubble.assistant {
    align-self: flex-start;
    background: #f8fafc;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 8px;
}

.bubble-title {
    font-size: 0.74rem;
    font-weight: 700;
    margin-bottom: 0.35rem;
    opacity: 0.82;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.bubble-text {
    white-space: pre-line;
    line-height: 1.6;
}

.bubble-meta {
    margin-top: 0.55rem;
    font-size: 0.77rem;
    opacity: 0.72;
}

.session-list {
    display: grid;
    gap: 0.85rem;
}

.session-card {
    width: 100%;
    border: 1px solid #e6edf8;
    border-radius: 18px;
    background: #fff;
    padding: 0.95rem 1rem;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 0.32rem;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.session-card:hover {
    transform: translateY(-1px);
    border-color: #c6d6ff;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
}

.session-card.active {
    border-color: #4f7df3;
    background: linear-gradient(180deg, #f4f8ff 0%, #ffffff 100%);
    box-shadow: 0 16px 30px rgba(79, 125, 243, 0.12);
}

.session-title {
    font-weight: 700;
    color: #0f172a;
}

.session-preview {
    font-size: 0.82rem;
    color: #526175;
    line-height: 1.45;
}

.session-meta,
.session-empty {
    font-size: 0.78rem;
    color: #6b7a90;
}

.session-empty {
    padding: 1rem;
    border: 1px dashed #cfd9e8;
    border-radius: 18px;
    background: #f8fafc;
}

@media (max-width: 991.98px) {
    .ai-hero-card .card-body {
        padding: 1.3rem;
    }

    .chat-board {
        min-height: 320px;
        max-height: 320px;
    }
}
</style>
