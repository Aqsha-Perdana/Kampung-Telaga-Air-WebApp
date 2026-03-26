<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->id_order }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 24px;
        }

        .header {
            border-bottom: 2px solid #0f172a;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }

        .header-table,
        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }

        .muted {
            color: #6b7280;
        }

        .section-title {
            margin: 22px 0 10px;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            width: 140px;
            color: #6b7280;
        }

        .items-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        .items-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .totals-wrap {
            margin-top: 12px;
            width: 100%;
        }

        .totals-table {
            margin-left: auto;
            width: 280px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .totals-table .total-row td {
            border-top: 2px solid #111827;
            font-size: 14px;
            font-weight: bold;
            padding-top: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    @php
        $statusLabel = strtoupper(str_replace('_', ' ', (string) $order->status));
        $paymentMethodLabel = $order->payment_method === 'stripe'
            ? 'Credit/Debit Card (Stripe)'
            : strtoupper(str_replace('_', ' ', (string) $order->payment_method));
    @endphp
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">INVOICE</div>
                    <div class="muted">Telaga Air Tour Package Booking</div>
                </td>
                <td class="text-right">
                    <div><strong>{{ $order->id_order }}</strong></div>
                    <div class="muted">
                        {{ optional($order->created_at)->format('d M Y H:i') }}
                    </div>
                    <div style="margin-top: 6px;">
                        <span class="status-badge">{{ $statusLabel }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Customer</div>
    <table class="info-table">
        <tr>
            <td class="label">Name</td>
            <td>{{ $order->customer_name }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $order->customer_email }}</td>
        </tr>
        <tr>
            <td class="label">Phone</td>
            <td>{{ $order->customer_phone }}</td>
        </tr>
        @if($order->customer_address)
            <tr>
                <td class="label">Address</td>
                <td>{{ $order->customer_address }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">Payment</div>
    <table class="info-table">
        <tr>
            <td class="label">Method</td>
            <td>{{ $paymentMethodLabel }}</td>
        </tr>
        <tr>
            <td class="label">Paid At</td>
            <td>{{ optional($order->paid_at)->format('d M Y H:i') ?: '-' }}</td>
        </tr>
        @if($order->redeem_code)
            <tr>
                <td class="label">Redeem Code</td>
                <td><strong>{{ $order->redeem_code }}</strong></td>
            </tr>
        @endif
        @if($order->display_currency && $order->display_currency !== 'MYR' && $order->display_amount)
            <tr>
                <td class="label">Display Amount</td>
                <td>
                    {{ $order->display_currency }}
                    {{ in_array($order->display_currency, ['IDR', 'JPY'], true)
                        ? number_format($order->display_amount, 0)
                        : number_format($order->display_amount, 2) }}
                    <span class="muted">(reference only)</span>
                </td>
            </tr>
        @endif
    </table>

    <div class="section-title">Booked Packages</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Package</th>
                <th>Departure</th>
                <th>Duration</th>
                <th>Participants</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->nama_paket }}</strong>
                        @if($item->catatan)
                            <div class="muted" style="margin-top: 4px;">Notes: {{ $item->catatan }}</div>
                        @endif
                    </td>
                    <td>{{ optional($item->tanggal_keberangkatan)->format('d M Y') }}</td>
                    <td>{{ $item->durasi_hari }} day(s)</td>
                    <td>{{ $item->jumlah_peserta }}</td>
                    <td class="text-right">RM {{ number_format((float) $item->harga_satuan, 2) }}</td>
                    <td class="text-right">RM {{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="muted">Total Charged</td>
                <td class="text-right">RM {{ number_format((float) $order->base_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">RM {{ number_format((float) $order->base_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This invoice is generated automatically from the Telaga Air booking system.
        All payment charges are processed in MYR.
    </div>
</body>
</html>

