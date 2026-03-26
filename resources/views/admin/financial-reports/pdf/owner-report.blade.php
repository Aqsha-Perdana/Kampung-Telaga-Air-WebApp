<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Owner Report - {{ $report['name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .owner-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .owner-info table {
            width: 100%;
        }
        .owner-info td {
            padding: 5px;
        }
        .summary-boxes {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-box h3 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .summary-box p {
            margin: 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        table.transactions {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.transactions th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        table.transactions td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table.transactions tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .total-row {
            background: #e8f4f8 !important;
            font-weight: bold;
            font-size: 13px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ strtoupper($type) }} OWNER REPORT</h1>
        <p><strong>Kampung Telaga Air</strong></p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <!-- Owner Information -->
    <div class="owner-info">
        <h3 style="margin-top: 0;">Owner Information</h3>
        <table>
            <tr>
                <td width="25%"><strong>ID:</strong></td>
                <td width="25%">{{ $report['id'] }}</td>
                <td width="25%"><strong>Type:</strong></td>
                <td width="25%">{{ $report['type'] }}</td>
            </tr>
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{ $report['name'] }}</td>
                <td><strong>Price per {{ $report['unit_name'] }}:</strong></td>
                <td>RM {{ number_format($report['price_per_unit'], 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary-boxes">
        <div class="summary-box">
            <h3>{{ $report['usage_count'] }}</h3>
            <p>Total Usage</p>
        </div>
        @if(isset($report['total_units']))
        <div class="summary-box">
            <h3>{{ $report['total_units'] }}</h3>
            <p>Total {{ ucfirst($report['unit_name']) }}s</p>
        </div>
        @endif
        <div class="summary-box">
            <h3>{{ $report['total_participants'] }}</h3>
            <p>Total Participants</p>
        </div>
        <div class="summary-box">
            <h3>RM {{ number_format($report['total_revenue'], 2) }}</h3>
            <p>Owner Revenue</p>
        </div>
    </div>

    <!-- Transaction Details -->
    <h3>Transaction Details</h3>
    <table class="transactions">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Package</th>
                <th>Departure</th>
                <th class="text-center">Pax</th>
                @if(isset($report['total_units']))
                <th class="text-center">{{ ucfirst($report['unit_name']) }}s</th>
                @endif
                <th class="text-right">Owner Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['transactions'] as $transaction)
            <tr>
                <td>{{ $transaction->id_order }}</td>
                <td>{{ $transaction->customer_name }}</td>
                <td>
                    {{ $transaction->nama_paket }}
                    @if(isset($transaction->variant_name))
                        <br><small style="color: #7f8c8d; font-style: italic;">{{ $transaction->variant_name }}</small>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($transaction->tanggal_keberangkatan)->format('d M Y') }}</td>
                <td class="text-center">{{ $transaction->jumlah_peserta }}</td>
                @if(isset($report['total_units']) && isset($transaction->jumlah_malam))
                <td class="text-center">{{ $transaction->jumlah_malam }}</td>
                @endif
                <td class="text-right">
                    @if(isset($transaction->variant_price))
                        RM {{ number_format($transaction->variant_price, 2) }}
                    @elseif(isset($transaction->jumlah_malam))
                        RM {{ number_format($transaction->jumlah_malam * $report['price_per_unit'], 2) }}
                    @else
                        RM {{ number_format($report['price_per_unit'], 2) }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ isset($report['total_units']) ? '7' : '6' }}" class="text-center">No transactions found</td>
            </tr>
            @endforelse
        </tbody>
        @if($report['transactions']->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="{{ isset($report['total_units']) ? '6' : '5' }}" class="text-right">TOTAL OWNER REVENUE:</td>
                <td class="text-right">RM {{ number_format($report['total_revenue'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Performance Analysis -->
    @if($report['transactions']->count() > 0)
    <div style="margin-top: 30px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">Performance Analysis</h3>
        <table style="width: 100%;">
            <tr>
                <td width="33%">
                    <strong>Avg. Participants per Booking:</strong><br>
                    {{ number_format($report['total_participants'] / $report['usage_count'], 1) }} pax
                </td>
                <td width="33%">
                    <strong>Avg. Owner Revenue per Booking:</strong><br>
                    RM {{ number_format($report['total_revenue'] / $report['usage_count'], 2) }}
                </td>
                @if(isset($report['total_units']))
                <td width="33%">
                    <strong>Avg. {{ ucfirst($report['unit_name']) }}s per Booking:</strong><br>
                    {{ number_format($report['total_units'] / $report['usage_count'], 1) }} {{ $report['unit_name'] }}s
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Kampung Telaga Air - Financial Report System</p>
    </div>
</body>
</html>