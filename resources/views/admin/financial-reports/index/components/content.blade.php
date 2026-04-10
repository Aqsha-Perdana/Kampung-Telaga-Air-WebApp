<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Header with Date Filter & Export -->
    <div class="card mb-4 report-header-card">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-xl-4 col-lg-12 mb-3 mb-xl-0">
                <div class="d-flex align-items-center">
                    <span class="fs-2 me-2"><i class="ti ti-chart-bar"></i></span>
                    <div>
                        <h4 class="mb-0 fw-bold">Financial Reports</h4>
                        <p class="text-muted mb-0 small">Compliant with MFRS Standards</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-12">
                <form method="GET" action="{{ route('financial-reports.index') }}" class="row g-2 justify-content-xl-end align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="small text-muted mb-1">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="small text-muted mb-1">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>

                        <div class="dropdown flex-grow-1">
                            <button class="btn btn-danger w-100 dropdown-toggle shadow-sm" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    data-bs-display="static" 
                                    aria-expanded="false">
                                <i class="ti ti-file-type-pdf me-1"></i> PDF
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end custom-report-dropdown">
                                <li class="dropdown-header small text-uppercase">Select Report Type</li>
                                <li><a class="dropdown-item py-2" href="{{ route('financial-reports.export-profit-loss-pdf', request()->all()) }}">Profit Loss Report</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('financial-reports.export-cash-flow-pdf', request()->all()) }}">Cash Flow Report</a></li>
                            </ul>
                        </div>

                        <a href="{{ route('financial-reports.export-excel', request()->all()) }}" class="btn btn-success">
                            <i class="ti ti-file-spreadsheet me-1"></i> Excel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  

    <div class="card mb-4 opening-cash-card">
        <div class="card-body">
            <div class="opening-cash-shell">
                <div class="opening-cash-summary">
                    <div class="opening-cash-meta-row">
                        <div>
                            <p class="opening-cash-eyebrow mb-1">Opening Cash</p>
                            <h4 class="opening-cash-amount mb-1">{{ format_ringgit_report($cashFlow['cash_reconciliation']['opening_balance']) }}</h4>
                            @if($cashFlow['cash_reconciliation']['opening_balance_is_manual'])
                                <p class="opening-cash-caption mb-0">
                                    Applied from {{ \Carbon\Carbon::parse($cashFlow['cash_reconciliation']['opening_balance_date'])->format('d M Y') }}
                                </p>
                            @else
                                <p class="opening-cash-caption mb-0">No manual balance found before {{ $startDate->format('d M Y') }}</p>
                            @endif
                        </div>
                        <span class="opening-cash-state {{ $cashFlow['cash_reconciliation']['opening_balance_is_manual'] ? 'is-manual' : 'is-fallback' }}">
                            {{ $cashFlow['cash_reconciliation']['opening_balance_is_manual'] ? 'Manual' : 'Fallback' }}
                        </span>
                    </div>

                    @if(!empty($cashFlow['cash_reconciliation']['opening_balance_notes']))
                    <p class="opening-cash-note mb-0">{{ $cashFlow['cash_reconciliation']['opening_balance_notes'] }}</p>
                    @endif

                    @if(!$cashFlow['cash_reconciliation']['opening_balance_is_manual'])
                    <p class="opening-cash-helper mb-0">Cash flow is still using RM 0.00 as fallback opening balance.</p>
                    @elseif(\Carbon\Carbon::parse($cashFlow['cash_reconciliation']['opening_balance_date'])->toDateString() !== $startDate->toDateString())
                    <p class="opening-cash-helper mb-0">The latest balance before the selected start date is being used.</p>
                    @endif

                    @if($recentOpeningBalances->isNotEmpty())
                    <div class="opening-cash-history">
                        @foreach($recentOpeningBalances as $balanceRecord)
                        <span class="opening-cash-history-item">
                            {{ $balanceRecord->balance_date->format('d M Y') }} · {{ format_ringgit_report($balanceRecord->amount) }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="opening-cash-form-wrap">
                    <form method="POST" action="{{ route('financial-reports.opening-balance.store') }}" class="opening-cash-form">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="small text-muted mb-1">Date</label>
                                <input
                                    type="date"
                                    name="balance_date"
                                    class="form-control form-control-sm @error('balance_date') is-invalid @enderror"
                                    value="{{ old('balance_date', optional($currentOpeningBalanceRecord?->balance_date)->format('Y-m-d') ?? $startDate->format('Y-m-d')) }}">
                                @error('balance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted mb-1">Amount (MYR)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="amount"
                                    class="form-control form-control-sm @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', $currentOpeningBalanceRecord?->amount !== null ? number_format((float) $currentOpeningBalanceRecord->amount, 2, '.', '') : number_format((float) ($cashFlow['cash_reconciliation']['opening_balance'] ?? 0), 2, '.', '')) }}">
                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-info text-white w-100">
                                    <i class="ti ti-device-floppy me-1"></i>{{ $currentOpeningBalanceRecord ? 'Update' : 'Save' }}
                                </button>
                            </div>

                            <div class="col-12">
                                <textarea
                                    name="notes"
                                    rows="2"
                                    class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                    placeholder="Optional note">{{ old('notes', $currentOpeningBalanceRecord?->notes) }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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

    <div class="alert alert-light border small">
        Profit &amp; Loss is recognized by order date for `paid`, `confirmed`, `completed`, and refunded impact. Cash Flow uses the direct method with gross customer receipts plus separate operating cash payments based on payment and refund dates.
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
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#gateway-fee-report">
                <i class="ti ti-credit-card-pay me-1"></i> Gateway Fee (MDR) Report
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
                                <td class="ps-4">Gross Tour Package Sales</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['revenue']['gross_tour_package_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Less: Sales Discounts</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['revenue']['sales_discounts']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Net Tour Package Sales</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['revenue']['net_tour_package_sales']) }}</td>
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
                    <div class="alert alert-light border small mt-2">
                        Revenue is presented as gross package sales less sales discounts. Gateway fee (MDR) is shown separately under operating expenses.
                    </div>

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
                                <td class="ps-4">Refund Fee Income</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['other_items']['refund_fee_income']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Total Other Income</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['other_items']['other_income']) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Other Expenses</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($profitLoss['other_items']['other_expenses']) }})</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Net Other Items</td>
                                <td class="text-end">{{ format_ringgit_report($profitLoss['other_items']['net_other_items']) }}</td>
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
                        <h6 class="fw-bold mb-3"><i class="ti ti-chart-bar me-1"></i> Key Performance Indicators</h6>
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
                    <h6 class="mt-4 mb-3 fw-bold">Detailed Transaction Impact</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                                <thead class="table-light">
                                  <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Gross Sales</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Net Revenue</th>
                                    <th class="text-end">Cost of Sales</th>
                                    <th class="text-end">Gateway Fee (MDR)</th>
                                    <th class="text-end">Other Income</th>
                                    <th class="text-end">Net Impact</th>
                                    <th class="text-center">Currency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profitLoss['revenue_breakdown'] as $order)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $order['order_id'] }}</span></td>
                                    <td>{{ $order['customer'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order['date'])->format('d M Y') }}</td>
                                      <td class="text-center">
                                          <span class="badge bg-{{ $order['status'] === 'refunded' ? 'dark' : 'success' }}">
                                              {{ ucwords(str_replace('_', ' ', $order['status'])) }}
                                          </span>
                                      </td>
                                      <td class="text-end">{{ format_ringgit_report($order['gross_revenue'] ?? 0) }}</td>
                                      <td class="text-end text-danger">({{ format_ringgit_report($order['sales_discount'] ?? 0) }})</td>
                                      <td class="text-end">{{ format_ringgit_report($order['net_revenue'] ?? $order['revenue'] ?? 0) }}</td>
                                      <td class="text-end text-danger">{{ format_ringgit_report($order['cost_of_sales']) }}</td>
                                      <td class="text-end text-danger">{{ format_ringgit_report($order['gateway_fee'] ?? 0) }}</td>
                                      <td class="text-end text-info">{{ format_ringgit_report($order['other_income'] ?? 0) }}</td>
                                    <td class="text-end fw-semibold {{ ($order['net_profit_impact'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">{{ format_ringgit_report($order['net_profit_impact'] ?? 0) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $order['currency_info']['display_currency'] ?: 'MYR' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No transaction impact data for this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(isset($impactPaginator) && $impactPaginator->hasPages())
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Showing {{ $impactPaginator->firstItem() }}-{{ $impactPaginator->lastItem() }} of {{ $impactPaginator->total() }} transactions
                            </small>
                            {{ $impactPaginator->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="gateway-fee-report">
            @php
                $mdrReport = $profitLoss['operating_expenses']['payment_gateway_fee_report'] ?? null;
            @endphp
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Gateway Fee (MDR) Report</h5>
                    <small class="text-muted">For the period from {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border small">
                        This report groups gateway fee deductions by payment gateway and payment type, using the same recognized-order basis as the Profit &amp; Loss statement.
                    </div>

                    @if(($mdrReport['transaction_count'] ?? 0) > 0)
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <small class="text-muted d-block mb-1">Total Gateway Fee (MDR)</small>
                                    <div class="fw-bold text-danger fs-5">{{ format_ringgit_report($mdrReport['fee_amount']) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <small class="text-muted d-block mb-1">Gross Amount Affected</small>
                                    <div class="fw-bold fs-5">{{ format_ringgit_report($mdrReport['gross_amount']) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <small class="text-muted d-block mb-1">Transactions with Gateway Fee</small>
                                    <div class="fw-bold fs-5">{{ $mdrReport['transaction_count'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <small class="text-muted d-block mb-1">Average Gateway Fee Rate</small>
                                    <div class="fw-bold fs-5">{{ number_format($mdrReport['average_fee_rate'] ?? 0, 2) }}%</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach(($mdrReport['source_summary'] ?? []) as $source => $count)
                                <span class="badge {{ gateway_fee_source_badge_class($source) }} border-0">
                                    {{ gateway_fee_source_label($source) }}: {{ $count }}
                                </span>
                            @endforeach
                        </div>

                        <div class="alert alert-warning-subtle border small mb-4">
                            Values marked as <strong>Estimated</strong> are derived from configured gateway MDR rules when the gateway did not return final fee data yet.
                        </div>

                        <div class="accordion" id="mdrBreakdownAccordion">
                            @foreach(($mdrReport['methods'] ?? []) as $method)
                                <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                                    <h2 class="accordion-header" id="mdrHeading{{ $loop->index }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mdrCollapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="mdrCollapse{{ $loop->index }}">
                                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center w-100 me-3 gap-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $method['method_label'] }}</div>
                                                    <div class="text-muted small">{{ $method['transaction_count'] }} transaction{{ $method['transaction_count'] > 1 ? 's' : '' }} with gateway fee</div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 small">
                                                    <span><span class="text-muted">Gross</span> <strong>{{ format_ringgit_report($method['gross_amount']) }}</strong></span>
                                                    <span><span class="text-muted">Gateway Fee</span> <strong class="text-danger">{{ format_ringgit_report($method['fee_amount']) }}</strong></span>
                                                    <span><span class="text-muted">Avg</span> <strong>{{ number_format($method['average_fee_rate'], 2) }}%</strong></span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="mdrCollapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="mdrHeading{{ $loop->index }}" data-bs-parent="#mdrBreakdownAccordion">
                                        <div class="accordion-body bg-light-subtle">
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @foreach(($method['source_summary'] ?? []) as $source => $count)
                                                    <span class="badge {{ gateway_fee_source_badge_class($source) }} border-0">
                                                        {{ gateway_fee_source_label($source) }}: {{ $count }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Payment Type</th>
                                                            <th class="text-center">Transactions</th>
                                                            <th class="text-end">Gross</th>
                                                            <th class="text-end">Gateway Fee</th>
                                                            <th class="text-end">Avg Rate</th>
                                                            <th class="text-end">Net Settlement</th>
                                                            <th>Source Mix</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach(($method['channels'] ?? []) as $channel)
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-medium">{{ $channel['descriptor'] }}</div>
                                                                    <div class="text-muted small">{{ $channel['channel_label'] === '-' ? 'Type not captured' : 'Channel: ' . $channel['channel_label'] }}</div>
                                                                </td>
                                                                <td class="text-center">{{ $channel['transaction_count'] }}</td>
                                                                <td class="text-end">{{ format_ringgit_report($channel['gross_amount']) }}</td>
                                                                <td class="text-end text-danger">{{ format_ringgit_report($channel['fee_amount']) }}</td>
                                                                <td class="text-end">{{ number_format($channel['average_fee_rate'], 2) }}%</td>
                                                                <td class="text-end">{{ format_ringgit_report($channel['net_amount']) }}</td>
                                                                <td>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        @foreach(($channel['source_summary'] ?? []) as $source => $count)
                                                                            <span class="badge {{ gateway_fee_source_badge_class($source) }} border-0">
                                                                                {{ gateway_fee_source_label($source) }}: {{ $count }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-credit-card-off fs-3 d-block mb-2 opacity-50"></i>
                            No gateway fee data available for this period.
                        </div>
                    @endif
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
                                <td class="ps-4"><strong>Cash Receipts from Customers (gross, net of sales discounts)</strong></td>
                                <td class="text-end fw-bold">{{ format_ringgit_report($cashFlow['operating_activities']['cash_receipts']['from_customers']) }}</td>
                            </tr>
                            @foreach($cashFlow['operating_activities']['cash_receipts']['by_payment_method'] as $method => $data)
                            <tr>
                                <td class="ps-5 text-muted small">via {{ payment_method_label($method) }} ({{ $data['count'] }} transactions)</td>
                                <td class="text-end text-muted small">Gross {{ format_ringgit_report($data['gross_amount']) }}, Net {{ format_ringgit_report($data['net_amount']) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="ps-5 text-muted">Net settlement reference after gateway charges</td>
                                <td class="text-end text-muted">{{ format_ringgit_report($cashFlow['operating_activities']['cash_receipts']['net_settlement_reference']) }}</td>
                            </tr>
                            
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
                                <td class="ps-5">Payment Gateway Fees</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['payment_gateway_fees']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-5">
                                    Refunds to Customers
                                    <span class="text-muted small">({{ $cashFlow['operating_activities']['cash_payments']['refund_transactions'] }} transactions)</span>
                                </td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['refunds_to_customers']) }})</td>
                            </tr>
                            <tr>
                                <td class="ps-5">Operating Expenses</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['operating_expenses']) }})</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td class="ps-4">Total Cash Payments</td>
                                <td class="text-end text-danger">({{ format_ringgit_report($cashFlow['operating_activities']['cash_payments']['total_cash_payments']) }})</td>
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
                        <h6 class="fw-bold mb-3"><i class="ti ti-bulb me-1"></i> Cash Flow Analysis</h6>
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
                                <p class="mb-1 small text-muted">Average Gross Customer Receipt</p>
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
                    <h5 class="card-title fw-bold mb-4"><i class="ti ti-users me-1"></i> Report by Resource Owner</h5>
                    
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
                                    <th class="text-end">Owner Revenue</th>
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
                                    <th class="text-end">Owner Revenue</th>
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
                                    <th>Culinary Name</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Participants</th>
                                    <th class="text-end">Owner Revenue</th>
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
                                    <th class="text-end">Owner Revenue</th>
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
