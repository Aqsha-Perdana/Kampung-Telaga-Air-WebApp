@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <!-- Header with Date Filter & Export -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 class="mb-0 fw-bold">📊 Financial Reports</h4>
                    <p class="text-muted mb-0 small">Compliant with MFRS Standards</p>
                </div>
                <div class="col-md-8">
                    <form method="GET" action="{{ route('financial-reports.index') }}" class="row g-2">
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="" class="btn btn-danger w-100">
                                <i class="ti ti-file-type-pdf me-1"></i> PDF
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="" class="btn btn-success w-100">
                                <i class="ti ti-file-spreadsheet me-1"></i> Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Revenue</p>
                            <h4 class="mb-0 fw-bold">{{ format_ringgit_report($profitLoss['revenue']['total_revenue']) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="ti ti-trending-up" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Gross Profit</p>
                            <h4 class="mb-0 fw-bold">{{ format_ringgit_report($profitLoss['gross_profit']['amount']) }}</h4>
                            <small class="text-success">{{ number_format($profitLoss['gross_profit']['margin_percentage'], 1) }}% margin</small>
                        </div>
                        <div class="text-success">
                            <i class="ti ti-chart-line" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Operating Expenses</p>
                            <h4 class="mb-0 fw-bold">{{ format_ringgit_report($profitLoss['operating_expenses']['total_operating_expenses']) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="ti ti-receipt" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-{{ $profitLoss['profit_for_period']['amount'] >= 0 ? 'success' : 'danger' }} border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Net Profit</p>
                            <h4 class="mb-0 fw-bold">{{ format_ringgit_report($profitLoss['profit_for_period']['amount']) }}</h4>
                            <small class="text-{{ $profitLoss['profit_for_period']['amount'] >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($profitLoss['profit_for_period']['margin_percentage'], 1) }}% margin
                            </small>
                        </div>
                        <div class="text-{{ $profitLoss['profit_for_period']['amount'] >= 0 ? 'success' : 'danger' }}">
                            <i class="ti ti-award" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#profit-loss">
                <i class="ti ti-report-money me-1"></i> Statement of Profit or Loss
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cash-flow">
                <i class="ti ti-cash me-1"></i> Statement of Cash Flows
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#owner-reports">
                <i class="ti ti-users me-1"></i> Owner Reports
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- STATEMENT OF PROFIT OR LOSS -->
        <div class="tab-pane fade show active" id="profit-loss">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Statement of Profit or Loss and Other Comprehensive Income</h5>
                    <small class="text-muted">For the period from {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</small>
                </div>
                <div class="card-body">
                    <!-- Revenue Section -->
                    <table class="table table-sm">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>REVENUE</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Tour Package Sales</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['revenue']['tour_package_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Other Revenue</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['revenue']['other_revenue']) }}</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Total Revenue</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['revenue']['total_revenue']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Cost of Sales Section -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>COST OF SALES</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Boat Services</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['cost_of_sales']['boat_services']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Homestay Services</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['cost_of_sales']['homestay_services']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Culinary Services</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['cost_of_sales']['culinary_services']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Kiosk Services</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['cost_of_sales']['kiosk_services']) }})</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Total Cost of Sales</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['cost_of_sales']['total_cost_of_sales']) }})</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Gross Profit -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-success fw-bold">
                                <td class="ps-4">GROSS PROFIT</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['gross_profit']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-5 text-muted small">Gross Profit Margin</td>
                                <td class="text-end text-muted small">{{ number_format($profitLoss['gross_profit']['margin_percentage'], 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Operating Expenses -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>OPERATING EXPENSES (by nature)</strong></td>
                            </tr>
                            @foreach($profitLoss['operating_expenses']['by_nature'] as $category => $data)
                            <tr>
                                <td class="ps-4">{{ $category }} <span class="badge bg-secondary">{{ $data['count'] }}x</span></td>
                                <td class="text-end text-danger">({{ format_ringgit_report($data['amount']) }})</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Total Operating Expenses</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['operating_expenses']['total_operating_expenses']) }})</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Operating Profit -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-info fw-bold">
                                <td class="ps-4">OPERATING PROFIT (EBIT)</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['operating_profit']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-5 text-muted small">Operating Profit Margin</td>
                                <td class="text-end text-muted small">{{ number_format($profitLoss['operating_profit']['margin_percentage'], 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Other Items -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>OTHER INCOME/(EXPENSES)</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Other Income</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['other_items']['other_income']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Other Expenses</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['other_items']['other_expenses']) }})</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Profit Before Tax -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-warning fw-bold">
                                <td class="ps-4">PROFIT BEFORE TAX</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['profit_before_tax']['amount']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Tax Expense -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>TAX EXPENSE</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Current Tax</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['tax_expense']['current_tax']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Deferred Tax</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['tax_expense']['deferred_tax']) }})</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Total Tax Expense</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['tax_expense']['total_tax']) }})</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Net Profit -->
                    <table class="table table-sm mt-3">
                        <tbody>
                            <tr class="table-{{ $profitLoss['profit_for_period']['amount'] >= 0 ? 'success' : 'danger' }} fw-bold">
                                <td class="ps-4"><strong>PROFIT FOR THE PERIOD</strong></td>
                                <td class="text-end fs-5">{{ format_ringgit_report($profitLoss['profit_for_period']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-5 text-muted small">Net Profit Margin</td>
                                <td class="text-end text-muted small">{{ number_format($profitLoss['profit_for_period']['margin_percentage'], 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Financial Ratios -->
                    <div class="alert alert-light border mt-4">
                        <h6 class="fw-bold mb-3">📊 Key Performance Indicators</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Gross Profit Margin</p>
                                <h5 class="mb-0 text-success">{{ number_format($profitLoss['gross_profit']['margin_percentage'], 2) }}%</h5>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Operating Profit Margin</p>
                                <h5 class="mb-0 text-info">{{ number_format($profitLoss['operating_profit']['margin_percentage'], 2) }}%</h5>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Net Profit Margin</p>
                                <h5 class="mb-0 text-{{ $profitLoss['profit_for_period']['amount'] >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($profitLoss['profit_for_period']['margin_percentage'], 2) }}%
                                </h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Total Orders</p>
                                <h5 class="mb-0">{{ $profitLoss['transactions']['total_orders'] }}</h5>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Total Customers</p>
                                <h5 class="mb-0">{{ $profitLoss['transactions']['total_customers'] }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Breakdown -->
                    <h6 class="mt-4 mb-3 fw-bold">Detailed Revenue Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Cost of Sales</th>
                                    <th class="text-end">Gross Profit</th>
                                    <th class="text-center">Currency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profitLoss['revenue_breakdown'] as $order)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $order['order_id'] }}</span></td>
                                    <td>{{ $order['customer'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order['date'])->format('d M Y') }}</td>
                                    <td class="text-end">{{ format_ringgit_report($order['revenue']) }}</td>
                                    <td class="text-end text-danger">{{ format_ringgit_report($order['cost_of_sales']) }}</td>
                                    <td class="text-end fw-semibold text-success">{{ format_ringgit_report($order['gross_profit']) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $order['currency_info']['display_currency'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATEMENT OF CASH FLOWS -->
        <div class="tab-pane fade" id="cash-flow">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Statement of Cash Flows</h5>
                    <small class="text-muted">For the period from {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</small>
                </div>
                <div class="card-body">
                    <!-- Operating Activities -->
                    <table class="table table-sm">
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="2"><strong>CASH FLOWS FROM OPERATING ACTIVITIES</strong></td>
                            </tr>
                            <tr class="table-light">
                                <td class="ps-4"><strong>Cash Receipts from Customers</strong></td>
                                <td class="text-end fw-bold">{{ format_ringgit_report($cashFlow['operating_activities']['cash_receipts']['from_customers']) }}</td>
                            </tr>
                            @foreach($cashFlow['operating_activities']['cash_receipts']['by_payment_method'] as $method => $data)
                            <tr>
                                <td class="ps-5 text-muted small">via {{ $method === 'stripe' ? 'Credit/Debit Card (Stripe)' : ucfirst($method) }} ({{ $data['count'] }} transactions)</td>
                                <td class="text-end text-muted small">{{ format_ringgit_report($data['amount']) }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="table-light mt-3">
                                <td class="ps-4"><strong>Cash Payments</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="ps-5">To Suppliers and Service Providers</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['to_suppliers']) }})</td>
                            </tr>
                            @foreach($cashFlow['operating_activities']['cash_payments']['supplier_breakdown'] as $type => $amount)
                            <tr>
                                <td class="ps-6 text-muted small">{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-end text-muted small text-danger">({{ format_ringgit_report($amount) }})</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="ps-5">Operating Expenses</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['operating_expenses']) }})</td>
                            </tr>
                            
                            <tr class="table-success fw-bold border-top">
                                <td class="ps-4">Net Cash from Operating Activities</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['operating_activities']['net_cash_from_operating']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Investing Activities -->
                    <table class="table table-sm mt-4">
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="2"><strong>CASH FLOWS FROM INVESTING ACTIVITIES</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Purchase of Property, Plant & Equipment</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['investing_activities']['cash_outflows']['purchase_of_assets']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Proceeds from Sale of Assets</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['investing_activities']['cash_inflows']['sale_of_assets']) }}</td>
                            </tr>
                            <tr class="table-info fw-bold border-top">
                                <td class="ps-4">Net Cash from Investing Activities</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['investing_activities']['net_cash_from_investing']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Financing Activities -->
                    <table class="table table-sm mt-4">
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="2"><strong>CASH FLOWS FROM FINANCING ACTIVITIES</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Proceeds from Borrowings</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['financing_activities']['cash_inflows']['loans_received']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Repayment of Borrowings</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['financing_activities']['cash_outflows']['loan_repayments']) }})</td>
                            </tr>
                            <tr class="table-warning fw-bold border-top">
                                <td class="ps-4">Net Cash from Financing Activities</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['financing_activities']['net_cash_from_financing']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Net Cash Movement -->
                    <table class="table table-sm mt-4">
                        <tbody>
                            <tr class="table-light">
                                <td colspan="2"><strong>CASH AND CASH EQUIVALENTS</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-4">Net Increase/(Decrease) in Cash</td>
                                <td class="text-end fw-bold">{{ format_ringgit_report($cashFlow['cash_summary']['net_increase_in_cash']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Cash at Beginning of Period</td>
                                <td class="text-end">{{ format_ringgit_report($cashFlow['cash_reconciliation']['opening_balance']) }}</td>
                            </tr>
                            <tr class="table-success fw-bold border-top">
                                <td class="ps-4">Cash at End of Period</td>
                                <td class="text-end fs-5">{{ format_ringgit_report($cashFlow['cash_reconciliation']['closing_balance']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Cash Flow Summary -->
                    <div class="alert alert-light border mt-4">
                        <h6 class="fw-bold mb-3">💡 Cash Flow Analysis</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-1 small text-muted">Operating Activities</p>
                                <h5 class="mb-0 text-success">{{ format_ringgit_report($cashFlow['cash_summary']['net_cash_from_operating']) }}</h5>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1 small text-muted">Investing Activities</p>
                                <h5 class="mb-0 text-info">{{ format_ringgit_report($cashFlow['cash_summary']['net_cash_from_investing']) }}</h5>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1 small text-muted">Financing Activities</p>
                                <h5 class="mb-0 text-warning">{{ format_ringgit_report($cashFlow['cash_summary']['net_cash_from_financing']) }}</h5>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1 small text-muted">Net Cash Movement</p>
                                <h5 class="mb-0 text-{{ $cashFlow['cash_summary']['net_increase_in_cash'] >= 0 ? 'success' : 'danger' }}">
                                    {{ format_ringgit_report($cashFlow['cash_summary']['net_increase_in_cash']) }}
                                </h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Total Transactions</p>
                                <h5 class="mb-0">{{ $cashFlow['statistics']['total_transactions'] }}</h5>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Average Transaction Value</p>
                                <h5 class="mb-0">{{ format_ringgit_report($cashFlow['statistics']['average_transaction_value']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OWNER REPORTS (Same as before) -->
        <div class="tab-pane fade" id="owner-reports">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">👥 Report by Resource Owner</h5>
                    
                    <!-- Boats Section -->
                    <h6 class="mb-3 mt-4"><i class="ti ti-anchor text-primary me-2"></i>Boat Owners</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Boat Name</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Participants</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ownerSummary['boats'] as $boat)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $boat['id'] }}</span></td>
                                    <td class="fw-semibold">{{ $boat['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $boat['usage_count'] }}x</span></td>
                                    <td class="text-center">{{ $boat['total_participants'] }}</td>
                                    <td class="text-end fw-bold"> {{ format_ringgit_report($boat['total_revenue']) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('financial-reports.owner', ['type' => 'boat', 'id' => $boat['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Homestays Section -->
                    <h6 class="mb-3 mt-4"><i class="ti ti-home text-success me-2"></i>Homestay Owners</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Homestay Name</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Nights</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ownerSummary['homestays'] as $homestay)
                                <tr>
                                    <td><span class="badge bg-success">{{ $homestay['id'] }}</span></td>
                                    <td class="fw-semibold">{{ $homestay['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $homestay['usage_count'] }}x</span></td>
                                    <td class="text-center">{{ $homestay['total_units'] }}</td>
                                    <td class="text-end fw-bold"> {{ format_ringgit_report($homestay['total_revenue']) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('financial-reports.owner', ['type' => 'homestay', 'id' => $homestay['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Culinary Section -->
                    <h6 class="mb-3 mt-4"><i class="ti ti-tools-kitchen-2 text-warning me-2"></i>Culinary Owners</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Package Name</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Participants</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ownerSummary['culinary'] as $culinary)
                                <tr>
                                    <td><span class="badge bg-warning">{{ $culinary['id'] }}</span></td>
                                    <td class="fw-semibold">{{ $culinary['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $culinary['usage_count'] }}x</span></td>
                                    <td class="text-center">{{ $culinary['total_participants'] }}</td>
                                    <td class="text-end fw-bold"> {{ format_ringgit_report($culinary['total_revenue']) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('financial-reports.owner', ['type' => 'culinary', 'id' => $culinary['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Kiosks Section -->
                    <h6 class="mb-3 mt-4"><i class="ti ti-building-store text-info me-2"></i>Kiosk Owners</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Kiosk Name</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Participants</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ownerSummary['kiosks'] as $kiosk)
                                <tr>
                                    <td><span class="badge bg-info">{{ $kiosk['id'] }}</span></td>
                                    <td class="fw-semibold">{{ $kiosk['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $kiosk['usage_count'] }}x</span></td>
                                    <td class="text-center">{{ $kiosk['total_participants'] }}</td>
                                    <td class="text-end fw-bold"> {{ format_ringgit_report($kiosk['total_revenue']) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('financial-reports.owner', ['type' => 'kiosk', 'id' => $kiosk['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
@media print {
    .sidebar, .navbar, .btn, .nav-pills { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection





