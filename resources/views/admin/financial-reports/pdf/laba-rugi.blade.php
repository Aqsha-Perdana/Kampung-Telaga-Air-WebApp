<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #000;
        }
        
        .report-period {
            font-size: 11pt;
            color: #666;
            margin-bottom: 5px;
        }
        
        .report-date {
            font-size: 9pt;
            color: #999;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px 10px;
            border-top: 2px solid #333;
            border-bottom: 1px solid #333;
            font-size: 12pt;
        }
        
        .account-line {
            padding: 6px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .account-line.indent-1 {
            padding-left: 30px;
        }
        
        .account-line.indent-2 {
            padding-left: 50px;
            font-size: 10pt;
            color: #666;
        }
        
        .account-name {
            display: inline-block;
            width: 70%;
        }
        
        .account-amount {
            display: inline-block;
            width: 28%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .subtotal-line {
            padding: 8px 10px;
            border-top: 1px solid #999;
            border-bottom: 1px solid #999;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .total-line {
            padding: 10px 10px;
            border-top: 3px double #333;
            border-bottom: 3px double #333;
            font-weight: bold;
            font-size: 12pt;
            background-color: #e8f4f8;
        }
        
        .negative {
            color: #c00;
        }
        
        .positive {
            color: #060;
        }
        
        .metrics-box {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .metrics-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .metric-item {
            margin: 8px 0;
            font-size: 10pt;
        }
        
        .metric-label {
            display: inline-block;
            width: 60%;
        }
        
        .metric-value {
            display: inline-block;
            width: 38%;
            text-align: right;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 30px;
            left: 50px;
            right: 50px;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KAMPUNG TELAGA AIR</div>
        <div style="font-size: 10pt; color: #666; margin: 5px 0;">Tourism & Homestay Services</div>
        <div class="report-title">LAPORAN LABA RUGI</div>
        <div class="report-title" style="font-size: 14pt; font-weight: normal;">(INCOME STATEMENT)</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} 
            s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </div>
        <div class="report-date">
            Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB
        </div>
    </div>

    <table>
        <!-- PENDAPATAN (REVENUE) -->
        <tr>
            <td colspan="2" class="section-header">PENDAPATAN (REVENUE)</td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Pendapatan Penjualan Paket Wisata</span>
                <span class="account-amount">RM {{ number_format($data['revenue']['gross_revenue'], 2) }}</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name">Jumlah Transaksi: {{ $data['orders_count'] }} order</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Dikurangi: Harga Pokok Penjualan (Cost of Sales)</span>
                <span class="account-amount negative">(RM {{ number_format($data['revenue']['cost_of_sales'], 2) }})</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name"><i>Pembayaran kepada Pemilik Sumberdaya</i></span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="subtotal-line">
                <span class="account-name">LABA KOTOR (GROSS PROFIT)</span>
                <span class="account-amount positive">RM {{ number_format($data['revenue']['gross_profit'], 2) }}</span>
            </td>
        </tr>
        
        <!-- BEBAN OPERASIONAL -->
        <tr><td colspan="2" style="height: 20px;"></td></tr>
        
        <tr>
            <td colspan="2" class="section-header">BEBAN OPERASIONAL (OPERATING EXPENSES)</td>
        </tr>
        
        @php
            $totalExpenses = 0;
        @endphp
        
        @foreach($data['expenses']['by_category'] as $category => $categoryData)
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">{{ $category }}</span>
                <span class="account-amount">RM {{ number_format($categoryData['total'], 2) }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="account-line indent-2">
                <span class="account-name"><i>{{ $categoryData['count'] }} transaksi</i></span>
            </td>
        </tr>
        @php
            $totalExpenses += $categoryData['total'];
        @endphp
        @endforeach
        
        @if($totalExpenses == 0)
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name"><i>Tidak ada beban operasional pada periode ini</i></span>
                <span class="account-amount">RM 0.00</span>
            </td>
        </tr>
        @endif
        
        <tr>
            <td colspan="2" class="subtotal-line">
                <span class="account-name">TOTAL BEBAN OPERASIONAL</span>
                <span class="account-amount negative">(RM {{ number_format($data['expenses']['total'], 2) }})</span>
            </td>
        </tr>
        
        <!-- LABA BERSIH -->
        <tr><td colspan="2" style="height: 20px;"></td></tr>
        
        <tr>
            <td colspan="2" class="total-line">
                <span class="account-name">LABA BERSIH (NET INCOME)</span>
                <span class="account-amount {{ $data['net_income']['amount'] >= 0 ? 'positive' : 'negative' }}">
                    RM {{ number_format($data['net_income']['amount'], 2) }}
                </span>
            </td>
        </tr>
    </table>
    
    <!-- ANALISIS PROFITABILITAS -->
    <div class="metrics-box">
        <div class="metrics-title">ANALISIS PROFITABILITAS (PROFITABILITY ANALYSIS)</div>
        
        <div class="metric-item">
            <span class="metric-label">Margin Laba Kotor (Gross Profit Margin):</span>
            <span class="metric-value">{{ number_format($data['revenue']['gross_profit_margin'], 2) }}%</span>
        </div>
        
        <div class="metric-item">
            <span class="metric-label">Margin Laba Bersih (Net Profit Margin):</span>
            <span class="metric-value {{ $data['net_income']['margin'] >= 0 ? 'positive' : 'negative' }}">
                {{ number_format($data['net_income']['margin'], 2) }}%
            </span>
        </div>
        
        <div class="metric-item" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ccc;">
            <span class="metric-label">Total Pendapatan (Total Revenue):</span>
            <span class="metric-value">RM {{ number_format($data['revenue']['gross_revenue'], 2) }}</span>
        </div>
        
        <div class="metric-item">
            <span class="metric-label">Total Harga Pokok (Total COGS):</span>
            <span class="metric-value">RM {{ number_format($data['revenue']['cost_of_sales'], 2) }}</span>
        </div>
        
        <div class="metric-item">
            <span class="metric-label">Total Beban Operasional (Total OpEx):</span>
            <span class="metric-value">RM {{ number_format($data['expenses']['total'], 2) }}</span>
        </div>
        
        <div class="metric-item" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ccc;">
            <span class="metric-label">Jumlah Transaksi (Total Transactions):</span>
            <span class="metric-value">{{ $data['orders_count'] }} order</span>
        </div>
        
        <div class="metric-item">
            <span class="metric-label">Rata-rata Pendapatan per Order:</span>
            <span class="metric-value">
                RM {{ $data['orders_count'] > 0 ? number_format($data['revenue']['gross_revenue'] / $data['orders_count'], 2) : '0.00' }}
            </span>
        </div>
        
        <div class="metric-item">
            <span class="metric-label">Rata-rata Laba per Order:</span>
            <span class="metric-value">
                RM {{ $data['orders_count'] > 0 ? number_format($data['net_income']['amount'] / $data['orders_count'], 2) : '0.00' }}
            </span>
        </div>
    </div>
    
    <!-- CATATAN KAKI -->
    <div style="margin-top: 40px; padding: 15px; background-color: #fffef0; border-left: 4px solid #f0ad4e;">
        <div style="font-weight: bold; margin-bottom: 8px; font-size: 10pt;">CATATAN:</div>
        <div style="font-size: 9pt; line-height: 1.6;">
            1. Laporan ini disusun berdasarkan transaksi dengan status: Paid, Confirmed, dan Completed.<br>
            2. Harga Pokok Penjualan (COGS) merupakan total pembayaran kepada pemilik sumberdaya (boat, homestay, culinary, kiosk).<br>
            3. Laba Kotor = Pendapatan Penjualan - Harga Pokok Penjualan<br>
            4. Laba Bersih = Laba Kotor - Beban Operasional<br>
            5. Semua nilai dalam mata uang Ringgit Malaysia (MYR).
        </div>
    </div>
    
    <!-- TANDA TANGAN -->
    <div style="margin-top: 50px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; border: none;">
                    <div style="margin-bottom: 80px;">Dibuat Oleh,</div>
                    <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 50px;">
                        Admin
                    </div>
                </td>
                <td style="width: 50%; text-align: center; border: none;">
                    <div style="margin-bottom: 80px;">Disetujui Oleh,</div>
                    <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 50px;">
                        Manager
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <div>Kampung Telaga Air - Financial Report | Halaman <span class="page-number"></span></div>
        <div style="font-size: 8pt; margin-top: 3px;">Dokumen ini dicetak secara otomatis dan sah tanpa tanda tangan basah</div>
    </div>
</body>
</html>