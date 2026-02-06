@extends('landing.layout')

@section('content')
<section class="py-5" style="margin-top: 80px;">
    <div class="container">

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-clock-history me-1"></i> Order History
                </h2>
                <p class="text-muted mb-0">
                    View all your orders and transaction details
                </p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <h3 class="fw-bold text-primary mb-1">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total Orders</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <h3 class="fw-bold text-success mb-1">{{ $stats['paid'] }}</h3>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <h3 class="fw-bold text-warning mb-1">{{ $stats['pending'] }}</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <h3 class="fw-bold text-danger mb-1">{{ $stats['failed'] }}</h3>
                        <small class="text-muted">Failed / Cancelled</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('status') ? 'active' : '' }}"
                           href="{{ route('orders.history') }}">
                            All Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'paid' ? 'active' : '' }}"
                           href="{{ route('orders.history', ['status' => 'paid']) }}">
                            <i class="bi bi-check-circle"></i> Completed
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'pending' ? 'active' : '' }}"
                           href="{{ route('orders.history', ['status' => 'pending']) }}">
                            <i class="bi bi-clock"></i> Pending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ in_array(request('status'), ['failed', 'cancelled']) ? 'active' : '' }}"
                           href="{{ route('orders.history', ['status' => 'failed']) }}">
                            <i class="bi bi-x-circle"></i> Failed / Cancelled
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <div class="col-12">

                @if($orders->isEmpty())
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Orders Yet</h5>
                            <p class="text-muted mb-3">
                                Start exploring our travel packages and make your first booking.
                            </p>
                            <a href="{{ route('landing.paket-wisata') }}" class="btn btn-primary">
                                <i class="bi bi-search"></i> Browse Packages
                            </a>
                        </div>
                    </div>
                @else

                    @foreach($orders as $order)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">

                                <!-- Order Information -->
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <h5 class="mb-0 fw-semibold">{{ $order->id_order }}</h5>

                                        @if($order->status === 'paid')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Completed
                                            </span>
                                        @elseif($order->status === 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @elseif($order->status === 'failed')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Failed
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $order->created_at->format('d M Y, H:i') }}
                                    </p>

                                    @foreach($order->items as $item)
                                        <div class="small mb-1">
                                            <i class="bi bi-box-seam"></i>
                                            <strong>{{ $item->nama_paket }}</strong>
                                            <span class="text-muted">× {{ $item->jumlah_peserta }}</span>
                                        </div>
                                    @endforeach

                                    @if($order->redeem_code)
                                        <div class="alert alert-success py-2 mt-3 mb-0">
                                            <i class="bi bi-ticket-perforated"></i>
                                            <strong>Redeem Code:</strong>
                                            <span class="font-monospace">{{ $order->redeem_code }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Amount & Actions -->
                                <div class="col-md-4 text-md-end">
                                    <h4 class="fw-bold text-primary mb-3">
                                        {{ $order->currency }} {{ number_format($order->total_amount, 2) }}
                                    </h4>

                                    <div class="d-grid gap-2">
                                        <a href="{{ route('orders.show', $order->id_order) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>

                                        @if($order->status === 'paid')
                                            <a href="{{ route('invoice.download', $order->id_order) }}"
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-download"></i> Download Invoice
                                            </a>
                                        @endif

                                        @if($order->status === 'pending')
                                            <form action="{{ route('orders.cancel', $order->id_order) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                    <i class="bi bi-x-circle"></i> Cancel Order
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>

                @endif
            </div>
        </div>

    </div>
</section>
@endsection
