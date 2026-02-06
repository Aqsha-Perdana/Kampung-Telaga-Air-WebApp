@extends('landing.layout')

@section('title', 'History Pemesanan')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="bi bi-clock-history"></i> History Pemesanan</h2>
            
            <!-- Filter Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('orders.history') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                            Semua
                        </a>
                        <a href="{{ route('orders.history', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                            Menunggu Pembayaran
                        </a>
                        <a href="{{ route('orders.history', ['status' => 'paid']) }}" class="btn btn-sm {{ request('status') == 'paid' ? 'btn-info' : 'btn-outline-info' }}">
                            Sudah Dibayar
                        </a>
                        <a href="{{ route('orders.history', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') == 'confirmed' ? 'btn-success' : 'btn-outline-success' }}">
                            Dikonfirmasi
                        </a>
                        <a href="{{ route('orders.history', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') == 'completed' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Selesai
                        </a>
                        <a href="{{ route('orders.history', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') == 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                            Dibatalkan
                        </a>
                    </div>
                </div>
            </div>

            @if($orders->count() > 0)
                @foreach($orders as $order)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Order #{{ $order->id_order }}</strong>
                            <br>
                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <div>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                            @elseif($order->status == 'paid')
                                <span class="badge bg-info">Sudah Dibayar</span>
                            @elseif($order->status == 'confirmed')
                                <span class="badge bg-success">Dikonfirmasi</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-primary">Selesai</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">Dibatalkan</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                @if($order->items && $order->items->count() > 0)
                                    @foreach($order->items as $item)
                                        <div class="mb-3">
                                            <h5 class="card-title mb-1">{{ $item->nama_paket }}</h5>
                                            <div class="text-muted small">
                                                <i class="bi bi-calendar-event"></i> 
                                                {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-clock"></i> {{ $item->durasi_hari }} Hari
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-people-fill"></i> {{ $item->jumlah_peserta }} Peserta
                                            </div>
                                            @if($item->catatan)
                                                <div class="mt-2">
                                                    <small><strong>Catatan:</strong> {{ $item->catatan }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                <div class="mt-3">
                                    <p class="mb-1">
                                        <i class="bi bi-person-circle"></i> 
                                        <strong>Pemesan:</strong> {{ $order->customer_name }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-telephone"></i> 
                                        {{ $order->customer_phone }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h4 class="text-primary mb-3">
                                    {{ $order->currency }} {{  format_ringgit($order->total_amount) }}
                                </h4>
                                
                                <div class="d-grid gap-2">
                                    <a href="{{ route('orders.show', $order->id_order) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </a>
                                    
                                    @if($order->status == 'pending')
                                        <a href="{{ route('orders.payment', $order->id_order) }}" class="btn btn-success btn-sm">
                                            <i class="bi bi-credit-card"></i> Bayar Sekarang
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="cancelOrder('{{ $order->id_order }}')">
                                            <i class="bi bi-x-circle"></i> Batalkan
                                        </button>
                                    @endif

                                    @if(in_array($order->status, ['completed', 'paid', 'confirmed']))
                                        <a href="{{ route('orders.invoice', $order->id_order) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-file-earmark-text"></i> Unduh Invoice
                                        </a>
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
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Belum Ada Pesanan</h4>
                        <p class="text-muted">Anda belum memiliki riwayat pemesanan</p>
                        <a href="{{ route('landing.paket-wisata') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-compass"></i> Jelajahi Paket Wisata
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function cancelOrder(orderId) {
    if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
        fetch(`/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal membatalkan pesanan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}
</script>
@endpush
@endsection