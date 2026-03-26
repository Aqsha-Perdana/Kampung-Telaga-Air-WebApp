@extends('landing.layout')

@section('content')
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                        </div>

                        <h2 class="mb-3">Payment Failed</h2>
                        <p class="text-muted mb-4">
                            We could not complete your payment. Please try again.
                        </p>

                        @if(!empty($order))
                            <div class="alert alert-light border text-start">
                                <h5 class="mb-3">
                                    <i class="bi bi-receipt"></i> Order Details
                                </h5>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Order ID:</div>
                                    <div class="col-8"><strong class="font-monospace">{{ $order->id_order }}</strong></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Status:</div>
                                    <div class="col-8"><strong class="text-capitalize">{{ $order->status }}</strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-4 text-muted">Amount:</div>
                                    <div class="col-8"><strong>RM {{ number_format($order->base_amount, 2) }}</strong></div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> Try Again
                            </a>
                            @if(!empty($order))
                                <a href="{{ route('orders.show', $order->id_order) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Order Details
                                </a>
                            @endif
                            <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-list-ul"></i> Order History
                            </a>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('home') }}" class="btn btn-link">
                                <i class="bi bi-house"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
