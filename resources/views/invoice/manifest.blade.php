<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 0; }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 30px;
            background-color: #ffffff;
        }
        .w-100 { width: 100%; border-collapse: collapse; }
        .v-top { vertical-align: top; }
        
        /* Admin Header */
        .admin-header {
            border-left: 5px solid #0f172a;
            padding: 15px 20px;
            background-color: #f1f5f9;
            margin-bottom: 25px;
        }
        .manifest-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        
        /* Section Styling */
        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f8fafc;
            padding: 8px 12px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }
        .section-content { padding: 12px; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { 
            text-align: left; 
            padding: 10px; 
            background: #0f172a; 
            color: white; 
            font-size: 9px;
        }
        .data-table td { 
            padding: 10px; 
            border-bottom: 1px solid #f1f5f9; 
            vertical-align: middle;
        }

        /* Checklist Box */
        .check-box {
            width: 15px;
            height: 15px;
            border: 1px solid #cbd5e1;
            display: inline-block;
        }

        /* Financial Summary Box (Internal Only) */
        .revenue-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        .rev-item { margin-bottom: 5px; font-size: 10px; }
        .rev-label { color: #166534; }
        .rev-value { font-weight: bold; float: right; }

        .badge-admin {
            padding: 3px 8px;
            background: #e2e8f0;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <table class="w-100 admin-header">
        <tr>
            <td>
                <div class="manifest-title">TRIP MANIFEST & OPERATIONS</div>
                <div style="color: #64748b; margin-top: 5px;">ID Order: #{{ $order->id_order }} | Generated: {{ date('d M Y H:i') }}</div>
            </td>
            <td style="text-align: right;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/logos/primary-logo.png'))) }}" style="height: 40px;">
            </td>
        </tr>
    </table>

    <table class="w-100">
        <tr>
            <td class="v-top" style="width: 48%;">
                <div class="section-box">
                    <div class="section-title">Guest Information (Primary Contact)</div>
                    <div class="section-content">
                        <table class="w-100">
                            <tr><td style="color: #64748b; width: 80px;">Name:</td><td><strong>{{ $order->customer_name }}</strong></td></tr>
                            <tr><td style="color: #64748b;">Phone:</td><td>{{ $order->customer_phone }}</td></tr>
                            <tr><td style="color: #64748b;">Redeem:</td><td style="color: #4f46e5; font-weight: bold;">{{ $order->redeem_code }}</td></tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="v-top" style="width: 48%;">
                <div class="section-box">
                    <div class="section-title">Operational Notes & Safety</div>
                    <div class="section-content">
                        <table class="w-100">
                            <tr>
                                <td style="color: #64748b; width: 80px;">Notes:</td>
                                <td style="font-weight: bold; color: #ef4444;">
                                    {{ $items->first()->catatan ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #64748b;">Emergency:</td>
                                <td style="font-size: 9px;">
                                    HQ: +60 12-345 6789<br>
                                    Local Clinic: +60 82-xxx xxx
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-box">
        <div class="section-title">Trip Details & Attendance Checklist</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">Check</th>
                    <th style="width: 45%;">Package Name</th>
                    <th style="width: 25%;">Departure Date</th>
                    <th style="width: 10%; text-align: center;">Pax</th>
                    <th style="width: 15%;">Internal Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="text-align: center;"><div class="check-box"></div></td>
                    <td>
                        <strong>{{ $item->nama_paket }}</strong><br>
                        <small>{{ $item->durasi_hari }} Days</small>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d F Y') }}</td>
                    <td style="text-align: center;">{{ $item->jumlah_peserta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="w-100">
        <tr>
            <td style="width: 5%;"></td>
            <td class="v-top" style="width: 40%;">
                <div class="section-title" style="border: 1px solid #e2e8f0; border-bottom: none; border-radius: 6px 6px 0 0;">Internal Financial Breakdown</div>
                <div class="revenue-box" style="border-radius: 0 0 6px 6px;">
                    <div class="rev-item">
                        <span class="rev-label">Boat Vendor:</span>
                        <span class="rev-value">RM {{ number_format($totals['boat'] ?? 0, 2) }}</span>
                    </div>
                    <div class="rev-item">
                        <span class="rev-label">Homestay:</span>
                        <span class="rev-value">RM {{ number_format($totals['homestay'] ?? 0, 2) }}</span>
                    </div>
                    <div class="rev-item">
                        <span class="rev-label">Culinary:</span>
                        <span class="rev-value">RM {{ number_format($totals['culinary'] ?? 0, 2) }}</span>
                    </div>
                    <div class="rev-item">
                        <span class="rev-label">Kiosk:</span>
                        <span class="rev-value">RM {{ number_format($totals['kiosk'] ?? 0, 2) }}</span>
                    </div>
                    <div class="rev-item" style="background-color: #ecfdf5; margin: 5px -15px; padding: 5px 15px; border-left: 3px solid #10b981;">
                        <span class="rev-label" style="color: #047857; font-weight: bold;">Company Revenue (Profit):</span>
                        <span class="rev-value" style="color: #047857;">RM {{ number_format($totals['company'], 2) }}</span>
                    </div>
                    <div class="rev-item" style="border-top: 1px solid #bbf7d0; margin-top: 8px; padding-top: 5px;">
                        <span class="rev-label" style="font-weight: bold;">TOTAL REVENUE:</span>
                        <span class="rev-value" style="color: #166534;">RM {{ number_format($order->base_amount, 2) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 30px; border: 1px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 6px;">
        <span style="color: #64748b; font-size: 10px;">OPERATIONAL USE ONLY - DO NOT SHARE WITH CUSTOMERS</span>
    </div>

</body>
</html>