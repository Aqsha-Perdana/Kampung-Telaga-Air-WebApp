@extends('landing.layout')

@section('content')
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if($order->status === 'paid')
                    {{-- Payment Success --}}
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="mb-3">Payment Successful!</h2>
                            <p class="text-muted mb-4">Thank you for your order.</p>

                            @if($order->redeem_code)
                                {{-- Redeem code generated --}}
                                <div class="alert alert-success text-start mt-3">
                                    <h5 class="mb-3">
                                        <i class="bi bi-ticket-perforated"></i> Your Redemption Code
                                    </h5>
                                    <div class="bg-white p-3 rounded text-center mb-3">
                                        <p class="fs-3 fw-bold mb-1 font-monospace text-success">
                                            {{ $order->redeem_code }}
                                        </p>
                                        <button class="btn btn-sm btn-outline-success" onclick="copyRedeemCode()">
                                            <i class="bi bi-clipboard"></i> Copy Code
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        Present this code to the service providers involved in your tour package.
                                    </small>
                                </div>
                            @else
                                {{-- Redeem code not generated yet --}}
                                <div class="alert alert-warning text-start mt-3">
                                    <h5 class="mb-2">
                                        <i class="bi bi-hourglass-split"></i> Processing Your Redemption Code
                                    </h5>
                                    <p class="mb-2">
                                        Your redemption code is being generated. This usually takes a few seconds.
                                    </p>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 100%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-clock"></i> Page will auto-refresh in a moment...
                                    </small>
                                </div>
                            @endif
                            
                            {{-- Order Information --}}
                            <div class="alert alert-light text-start border mt-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-receipt"></i> Order Details
                                </h5>
                                
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Order ID:</div>
                                    <div class="col-8">
                                        <strong class="font-monospace">{{ $order->id_order }}</strong>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Payment Date:</div>
                                    <div class="col-8">
                                        <strong>{{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</strong>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Payment Method:</div>
                                    <div class="col-8">
                                        <strong>Credit/Debit Card (Stripe)</strong>
                                    </div>
                                </div>

                                <hr>

                                {{-- Payment Amount --}}
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">Amount Charged:</div>
                                    <div class="col-8">
                                        <strong class="text-success fs-5">
                                            RM {{ number_format($order->base_amount, 2) }}
                                        </strong>
                                        <span class="badge bg-success ms-2">MYR</span>
                                    </div>
                                </div>

                                {{-- Display Amount (if different) --}}
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                    <div class="row mb-2">
                                        <div class="col-4 text-muted">Display Amount:</div>
                                        <div class="col-8">
                                            <span class="text-muted">
                                                ≈ {{ $order->display_currency }} 
                                                {{ $order->display_currency === 'IDR' || $order->display_currency === 'JPY' 
                                                    ? number_format($order->display_amount, 0) 
                                                    : number_format($order->display_amount, 2) }}
                                            </span>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                (Reference only, actual charge in MYR)
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <hr>

                                <div class="row">
                                    <div class="col-4 text-muted">Email:</div>
                                    <div class="col-8">
                                        <strong>{{ $order->customer_email }}</strong>
                                        <br>
                                    </div>
                                </div>
                            </div>

                            {{-- Order Items --}}
                            @if($order->items && $order->items->count() > 0)
                            <div class="alert alert-light text-start border mt-3">
                                <h6 class="mb-3">
                                    <i class="bi bi-list-check"></i> Package Details
                                </h6>
                                @foreach($order->items as $item)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $item->nama_paket }}</strong>
                                            <div class="text-muted small">
                                                <i class="bi bi-calendar-event"></i> 
                                                {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-people"></i> {{ $item->jumlah_peserta }} participant(s)
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <strong>RM {{ number_format($item->subtotal, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ route('orders.history') }}" class="btn btn-primary">
                                    <i class="bi bi-list-ul"></i> View Order History
                                </a>
                                <a href="{{ route('orders.show', $order->id_order) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Order Details
                                </a>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('home') }}" class="btn btn-link">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                            </div>
                            
                            @if(!$order->redeem_code)
                                {{-- Auto refresh if no redeem code yet --}}
                                <script>
                                    let refreshCount = 0;
                                    const maxRefresh = 10; // Max 10 refreshes (20 seconds)
                                    
                                    function checkRedeemCode() {
                                        if (refreshCount >= maxRefresh) {
                                            console.log('Max refresh attempts reached');
                                            return;
                                        }
                                        
                                        refreshCount++;
                                        console.log(`Auto-refresh attempt ${refreshCount}/${maxRefresh}...`);
                                        
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    }
                                    
                                    checkRedeemCode();
                                </script>
                            @endif
                        </div>
                    </div>

                @elseif($order->status === 'pending')
                    {{-- Payment Processing --}}
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <div class="spinner-border text-warning" style="width: 4rem; height: 4rem;"></div>
                            </div>
                            <h2 class="mb-3">Payment Processing</h2>
                            <p class="text-muted mb-4">Order ID: <strong>{{ $order->id_order }}</strong></p>

                            <div class="alert alert-warning">
                                <div class="mb-3">
                                    <i class="bi bi-hourglass-split fs-3"></i>
                                </div>
                                <p class="mb-2">Your payment is being verified by our system.</p>
                                <p class="mb-3">Please wait a moment or refresh this page.</p>
                                
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                         role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="alert alert-light text-start">
                                <h6 class="mb-2">Payment Information:</h6>
                                <p class="mb-1">
                                    <strong>Amount:</strong> RM {{ number_format($order->base_amount, 2) }}
                                </p>
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                <p class="mb-1 text-muted small">
                                    Display: {{ $order->display_currency }} 
                                    {{ number_format($order->display_amount, $order->display_currency === 'IDR' ? 0 : 2) }}
                                </p>
                                @endif
                                <p class="mb-0">
                                    <strong>Method:</strong> Credit/Debit Card (Stripe)
                                </p>
                            </div>

                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                                <button onclick="location.reload()" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Page
                                </button>
                                <a href="{{ route('orders.history') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-list-ul"></i> Check Order History
                                </a>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    If status doesn't update after 5 minutes, please contact our customer service.
                                </small>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Other Status (failed, cancelled) --}}
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-exclamation-circle text-danger" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="mb-3">Payment Status: {{ ucfirst($order->status) }}</h2>
                            <p class="text-muted mb-4">Order ID: <strong>{{ $order->id_order }}</strong></p>

                            <div class="alert alert-{{ $order->status === 'failed' ? 'danger' : 'secondary' }}">
                                <i class="bi bi-info-circle"></i>
                                @if($order->status === 'failed')
                                    <p class="mb-2">Your payment could not be processed.</p>
                                    <p class="mb-0">Please try again or contact customer service for assistance.</p>
                                @elseif($order->status === 'cancelled')
                                    <p class="mb-0">This order has been cancelled.</p>
                                @else
                                    <p class="mb-0">Please contact customer service for more information.</p>
                                @endif
                            </div>

                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                                @if($order->status === 'failed')
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Try Again
                                </a>
                                @endif
                                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                                <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-list-ul"></i> Order History
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@if($order->status === 'pending')
{{-- Auto-refresh for pending orders --}}
@push('scripts')
<script>
    let pendingRefreshCount = 0;
    const maxPendingRefresh = 20; // 20 attempts × 3 seconds = 1 minute
    
    function checkPendingStatus() {
        if (pendingRefreshCount >= maxPendingRefresh) {
            console.log('Max refresh attempts reached for pending status');
            // Show message to contact support
            return;
        }
        
        pendingRefreshCount++;
        console.log(`Auto-refresh pending check ${pendingRefreshCount}/${maxPendingRefresh}...`);
        
        setTimeout(() => {
            location.reload();
        }, 3000); // Refresh every 3 seconds
    }
    
    checkPendingStatus();
</script>
@endpush
@endif

@if($order->redeem_code)
@push('scripts')
<script>
    // Copy redeem code function
    function copyRedeemCode() {
        const code = '{{ $order->redeem_code }}';
        navigator.clipboard.writeText(code).then(() => {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy code. Please copy manually: ' + code);
        });
    }
</script>
@endpush
@endif

@endsection
