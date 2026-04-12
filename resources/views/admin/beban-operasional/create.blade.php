@extends('layout.sidebar')

@section('content')
@include('admin.beban-operasional.partials.form-styles')

<div class="container-fluid expense-page-shell">
    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
        <div class="fw-semibold mb-2">Please review the highlighted fields.</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm border-0 expense-hero-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <div class="expense-hero-kicker mb-2">Financial Input</div>
                    <h4 class="expense-hero-title mb-1">Create Operating Expense</h4>
                    <p class="text-muted small mb-0">Record new operational expenses with the same structure used across the admin workspace.</p>
                    <div class="expense-hero-meta">
                        <span class="expense-hero-chip"><i class="bi bi-wallet2"></i> Payment-ready fields</span>
                        <span class="expense-hero-chip"><i class="bi bi-file-earmark-lock2"></i> Optional proof upload</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('beban-operasional.index') }}" class="btn btn-light border btn-sm">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('beban-operasional.store') }}" method="POST" enctype="multipart/form-data" id="expenseForm">
        @csrf
        @php($submitLabel = 'Save Expense')
        @include('admin.beban-operasional.partials.form-fields')
    </form>
</div>

@include('admin.beban-operasional.partials.form-scripts')
@endsection
