<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas</title>
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
        
        .balance-line {
            padding: 10px 10px;
            border: 2px solid #333;
            font-weight: bold;
            font-size: 13pt;
            background-color: #d4edda;
        }
        
        .negative {
            color: #c00;
        }
        
        .positive {
            color: #060;
        }
        
        .inflow {
            color: #006400;
        }
        
        .outflow {
            color: #8b0000;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KAMPUNG TELAGA AIR</div>
        <div style="font-size: 10pt; color: #666; margin: 5px 0;">Tourism & Homestay Services</div>
        <div class="report-title">LAPORAN ARUS KAS</div>
        <div class="report-title" style="font-size: 14pt; font-weight: normal;">(CASH FLOW STATEMENT)</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} 
            s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </div>
        <div class="report-date">
            Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB
        </div>
    </div>

    <table>
        <!-- KAS MASUK (CASH INFLOWS) -->
        <tr>
            <td colspan="2" class="section-header" style="background-color: #d4edda;">
                KAS MASUK (CASH INFLOWS)
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Penerimaan dari Pelanggan</span>
                <span class="account-amount inflow">RM {{ number_format($data['inflows']['customer_payments'], 2) }}</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name"><i>Rincian berdasarkan metode pembayaran:</i></span>
            </td>
        </tr>
        
        @foreach($data['inflows']['by_payment_method'] as $method => $methodData)
        <tr>
            <td colspan="2" class="account-line indent-2">
                <span class="account-name">{{ $method === 'stripe' ? 'Credit/Debit Card (Stripe)' : ($method ?? 'Metode Lainnya') }} ({{ $methodData['count'] }}x)</span>
                <span class="account-amount">RM {{ number_format($methodData['total'], 2) }}</span>
            </td>
        </tr>
        @endforeach
        
        <tr>
            <td colspan="2" class="subtotal-line" style="background-color: #d4edda;">
                <span class="account-name">TOTAL KAS MASUK</span>
                <span class="account-amount inflow">RM {{ number_format($data['inflows']['total'], 2) }}</span>
            </td>
        </tr>
        
        <!-- KAS KELUAR (CASH OUTFLOWS) -->
        <tr><td colspan="2" style="height: 20px;"></td></tr>
        
        <tr>
            <td colspan="2" class="section-header" style="background-color: #f8d7da;">
                KAS KELUAR (CASH OUTFLOWS)
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Pembayaran kepada Pemilik Sumberdaya</span>
                <span class="account-amount outflow">(RM {{ number_format($data['outflows']['owner_payments'], 2) }})</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name"><i>Rincian pembayaran kepada owner:</i></span>
            </td>
        </tr>
        
        @foreach($data['outflows']['owner_breakdown'] as $type => $amount)
        <tr>
            <td colspan="2" class="account-line indent-2">
                <span class="account-name">{{ ucfirst($type) }}</span>
                <span class="account-amount">(RM {{ number_format($amount, 2) }})</span>
            </td>
        </tr>
        @endforeach
        
        <tr><td colspan="2" style="height: 10px;"></td></tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Pembayaran Beban Operasional</span>
                <span class="account-amount outflow">(RM {{ number_format($data['outflows']['operating_expenses'], 2) }})</span>
            </td>
        </tr>
        
        @if(isset($data['outflows']['expenses_by_method']) && count($data['outflows']['expenses_by_method']) > 0)
        <tr>
            <td colspan="2" class="account-line indent-1">
                <span class="account-name"><i>Rincian berdasarkan metode pembayaran:</i></span>
            </td>
        </tr>
        
        @foreach($data['outflows']['expenses_by_method'] as $method => $amount)
        <tr>
            <td colspan="2" class="account-line indent-2">
                <span class="account-name">{{ $method }}</span>
                <span class="account-amount">(RM {{ number_format($amount, 2) }})</span>
            </td>
        </tr>
        @endforeach
        @endif
        
        <tr>
            <td colspan="2" class="subtotal-line" style="background-color: #f8d7da;">
                <span class="account-name">TOTAL KAS KELUAR</span>
                <span class="account-amount outflow">(RM {{ number_format($data['outflows']['total'], 2) }})</span>
            </td>
        </tr>
        
        <!-- NET CASH FLOW -->
        <tr><td colspan="2" style="height: 20px;"></td></tr>
        
        <tr>
            <td colspan="2" class="total-line">
                <span class="account-name">ARUS KAS BERSIH (NET CASH FLOW)</span>
                <span class="account-amount {{ $data['net_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                    RM {{ number_format($data['net_cash_flow'], 2) }}
                </span>
            </td>
        </tr>
        
        <!-- SALDO KAS -->
        <tr><td colspan="2" style="height: 20px;"></td></tr>
        
        <tr>
            <td colspan="2" class="section-header">POSISI KAS (CASH POSITION)</td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Saldo Kas Awal Periode (Opening Balance)</span>
                <span class="account-amount">RM {{ number_format($data['opening_balance'], 2) }}</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="account-line">
                <span class="account-name">Arus Kas Bersih Periode Ini</span>
                <span class="account-amount {{ $data['net_cash_flow'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $data['net_cash_flow'] >= 0 ? '' : '(' }}RM {{ number_format(abs($data['net_cash_flow']), 2) }}{{ $data['net_cash_flow'] >= 0 ? '' : ')' }}
                </span>
            </td>
        </tr>
        
        <tr>
            <td colspan="2" class="balance-line">
                <span class="account-name">SALDO KAS AKHIR PERIODE (CLOSING BALANCE)</span>
                <span class="account-amount {{ $data['closing_balance'] >= 0 ? 'positive' : 'negative' }}">
                    RM {{ number_format($data['closing_balance'], 2) }}
                </span>
            </td>
        </tr>
    </table>
    
    <!-- RINGKASAN ARUS KAS -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f8f8; border: 1px solid #ddd; border-radius: 5px;">
        <div style="font-weight: bold; font-size: 12pt; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
            RINGKASAN ARUS KAS (CASH FLOW SUMMARY)
        </div>
        
        <div style="margin: 8px 0; font-size: 10pt;">
            <span style="display: inline-block; width: 60%;">Jumlah Transaksi Penerimaan:</span>
            <span style="display: inline-block; width: 38%; text-align: right; font-weight: bold;">
                {{ $data['transactions_count'] }} transaksi
            </span>
        </div>
        
        <div style="margin: 8px 0; font-size: 10pt;">
            <span style="display: inline-block; width: 60%;">Total Kas Masuk:</span>
            <span style="display: inline-block; width: 38%; text-align: right; font-weight: bold; color: #060;">
                RM {{ number_format($data['inflows']['total'], 2) }}
            </span>
        </div>
        
        <div style="margin: 8px 0; font-size: 10pt;">
            <span style="display: inline-block; width: 60%;">Total Kas Keluar:</span>
            <span style="display: inline-block; width: 38%; text-align: right; font-weight: bold; color: #c00;">
                RM {{ number_format($data['outflows']['total'], 2) }}
            </span>
        </div>
        
        <div style="margin: 15px 0 8px 0; padding-top: 10px; border-top: 1px solid #ccc; font-size: 10pt;">
            <span style="display: inline-block; width: 60%;">Persentase Kas Keluar dari Kas Masuk:</span>
            <span style="display: inline-block; width: 38%; text-align: right; font-weight: bold;">
                {{ $data['inflows']['total'] > 0 ? number_format(($data['outflows']['total'] / $data['inflows']['total']) * 100, 2) : '0.00' }}%
            </span>
        </div>
        
        <div style="margin: 8px 0; font-size: 10pt;">
            <span style="display: inline-block; width: 60%;">Status Arus Kas:</span>
            <span style="display: inline-block; width: 38%; text-align: right; font-weight: bold; {{ $data['net_cash_flow'] >= 0 ? 'color: #060' : 'color: #c00' }};">
                {{ $data['net_cash_flow'] >= 0 ? 'POSITIF (Surplus)' : 'NEGATIF (Defisit)' }}
            </span>
        </div>
    </div>
    
    <!-- CATATAN KAKI -->
    <div style="margin-top: 30px; padding: 15px; background-color: #fffef0; border-left: 4px solid #f0ad4e;">
        <div style="font-weight: bold; margin-bottom: 8px; font-size: 10pt;">CATATAN:</div>
        <div style="font-size: 9pt; line-height: 1.6;">
            1. Laporan ini menunjukkan aliran kas masuk dan keluar selama periode pelaporan.<br>
            2. Kas Masuk bersumber dari pembayaran pelanggan atas paket wisata yang telah dibayar.<br>
            3. Kas Keluar terdiri dari pembayaran kepada pemilik sumberdaya dan beban operasional.<br>
            4. Saldo Kas Awal menunjukkan posisi kas pada awal periode pelaporan.<br>
            5. Saldo Kas Akhir menunjukkan posisi kas pada akhir periode pelaporan.<br>
            6. Semua nilai dalam mata uang Ringgit Malaysia (MYR).
        </div>
    </div>
    
    <!-- TANDA TANGAN -->
    <div style="margin-top: 50px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; border: none;">
                    <div style="margin-bottom: 80px;">Dibuat Oleh,</div>
                    <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 50px;">
                        Admin/Kasir
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
        <div>Kampung Telaga Air - Cash Flow Report | Halaman <span class="page-number"></span></div>
        <div style="font-size: 8pt; margin-top: 3px;">Dokumen ini dicetak secara otomatis dan sah tanpa tanda tangan basah</div>
    </div>
</body>
</html> 
