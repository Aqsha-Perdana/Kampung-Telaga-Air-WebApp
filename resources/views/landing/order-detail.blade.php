@extends('landing.layout')

@section('content')
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Order History
            </a>
        </div>

        <div class="row">
            <!-- Order Information -->
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> Order Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Order ID -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Order ID:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="font-monospace">{{ $order->id_order }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" 
                                        onclick="copyOrderId()"
                                        title="Copy Order ID">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-md-8">
                                @if($order->status === 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Paid
                                    </span>
                                @elseif($order->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-hourglass-split"></i> Pending
                                    </span>
                                @elseif($order->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Failed
                                    </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-slash-circle"></i> Cancelled
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Order Date -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Order Date:</strong>
                            </div>
                            <div class="col-md-8">
                                <i class="bi bi-calendar3"></i> 
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>

                        <!-- Payment Date -->
                        @if($order->paid_at)
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Payment Date:</strong>
                            </div>
                            <div class="col-md-8">
                                <i class="bi bi-check-circle text-success"></i> 
                                {{ $order->paid_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                        @endif

                        <!-- Payment Method -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Payment Method:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-light text-dark">
                                    @if($order->payment_method === 'stripe')
                                        <i class="bi bi-credit-card"></i> Credit/Debit Card
                                    @elseif($order->payment_method === 'bank_transfer')
                                        <i class="bi bi-bank"></i> Bank Transfer
                                    @elseif($order->payment_method === 'ewallet')
                                        <i class="bi bi-wallet2"></i> E-Wallet
                                    @else
                                        {{ strtoupper($order->payment_method) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Payment Amount -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Amount Charged:</strong>
                            </div>
                            <div class="col-md-8">
                                <h5 class="text-success mb-1">
                                    RM {{ number_format($order->base_amount, 2) }}
                                    <span class="badge bg-success">MYR</span>
                                </h5>
                                
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                <small class="text-muted d-block">
                                    Display Amount: 
                                    {{ $order->display_currency }} 
                                    {{ $order->display_currency === 'IDR' || $order->display_currency === 'JPY' 
                                        ? number_format($order->display_amount, 0) 
                                        : number_format($order->display_amount, 2) }}
                                    <br>
                                    <span style="font-size: 0.75rem;">
                                        (Reference only - charged in MYR)
                                    </span>
                                </small>
                                @endif
                            </div>
                        </div>

                        <!-- Redeem Code -->
                        @if($order->redeem_code)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <h6 class="mb-3">
                                        <i class="bi bi-ticket-perforated"></i> Redemption Code
                                    </h6>
                                    <div class="bg-white p-3 rounded text-center">
                                        <h2 class="font-monospace text-success mb-2">
                                            {{ $order->redeem_code }}
                                        </h2>
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="copyRedeemCode()">
                                            <i class="bi bi-clipboard"></i> Copy Code
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle"></i> 
                                        Present this code to service providers
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Package Items List -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Package Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($order->items as $item)
                        <div class="border rounded p-3 mb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $item->nama_paket }}</h6>
                                <span class="badge bg-primary">{{ $item->durasi_hari }} Days</span>
                            </div>
                            
                            <div class="row text-muted small mb-2">
                                <div class="col-md-6">
                                    <i class="bi bi-people"></i> 
                                    {{ $item->jumlah_peserta }} participant(s)
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <i class="bi bi-calendar-event"></i> 
                                    {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Unit Price:</small><br>
                                    <strong>RM {{ number_format($item->harga_satuan, 2) }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted">Subtotal:</small><br>
                                    <strong class="text-primary">
                                        RM {{ number_format($item->subtotal, 2) }}
                                    </strong>
                                </div>
                            </div>

                            @if($item->catatan)
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-chat-left-text"></i> 
                                    <strong>Notes:</strong> {{ $item->catatan }}
                                </small>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        <!-- Total -->
                        <div class="border-top pt-3 mt-3">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="mb-0">Total:</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <h4 class="text-success mb-0">
                                        RM {{ number_format($order->base_amount, 2) }}
                                    </h4>
                                    @if($order->display_currency && $order->display_currency !== 'MYR')
                                    <small class="text-muted">
                                        ≈ {{ $order->display_currency }} 
                                        {{ number_format($order->display_amount, 
                                            $order->display_currency === 'IDR' ? 0 : 2) }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Customer Info & Actions -->
            <div class="col-md-4">
                <!-- Customer Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person"></i> Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="d-block text-muted small">Full Name</strong>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="d-block text-muted small">Email</strong>
                            <span>{{ $order->customer_email }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="d-block text-muted small">Phone</strong>
                            <span>{{ $order->customer_phone }}</span>
                        </div>
                        
                        @if($order->customer_address)
                        <div class="mb-0">
                            <strong class="d-block text-muted small">Address</strong>
                            <span>{{ $order->customer_address }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Information (if display currency) -->
                @if($order->display_currency && $order->display_currency !== 'MYR')
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">
                            <i class="bi bi-info-circle"></i> Currency Information
                        </h6>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Display Currency</small>
                            <strong>{{ $order->display_currency }}</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Exchange Rate</small>
                            <strong>
                                1 MYR = {{ number_format($order->display_exchange_rate, 4) }} 
                                {{ $order->display_currency }}
                            </strong>
                        </div>
                        
                        <hr>
                        
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> 
                            All payments are processed in MYR
                        </small>
                    </div>
                </div>
                @endif

                <!-- Actions Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-gear"></i> Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($order->status === 'paid')
                            <a href="{{ route('invoice.download', $order->id_order) }}" 
                               class="btn btn-primary"
                               target="_blank">
                                <i class="bi bi-download"></i> Download Invoice
                            </a>
                            @endif

                            @if($order->status === 'pending')
                            <button type="button" 
                                    class="btn btn-warning"
                                    onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Status
                            </button>
                            
                            <form action="{{ route('orders.cancel', $order->id_order) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle"></i> Cancel Order
                                </button>
                            </form>
                            @endif

                            @if($order->status === 'failed')
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> Try Again
                            </a>
                            @endif

                            <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-list-ul"></i> Order History
                            </a>

                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card border-0 shadow-sm mt-4 bg-light">
                    <div class="card-body">
                        <h6 class="mb-2">
                            <i class="bi bi-question-circle"></i> Need Help?
                        </h6>
                        <p class="small text-muted mb-2">
                            If you have any questions about your order, please contact our customer service.
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-envelope"></i> support@telagaair.com<br>
                            <i class="bi bi-whatsapp"></i> +60 12-345 6789
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Copy Order ID to clipboard
    function copyOrderId() {
        const orderId = '{{ $order->id_order }}';
        navigator.clipboard.writeText(orderId).then(() => {
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy: ' + orderId);
        });
    }

    // Copy Redeem Code to clipboard
    function copyRedeemCode() {
        const code = '{{ $order->redeem_code }}';
        navigator.clipboard.writeText(code).then(() => {
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

@endsection