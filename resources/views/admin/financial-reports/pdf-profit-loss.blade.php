<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>P&L - {{ \Carbon\Carbon::parse($startDate)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/y') }}</title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; line-height: 1.2; color: #333; margin: 0; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .company-name { font-size: 14pt; font-weight: bold; }
        .report-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #eee; }
        
        .section-header { background-color: #f5f5f5; font-weight: bold; font-size: 9pt; border-top: 1px solid #ddd; }
        .indent-1 { padding-left: 20px; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc3545; }
        .highlight-row { background-color: #fcfcfc; font-weight: bold; border-top: 1px solid #000; }
        .double-underline { border-bottom: 4px double #000; }
        
        /* Layout Elements */
        .dashboard-container { width: 100%; margin-top: 10px; }
        .summary-card { 
            background: #2c3e50; color: white; padding: 12px; border-radius: 4px; 
            width: 46%; float: left; margin-right: 2%;
        }
        .metrics-card { 
            width: 48%; float: left; border: 1px solid #ccc; padding: 10px; border-radius: 4px;
        }
        .clearfix::after { content: ""; clear: both; display: table; }

        .mfrs-note { 
            margin-top: 12px; padding: 10px; background-color: #f9f9f9; 
            border-left: 4px solid #333; font-size: 8pt; 
        }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KAMPUNG TELAGA AIR</div>
        <div class="report-title">Statement of Profit or Loss and Other Comprehensive Income</div>
        <div style="font-size: 8pt; font-style: italic; color: #555;">
            For the financial period from {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr style="border-bottom: 2px solid #000;">
                <th width="70%">DESCRIPTION</th>
                <th width="30%" class="text-right">MYR</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header"><td colspan="2">REVENUE</td></tr>
            <tr>
                <td class="indent-1">Tour Package Sales</td>
                <td class="text-right">{{ number_format($profitLoss['revenue']['tour_package_sales'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Other Operating Revenue</td>
                <td class="text-right">{{ number_format($profitLoss['revenue']['other_revenue'], 2) }}</td>
            </tr>
            <tr class="fw-bold">
                <td>Total Revenue</td>
                <td class="text-right">{{ number_format($profitLoss['revenue']['total_revenue'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">COST OF SALES</td></tr>
            <tr><td class="indent-1">Boat Services</td><td class="text-right text-danger">({{ number_format($profitLoss['cost_of_sales']['boat_services'], 2) }})</td></tr>
            <tr><td class="indent-1">Homestay Services</td><td class="text-right text-danger">({{ number_format($profitLoss['cost_of_sales']['homestay_services'], 2) }})</td></tr>
            <tr><td class="indent-1">Culinary Services</td><td class="text-right text-danger">({{ number_format($profitLoss['cost_of_sales']['culinary_services'], 2) }})</td></tr>
            <tr><td class="indent-1">Kiosk Services</td><td class="text-right text-danger">({{ number_format($profitLoss['cost_of_sales']['kiosk_services'], 2) }})</td></tr>
            <tr class="highlight-row">
                <td>GROSS PROFIT</td>
                <td class="text-right">{{ number_format($profitLoss['gross_profit']['amount'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">OTHER OPERATING EXPENSES</td></tr>
            @foreach($profitLoss['operating_expenses']['by_nature'] as $category => $data)
            <tr>
                <td class="indent-1">{{ $category }}</td>
                <td class="text-right text-danger">({{ number_format($data['amount'], 2) }})</td>
            </tr>
            @endforeach
            <tr class="highlight-row">
                <td>RESULTS FROM OPERATING ACTIVITIES</td>
                <td class="text-right">{{ number_format($profitLoss['operating_profit']['amount'], 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">OTHER INCOME / (EXPENSES)</td></tr>
            <tr>
                <td class="indent-1">Refund Fee Income</td>
                <td class="text-right">{{ number_format($profitLoss['other_items']['refund_fee_income'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Total Other Income</td>
                <td class="text-right">{{ number_format($profitLoss['other_items']['other_income'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Other Expenses</td>
                <td class="text-right text-danger">({{ number_format($profitLoss['other_items']['other_expenses'], 2) }})</td>
            </tr>
            <tr>
                <td class="indent-1">Net Other Income / (Expenses)</td>
                <td class="text-right">{{ number_format($profitLoss['other_items']['net_other_items'], 2) }}</td>
            </tr>
            <tr class="fw-bold">
                <td>PROFIT BEFORE TAX</td>
                <td class="text-right">{{ number_format($profitLoss['profit_before_tax']['amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="indent-1">Income Tax Expense</td>
                <td class="text-right text-danger">({{ number_format($profitLoss['tax_expense']['total_tax'], 2) }})</td>
            </tr>

            <tr class="highlight-row double-underline" style="background-color: #f1f8e9; font-size: 10pt;">
                <td>PROFIT FOR THE FINANCIAL PERIOD</td>
                <td class="text-right">RM {{ number_format($profitLoss['profit_for_period']['amount'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="dashboard-container clearfix no-break">
        <div class="summary-card">
            <div style="font-size: 8pt; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.3); margin-bottom: 5px;">Performance Summary</div>
            <div style="font-size: 14pt; font-weight: bold;">RM {{ number_format($profitLoss['profit_for_period']['amount'], 2) }}</div>
            <div style="font-size: 7.5pt; opacity: 0.8;">Net Profit after tax and adjustments</div>
        </div>
        
        <div class="metrics-card">
            <div style="font-weight: bold; font-size: 8pt; margin-bottom: 6px;">BUSINESS ANALYTICS</div>
            <table style="margin: 0; font-size: 7.5pt; border: none;">
                <tr style="border: none;">
                    <td style="border: none; padding: 2px;">GP Margin: <b>{{ number_format($profitLoss['gross_profit']['margin_percentage'], 1) }}%</b></td>
                    <td style="border: none; padding: 2px;">Net Margin: <b>{{ number_format($profitLoss['profit_for_period']['margin_percentage'], 1) }}%</b></td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; padding: 2px;">Total Orders: <b>{{ $profitLoss['transactions']['total_orders'] }}</b></td>
                    <td style="border: none; padding: 2px;">Cust. Base: <b>{{ $profitLoss['transactions']['total_customers'] }}</b></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mfrs-note no-break">
        <strong>MANAGEMENT NOTES:</strong><br>
        This report has been prepared in accordance with the requirements of Malaysian Financial Reporting Standards (MFRS 101). 
        The net profit margin was recorded at {{ number_format($profitLoss['profit_for_period']['margin_percentage'], 1) }}% of total operating income RM {{ number_format($profitLoss['revenue']['total_revenue'], 2) }}.
    </div>

    <div style="position: fixed; bottom: 0; width: 100%; font-size: 7pt; text-align: center; color: #888; border-top: 1px solid #eee; padding-top: 5px;">
        This statement is computer generated and is for internal reporting purposes. | MFRS 101 Compliant
    </div>
</body>
</html>
