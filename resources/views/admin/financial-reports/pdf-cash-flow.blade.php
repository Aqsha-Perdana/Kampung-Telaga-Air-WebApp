<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow - {{ \Carbon\Carbon::parse($startDate)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/y') }}</title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; line-height: 1.2; color: #333; margin: 0; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .company-name { font-size: 14pt; font-weight: bold; }
        .report-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #eee; }
        
        .section-header { background-color: #f0f0f0; font-weight: bold; font-size: 9pt; }
        .indent-1 { padding-left: 15px; }
        .indent-2 { padding-left: 30px; color: #666; font-size: 8pt; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc3545; }
        .highlight-row { background-color: #f8f9fa; font-weight: bold; border-top: 1px solid #333; }
        
        /* Layout Dashboard */
        .flex-container { width: 100%; margin-top: 10px; }
        .summary-box { 
            background: #2c3e50; color: white; padding: 10px; border-radius: 5px; 
            width: 45%; float: left; margin-right: 2%;
        }
        .kpi-mini-box { 
            width: 50%; float: left; border: 1px solid #ddd; padding: 8px; border-radius: 5px;
        }
        .clearfix::after { content: ""; clear: both; display: table; }

        .insight-section { 
            margin-top: 10px; padding: 8px; background-color: #fff3cd; 
            border-left: 3px solid #ffc107; font-size: 8pt; 
        }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KAMPUNG TELAGA AIR</div>
        <div class="report-title">Statement of Cash Flows (Direct Method)</div>
        <div style="font-size: 8pt; font-style: italic;">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} 
            | Generated: {{ $generatedAt->format('d/m/y H:i') }}
        </div>
    </div>

    <table>
        <tbody>
            <tr class="section-header"><td colspan="2">CASH FLOWS FROM OPERATING ACTIVITIES</td></tr>
            <tr>
                <td class="indent-1">Cash Receipts from Customers</td>
                <td class="text-right">{{ number_format($cashFlow['operating_activities']['cash_receipts']['from_customers'], 2) }}</td>
            </tr>
            @foreach($cashFlow['operating_activities']['cash_receipts']['by_payment_method'] as $method => $data)
            <tr>
                <td class="indent-2">via {{ $method === 'stripe' ? 'Credit/Debit Card (Stripe)' : ucfirst($method) }} ({{ $data['count'] }} transactions)</td>
                <td class="text-right">{{ number_format($data['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td class="indent-1">Cash Payments to Suppliers & Service Providers</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['operating_activities']['cash_payments']['to_suppliers'], 2) }})</td>
            </tr>
            <tr>
                <td class="indent-1">Refunds Paid to Customers ({{ $cashFlow['operating_activities']['cash_payments']['refund_transactions'] }} transactions)</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['operating_activities']['cash_payments']['refunds_to_customers'], 2) }})</td>
            </tr>
            <tr>
                <td class="indent-1">Cash Payments for Operating Expenses</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['operating_activities']['cash_payments']['operating_expenses'], 2) }})</td>
            </tr>
            <tr>
                <td class="indent-1">Total Cash Payments</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['operating_activities']['cash_payments']['to_suppliers'] + $cashFlow['operating_activities']['cash_payments']['refunds_to_customers'] + $cashFlow['operating_activities']['cash_payments']['operating_expenses'], 2) }})</td>
            </tr>
            <tr class="highlight-row">
                <td>Net Cash from Operating Activities</td>
                <td class="text-right">{{ number_format($cashFlow['operating_activities']['net_cash_from_operating'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">CASH FLOWS FROM INVESTING ACTIVITIES</td></tr>
            <tr>
                <td class="indent-1">Proceeds from Sale of Assets</td>
                <td class="text-right">{{ number_format($cashFlow['investing_activities']['cash_inflows']['sale_of_assets'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Acquisition of Property, Plant & Equipment</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['investing_activities']['cash_outflows']['purchase_of_assets'], 2) }})</td>
            </tr>
            <tr class="highlight-row">
                <td>Net Cash used in Investing Activities</td>
                <td class="text-right">{{ number_format($cashFlow['investing_activities']['net_cash_from_investing'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">CASH FLOWS FROM FINANCING ACTIVITIES</td></tr>
            <tr>
                <td class="indent-1">Proceeds from Borrowings / Loans Received</td>
                <td class="text-right">{{ number_format($cashFlow['financing_activities']['cash_inflows']['loans_received'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Repayment of Borrowings</td>
                <td class="text-right text-danger">({{ number_format($cashFlow['financing_activities']['cash_outflows']['loan_repayments'], 2) }})</td>
            </tr>
            <tr class="highlight-row">
                <td>Net Cash from Financing Activities</td>
                <td class="text-right">{{ number_format($cashFlow['financing_activities']['net_cash_from_financing'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">CASH RECONCILIATION</td></tr>
            <tr>
                <td class="indent-1 fw-bold">NET INCREASE/(DECREASE) IN CASH</td>
                <td class="text-right fw-bold">{{ number_format($cashFlow['cash_summary']['net_increase_in_cash'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Cash and Cash Equivalents at Beginning of Period</td>
                <td class="text-right">{{ number_format($cashFlow['cash_reconciliation']['opening_balance'], 2) }}</td>
            </tr>
            <tr class="highlight-row" style="background-color: #d4edda; font-size: 10pt; border-bottom: 3px double #333;">
                <td>CASH AND CASH EQUIVALENTS AT END OF PERIOD</td>
                <td class="text-right">RM {{ number_format($cashFlow['cash_reconciliation']['closing_balance'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="flex-container clearfix no-break">
        <div class="summary-box">
            <div style="font-size: 8pt; margin-bottom: 5px;">CASH POSITION (MYR)</div>
            <table style="color: white; margin: 0; border: none;">
                <tr style="border: none;">
                    <td style="border: none; padding: 2px;">Opening: <b>{{ number_format($cashFlow['cash_reconciliation']['opening_balance'], 2) }}</b></td>
                    <td style="border: none; padding: 2px;">Closing: <b>{{ number_format($cashFlow['cash_reconciliation']['closing_balance'], 2) }}</b></td>
                </tr>
            </table>
        </div>
        
        <div class="kpi-mini-box">
            <div style="font-weight: bold; font-size: 8pt; margin-bottom: 4px;">CASH FLOW METRICS</div>
            <table style="margin: 0; font-size: 7.5pt;">
                <tr>
                    <td>Total Trans: <b>{{ $cashFlow['statistics']['total_transactions'] }}</b></td>
                    <td>Burn Rate: <b>{{ number_format($cashFlow['operating_activities']['cash_payments']['operating_expenses'], 2) }}</b></td>
                </tr>
                <tr>
                    <td>Net Growth: <b>{{ number_format($cashFlow['cash_summary']['net_increase_in_cash'], 2) }}</b></td>
                    <td>Status: <b>{{ $cashFlow['cash_summary']['net_increase_in_cash'] >= 0 ? 'SURPLUS' : 'DEFICIT' }}</b></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="insight-section no-break">
        <b>💡 CASH FLOW INSIGHTS:</b> 
        Liquidity for this period shows a trend {{ $cashFlow['cash_summary']['net_increase_in_cash'] >= 0 ? 'POSITIF' : 'NEGATIF' }}. 
        Operating activities contributed {{ number_format($cashFlow['operating_activities']['cash_receipts']['from_customers'] > 0 ? ($cashFlow['operating_activities']['net_cash_from_operating'] / $cashFlow['operating_activities']['cash_receipts']['from_customers']) * 100 : 0, 1) }}% cash efficiency from total customer receipts. 
        Closing cash balance of RM {{ number_format($cashFlow['cash_reconciliation']['closing_balance'], 2) }} available for the next operational period.
    </div>

    <div style="position: fixed; bottom: 0; width: 100%; font-size: 7pt; text-align: center; color: #999; border-top: 1px solid #ddd; padding-top: 5px;">
        This statement is prepared in accordance with Malaysian Financial Reporting Standards (MFRS 107)
    </div>
</body>
</html>

