<style>
    .expense-page-shell {
        padding: 1.5rem 1rem 2rem;
    }

    .expense-hero-card,
    .expense-form-card {
        border-radius: 20px;
    }

    .expense-hero-card {
        background: #ffffff;
        border: 0;
    }

    .expense-hero-kicker {
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #2563eb;
        font-weight: 700;
    }

    .expense-hero-title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #0f172a;
    }

    .expense-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.9rem;
    }

    .expense-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .expense-form-card {
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .expense-form-card .card-header,
    .expense-form-card .card-body {
        padding-inline: 1.35rem;
    }

    .expense-form-card .card-header {
        padding-top: 1.25rem;
    }

    .expense-form-card .card-body {
        padding-bottom: 1.35rem;
    }

    .expense-page-shell .form-control,
    .expense-page-shell .form-select,
    .expense-page-shell .input-group-text {
        border-radius: 14px;
        border-color: rgba(148, 163, 184, 0.32);
        min-height: 48px;
        box-shadow: none;
    }

    .expense-page-shell .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .expense-page-shell .input-group > .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
    }

    .expense-page-shell .form-control:focus,
    .expense-page-shell .form-select:focus {
        border-color: rgba(37, 99, 235, 0.42);
        box-shadow: 0 0 0 0.24rem rgba(37, 99, 235, 0.11);
    }

    .expense-upload-shell {
        border: 1.5px dashed rgba(96, 165, 250, 0.45);
        border-radius: 18px;
        background: #f8fbff;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .expense-upload-shell.is-dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .expense-upload-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 0.9rem;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #2563eb;
        font-size: 1.45rem;
    }

    .expense-guidance-list {
        display: grid;
        gap: 0.9rem;
    }

    .expense-guidance-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: #475569;
        font-size: 0.94rem;
    }

    .expense-guidance-item i {
        margin-top: 0.18rem;
        flex-shrink: 0;
    }

    @media (max-width: 991.98px) {
        .expense-page-shell {
            padding-inline: 0.25rem;
        }
    }
</style>
