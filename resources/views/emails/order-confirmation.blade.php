<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Konfirmasi Pemesanan</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $order->customer_name }},</p>
            <p>Terima kasih telah memesan paket wisata kami!</p>
            
            <h3>Detail Pesanan</h3>
            <p><strong>Order ID:</strong> {{ $order->id_order }}</p>
            <p><strong>Total:</strong> {{ $order->currency }} {{ format_ringgit($order->total_amount) }}</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Paket</th>
                        <th>Tanggal</th>
                        <th>Peserta</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->nama_paket }}</td>
                        <td>{{ $item->tanggal_keberangkatan->format('d M Y') }}</td>
                        <td>{{ $item->jumlah_peserta }}</td>
                        <td>{{ format_ringgit($item->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Kami akan menghubungi Anda segera untuk konfirmasi lebih lanjut.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Wisata Telaga Air. All rights reserved.</p>
        </div>
    </div>
</body>
</html>


