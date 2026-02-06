<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Standar PDF Reset */
        @page { size: A4; margin: 0; }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            margin: 0;
            padding: 40px;
            line-height: 1.4;
        }

        .w-100 { width: 100%; border-collapse: collapse; }
        .v-top { vertical-align: top; }

        /* Header Area */
        .header-bg {
            background-color: #f8fafc;
            padding: 25px;
            border-bottom: 3px solid #4f46e5;
            margin-bottom: 30px;
        }

        /* Ganti .logo-box lama dengan ini */
        .logo-image {
            width: 60px; /* Atur lebar logo sesuai keinginan */
            height: auto;
            display: block;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 26px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }

        /* Section Cards */
        .card-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .info-data { color: #64748b; width: 90px; }
        .info-value { color: #1e293b; font-weight: bold; }

        /* Currency Conversion Box */
        .currency-box {
            margin: 20px 0;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 15px;
        }

        .amount-text {
            font-size: 18px;
            font-weight: bold;
            color: #92400e;
        }

        /* Table Styling */
        .main-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .main-table th {
            background: #4f46e5;
            color: #ffffff;
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .main-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; }

        /* Summary Area */
        .summary-container { width: 250px; margin-left: auto; margin-top: 20px; }
        .summary-row td { padding: 5px 0; }
        .grand-total-row {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            border-top: 2px solid #4f46e5;
        }

        /* Status Badge */
        .paid-badge {
            background: #22c55e;
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }

        /* Redemption Code Area */
        .redemption-container {
            background: #4f46e5;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 30px;
        }

        .code-font {
            font-family: Courier, monospace;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <table class="w-100 header-bg">
    <tr>
        <td class="v-top">
            <table style="border-collapse: collapse;">
                <tr>
                    @php
                        $path = public_path('assets/images/logos/primary-logo.png');
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    @endphp
                    <td>
                        <img src="{{ $base64 }}" class="logo-image">
                    </td>
                    <td style="padding-left: 15px;">
                        <div style="font-size: 18px; font-weight: bold; color: #1e293b;">Kampung Telaga Air</div>
                        <div style="color: #64748b; font-size: 10px;">Kuching, Malaysia</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="v-top" style="text-align: right;">
            <div class="invoice-title">INVOICE</div>
            <div style="font-size: 14px; color: #64748b; margin: 5px 0;">#{{ $order->id_order }}</div>
            <div class="paid-badge">PAID IN FULL</div>
        </td>
    </tr>
</table>

    <table class="w-100" style="margin-bottom: 20px;">
        <tr>
            <td class="v-top" style="width: 48%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <div class="card-label">Billing Information</div>
                <table class="w-100">
                    <tr><td class="info-data">Customer:</td><td class="info-value">{{ $order->customer_name }}</td></tr>
                    <tr><td class="info-data">Email:</td><td class="info-value">{{ $order->customer_email }}</td></tr>
                    <tr><td class="info-data">Phone:</td><td class="info-value">{{ $order->customer_phone }}</td></tr>
                    <tr><td class="info-data">Address:</td><td class="info-value">{{ $order->customer_address }}</td></tr>
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td class="v-top" style="width: 48%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <div class="card-label">Order Details</div>
                <table class="w-100">
                    <tr><td class="info-data">Order Date:</td><td class="info-value">{{ \Carbon\Carbon::parse($order->created_at)->format('d F Y') }}</td></tr>
                    <tr><td class="info-data">Payment At:</td><td class="info-value">{{ \Carbon\Carbon::parse($order->paid_at)->format('d M Y, H:i') }}</td></tr>
                    <tr><td class="info-data">Method:</td><td class="info-value">Credit Card</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if($order->display_currency && $order->display_currency !== 'MYR')
    <div class="currency-box">
        <table class="w-100">
            <tr>
                <td style="text-align: center; width: 45%;">
                    <div style="font-size: 9px; color: #92400e;">YOU VIEWED AS</div>
                    <div class="amount-text">Rp {{ number_format($order->display_amount, 0) }}</div>
                    <div style="font-size: 9px; font-weight: bold;">IDR</div>
                </td>
                <td style="text-align: center; width: 10%; font-size: 20px; color: #f59e0b;">-</td>
                <td style="text-align: center; width: 45%;">
                    <div style="font-size: 9px; color: #92400e;">YOU WERE CHARGED</div>
                    <div class="amount-text">RM {{ number_format($order->base_amount, 2) }}</div>
                    <div style="font-size: 9px; font-weight: bold;">MYR</div>
                </td>
            </tr>
        </table>
        <div style="text-align: center; font-size: 8px; margin-top: 10px; color: #b45309; border-top: 1px dashed #fde68a; padding-top: 8px;">
            Exchange Rate: 1 MYR = {{ number_format($order->display_exchange_rate, 2) }} IDR | All payments processed in MYR
        </div>
    </div>
    @endif

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 50%;">Tour Package</th>
                <th style="width: 20%;">Departure</th>
                <th style="width: 10%; text-align: center;">Pax</th>
                <th style="width: 20%; text-align: right;">Total (MYR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $item->nama_paket }}</div>
                    <div style="font-size: 9px; color: #64748b;">{{ $item->durasi_hari }} Days Tour Experience</div>
                </td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}</td>
                <td style="text-align: center;">{{ $item->jumlah_peserta }}</td>
                <td style="text-align: right; font-weight: bold;">RM {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-container">
        <tr class="summary-row">
            <td style="color: #64748b;">Subtotal</td>
            <td style="text-align: right; font-weight: bold;">RM {{ number_format($order->base_amount, 2) }}</td>
        </tr>
        <tr class="summary-row">
            <td style="color: #64748b;">Total Participants</td>
            <td style="text-align: right; font-weight: bold;">{{ $order->items->sum('jumlah_peserta') }} Person</td>
        </tr>
        <tr class="grand-total-row">
            <td style="padding-top: 10px;">TOTAL</td>
            <td style="text-align: right; padding-top: 10px;">RM {{ number_format($order->base_amount, 2) }}</td>
        </tr>
    </table>

    @if($order->redeem_code)
    <div class="redemption-container">
        <div style="font-size: 10px; text-transform: uppercase; opacity: 0.9;">Redemption Code</div>
        <div class="code-font">{{ $order->redeem_code }}</div>
        <div style="font-size: 9px; opacity: 0.9;">Please present this code to our staff at the venue.</div>
    </div>
    @endif

    <div style="margin-top: 50px; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
        <div style="color: #4f46e5; font-size: 11px; font-weight: bold; margin-bottom: 5px;">Thank You for Booking with Kampung Telaga Air!</div>
        Support: +60 12-345 6789 | Email: support@telagaair.com<br>
        <em>This is a system-generated document, no signature is required.</em>
    </div>

</body>
</html>