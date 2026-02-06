<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialReportController extends Controller
{
    /**
     * Main Financial Reports Dashboard
     * Compliant with Malaysian Financial Reporting Standards (MFRS)
     */
    public function index(Request $request)
    {
        // Date Range Filter
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        // Get all data compliant with MFRS
        $profitLoss = $this->getStatementOfProfitOrLoss($startDate, $endDate);
        $cashFlow = $this->getStatementOfCashFlows($startDate, $endDate);
        $ownerSummary = $this->getOwnerSummary($startDate, $endDate);
        
        return view('admin.financial-reports.index', compact(
            'profitLoss',
            'cashFlow',
            'ownerSummary',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * STATEMENT OF PROFIT OR LOSS AND OTHER COMPREHENSIVE INCOME
     * Compliant with MFRS 101: Presentation of Financial Statements
     * 
     * Structure follows Malaysian GAAP:
     * Revenue
     * - Cost of Sales
     * = Gross Profit
     * - Operating Expenses
     * = Operating Profit
     * +/- Other Income/Expenses
     * = Profit Before Tax
     * - Tax
     * = Profit After Tax
     */
    private function getStatementOfProfitOrLoss($startDate, $endDate)
    {
        // REVENUE - Only from paid/confirmed orders (MFRS 15)
        // **UPDATED: Use base_amount (MYR) for all calculations**
        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Revenue from tour packages (ALWAYS in MYR)
        $revenue = $orders->sum('base_amount'); // ✅ UPDATED: Use base_amount
        
        // COST OF SALES (Direct costs to deliver services)
        $costOfSales = 0;
        $revenueBreakdown = [];
        
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $costOfSales += $orderCost['total'];
            
            $revenueBreakdown[] = [
                'order_id' => $order->id_order,
                'customer' => $order->customer_name,
                'date' => $order->created_at,
                'revenue' => $order->base_amount, // ✅ UPDATED
                'currency_info' => [
                    'payment_currency' => 'MYR',
                    'display_currency' => $order->display_currency,
                    'display_amount' => $order->display_amount,
                ],
                'cost_of_sales' => $orderCost['total'],
                'gross_profit' => $order->base_amount - $orderCost['total'], // ✅ UPDATED
                'cost_breakdown' => $orderCost['breakdown']
            ];
        }
        
        // GROSS PROFIT
        $grossProfit = $revenue - $costOfSales;
        
        // OPERATING EXPENSES (Indirect costs - MFRS Framework)
        $operatingExpenses = DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();
        
        $expensesByNature = $operatingExpenses->groupBy('kategori')->map(function($items) {
            return [
                'count' => $items->count(),
                'amount' => $items->sum('jumlah')
            ];
        })->toArray();
        
        $totalOperatingExpenses = $operatingExpenses->sum('jumlah');
        
        // OPERATING PROFIT (EBIT - Earnings Before Interest and Tax)
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        
        // OTHER INCOME (if any - for future use)
        $otherIncome = 0;
        
        // OTHER EXPENSES (if any - for future use)
        $otherExpenses = 0;
        
        // PROFIT BEFORE TAX (PBT)
        $profitBeforeTax = $operatingProfit + $otherIncome - $otherExpenses;
        
        // TAX EXPENSE (Malaysian corporate tax rate ~24%)
        // Note: For actual implementation, should be based on actual tax calculation
        $taxRate = 0; // Set to 0 for now, can be configured
        $taxExpense = $profitBeforeTax * $taxRate;
        
        // PROFIT FOR THE PERIOD (Net Income)
        $profitForPeriod = $profitBeforeTax - $taxExpense;
        
        // FINANCIAL RATIOS (Key Performance Indicators)
        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $operatingProfitMargin = $revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0;
        $netProfitMargin = $revenue > 0 ? ($profitForPeriod / $revenue) * 100 : 0;
        
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'currency' => 'MYR', // ✅ All amounts in MYR
            ],
            
            // REVENUE SECTION
            'revenue' => [
                'tour_package_sales' => $revenue,
                'other_revenue' => 0, // For future use
                'total_revenue' => $revenue,
            ],
            
            // COST OF SALES SECTION
            'cost_of_sales' => [
                'boat_services' => $this->getCostByType($orders, 'boats'),
                'accommodation_services' => $this->getCostByType($orders, 'homestays'),
                'culinary_services' => $this->getCostByType($orders, 'culinary'),
                'kiosk_services' => $this->getCostByType($orders, 'kiosks'),
                'total_cost_of_sales' => $costOfSales,
            ],
            
            // GROSS PROFIT
            'gross_profit' => [
                'amount' => $grossProfit,
                'margin_percentage' => $grossProfitMargin,
            ],
            
            // OPERATING EXPENSES (by nature - MFRS compliant)
            'operating_expenses' => [
                'by_nature' => $expensesByNature,
                'total_operating_expenses' => $totalOperatingExpenses,
            ],
            
            // OPERATING PROFIT
            'operating_profit' => [
                'amount' => $operatingProfit,
                'margin_percentage' => $operatingProfitMargin,
            ],
            
            // OTHER ITEMS
            'other_items' => [
                'other_income' => $otherIncome,
                'other_expenses' => $otherExpenses,
                'net_other_items' => $otherIncome - $otherExpenses,
            ],
            
            // PROFIT BEFORE TAX
            'profit_before_tax' => [
                'amount' => $profitBeforeTax,
            ],
            
            // TAX EXPENSE
            'tax_expense' => [
                'current_tax' => $taxExpense,
                'deferred_tax' => 0, // For future use
                'total_tax' => $taxExpense,
                'effective_tax_rate' => $taxRate * 100,
            ],
            
            // PROFIT FOR THE PERIOD
            'profit_for_period' => [
                'amount' => $profitForPeriod,
                'margin_percentage' => $netProfitMargin,
            ],
            
            // ADDITIONAL INFO
            'transactions' => [
                'total_orders' => $orders->count(),
                'total_customers' => $orders->unique('customer_email')->count(),
            ],
            
            // DETAILED BREAKDOWN
            'revenue_breakdown' => $revenueBreakdown,
        ];
    }
    
    /**
     * STATEMENT OF CASH FLOWS
     * Compliant with MFRS 107: Statement of Cash Flows
     * 
     * Three categories:
     * 1. Operating Activities
     * 2. Investing Activities
     * 3. Financing Activities
     */
    private function getStatementOfCashFlows($startDate, $endDate)
    {
        // CASH FLOWS FROM OPERATING ACTIVITIES
        
        // Cash receipts from customers (ALWAYS in MYR)
        $cashFromCustomers = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('base_amount'); // ✅ UPDATED: Use base_amount
        
        $paymentMethodBreakdown = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(base_amount) as total_amount') // ✅ UPDATED
            )
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->payment_method => [
                    'count' => $item->transaction_count,
                    'amount' => $item->total_amount
                ]];
            })
            ->toArray();
        
        // Cash paid to suppliers and employees
        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->get();
        
        $cashToSuppliers = 0;
        $supplierBreakdown = [
            'boat_owners' => 0,
            'homestay_owners' => 0,
            'culinary_providers' => 0,
            'kiosk_owners' => 0,
        ];
        
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $cashToSuppliers += $orderCost['total'];
            
            $supplierBreakdown['boat_owners'] += $orderCost['breakdown']['boats']['total'];
            $supplierBreakdown['homestay_owners'] += $orderCost['breakdown']['homestays']['total'];
            $supplierBreakdown['culinary_providers'] += $orderCost['breakdown']['culinary']['total'];
            $supplierBreakdown['kiosk_owners'] += $orderCost['breakdown']['kiosks']['total'];
        }
        
        // Cash paid for operating expenses
        $cashForOperatingExpenses = DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->sum('jumlah');
        
        $operatingExpensesByCategory = DB::table('beban_operasionals')
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('kategori')
            ->get()
            ->pluck('total', 'kategori')
            ->toArray();
        
        // Net cash from operating activities
        $netCashFromOperating = $cashFromCustomers - $cashToSuppliers - $cashForOperatingExpenses;
        
        // CASH FLOWS FROM INVESTING ACTIVITIES
        // (For future: purchase of property, equipment, etc.)
        $cashForInvestments = 0;
        $cashFromInvestments = 0;
        $netCashFromInvesting = $cashFromInvestments - $cashForInvestments;
        
        // CASH FLOWS FROM FINANCING ACTIVITIES
        // (For future: loans, equity, dividends, etc.)
        $cashFromFinancing = 0;
        $cashForFinancing = 0;
        $netCashFromFinancing = $cashFromFinancing - $cashForFinancing;
        
        // NET INCREASE/DECREASE IN CASH
        $netCashMovement = $netCashFromOperating + $netCashFromInvesting + $netCashFromFinancing;
        
        // CASH BALANCE (Note: Requires cash_balance table for accurate tracking)
        $openingCash = 0; // Should be from previous period
        $closingCash = $openingCash + $netCashMovement;
        
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'currency' => 'MYR', // ✅ All amounts in MYR
            ],
            
            // OPERATING ACTIVITIES
            'operating_activities' => [
                'cash_receipts' => [
                    'from_customers' => $cashFromCustomers,
                    'by_payment_method' => $paymentMethodBreakdown,
                ],
                'cash_payments' => [
                    'to_suppliers' => $cashToSuppliers,
                    'supplier_breakdown' => $supplierBreakdown,
                    'operating_expenses' => $cashForOperatingExpenses,
                    'expense_breakdown' => $operatingExpensesByCategory,
                ],
                'net_cash_from_operating' => $netCashFromOperating,
            ],
            
            // INVESTING ACTIVITIES
            'investing_activities' => [
                'cash_outflows' => [
                    'purchase_of_assets' => $cashForInvestments,
                ],
                'cash_inflows' => [
                    'sale_of_assets' => $cashFromInvestments,
                ],
                'net_cash_from_investing' => $netCashFromInvesting,
            ],
            
            // FINANCING ACTIVITIES
            'financing_activities' => [
                'cash_inflows' => [
                    'loans_received' => $cashFromFinancing,
                ],
                'cash_outflows' => [
                    'loan_repayments' => $cashForFinancing,
                ],
                'net_cash_from_financing' => $netCashFromFinancing,
            ],
            
            // NET CASH MOVEMENT
            'cash_summary' => [
                'net_cash_from_operating' => $netCashFromOperating,
                'net_cash_from_investing' => $netCashFromInvesting,
                'net_cash_from_financing' => $netCashFromFinancing,
                'net_increase_in_cash' => $netCashMovement,
            ],
            
            // CASH BALANCE RECONCILIATION
            'cash_reconciliation' => [
                'opening_balance' => $openingCash,
                'net_movement' => $netCashMovement,
                'closing_balance' => $closingCash,
            ],
            
            // ADDITIONAL INFO
            'statistics' => [
                'total_transactions' => $orders->count(),
                'average_transaction_value' => $orders->count() > 0 ? $cashFromCustomers / $orders->count() : 0,
            ],
        ];
    }
    
    /**
     * Helper: Get cost by resource type
     */
    private function getCostByType($orders, $type)
    {
        $total = 0;
        
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $total += $orderCost['breakdown'][$type]['total'];
        }
        
        return $total;
    }
    
    /**
     * Calculate cost for a specific order (Updated for Display Currency)
     */
    private function calculateOrderCost($orderId)
    {
        $orderItems = DB::table('order_items')
            ->where('id_order', $orderId)
            ->get();
        
        $totalCost = 0;
        $breakdown = [
            'boats' => ['items' => [], 'total' => 0],
            'homestays' => ['items' => [], 'total' => 0],
            'culinary' => ['items' => [], 'total' => 0],
            'kiosks' => ['items' => [], 'total' => 0]
        ];
        
        foreach ($orderItems as $item) {
            // Get boats
            $boats = DB::table('paket_wisata_boat')
                ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
                ->where('paket_wisata_boat.id_paket', $item->id_paket)
                ->select('boats.*', 'paket_wisata_boat.hari_ke')
                ->get();
            
            foreach ($boats as $boat) {
                $cost = $boat->harga_sewa;
                $totalCost += $cost;
                $breakdown['boats']['items'][] = [
                    'name' => $boat->nama,
                    'unit_price' => $boat->harga_sewa,
                    'quantity' => 1,
                    'total' => $cost
                ];
                $breakdown['boats']['total'] += $cost;
            }
            
            // Get homestays
            $homestays = DB::table('paket_wisata_homestay')
                ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
                ->where('paket_wisata_homestay.id_paket', $item->id_paket)
                ->select('homestays.*', 'paket_wisata_homestay.jumlah_malam')
                ->get();
            
            foreach ($homestays as $homestay) {
                $cost = $homestay->harga_per_malam * $homestay->jumlah_malam;
                $totalCost += $cost;
                $breakdown['homestays']['items'][] = [
                    'name' => $homestay->nama,
                    'unit_price' => $homestay->harga_per_malam,
                    'quantity' => $homestay->jumlah_malam,
                    'total' => $cost
                ];
                $breakdown['homestays']['total'] += $cost;
            }
            
            // Get culinary
            $culinary = DB::table('paket_wisata_culinary')
                ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
                ->where('paket_wisata_culinary.id_paket', $item->id_paket)
                ->select('paket_culinaries.*', 'paket_wisata_culinary.hari_ke')
                ->get();
            
            foreach ($culinary as $cul) {
                $cost = $cul->harga;
                $totalCost += $cost;
                $breakdown['culinary']['items'][] = [
                    'name' => $cul->nama_paket,
                    'unit_price' => $cul->harga,
                    'quantity' => 1,
                    'total' => $cost
                ];
                $breakdown['culinary']['total'] += $cost;
            }
            
            // Get kiosks
            $kiosks = DB::table('paket_wisata_kiosk')
                ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
                ->where('paket_wisata_kiosk.id_paket', $item->id_paket)
                ->select('kiosks.*', 'paket_wisata_kiosk.hari_ke')
                ->get();
            
            foreach ($kiosks as $kiosk) {
                $cost = $kiosk->harga_per_paket;
                $totalCost += $cost;
                $breakdown['kiosks']['items'][] = [
                    'name' => $kiosk->nama,
                    'unit_price' => $kiosk->harga_per_paket,
                    'quantity' => 1,
                    'total' => $cost
                ];
                $breakdown['kiosks']['total'] += $cost;
            }
        }
        
        return [
            'total' => $totalCost,
            'breakdown' => $breakdown
        ];
    }
    
    /**
     * OWNER SUMMARY - All Owners
     * (Same as before, no changes needed)
     */
    private function getOwnerSummary($startDate, $endDate)
    {
        return [
            'boats' => $this->getBoatOwnersSummary($startDate, $endDate),
            'homestays' => $this->getHomestayOwnersSummary($startDate, $endDate),
            'culinary' => $this->getCulinaryOwnersSummary($startDate, $endDate),
            'kiosks' => $this->getKioskOwnersSummary($startDate, $endDate)
        ];
    }
    
    // ... (Other methods remain the same: getBoatOwnersSummary, getHomestayOwnersSummary, etc.)
    // These don't need updates as they calculate in MYR by default
    private function getBoatOwnersSummary($startDate, $endDate)
    {
        $boats = DB::table('boats')->where('is_active', 1)->get();
        $summary = [];
        
        foreach ($boats as $boat) {
            $usage = DB::table('paket_wisata_boat')
                ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->where('paket_wisata_boat.id_boat', $boat->id)
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.tanggal_keberangkatan',
                    'order_items.jumlah_peserta',
                    'orders.customer_name',
                    'orders.created_at',
                    'paket_wisata_boat.hari_ke'
                )
                ->get();
            
            $totalRevenue = $usage->count() * $boat->harga_sewa;
            
            $summary[] = [
                'id' => $boat->id_boat,
                'name' => $boat->nama,
                'type' => 'Boat',
                'price_per_unit' => $boat->harga_sewa,
                'unit_name' => 'day',
                'usage_count' => $usage->count(),
                'total_participants' => $usage->sum('jumlah_peserta'),
                'total_revenue' => $totalRevenue,
                'transactions' => $usage
            ];
        }
        
        return $summary;
    }
    
    /**
     * HOMESTAY OWNERS SUMMARY
     */
    private function getHomestayOwnersSummary($startDate, $endDate)
    {
        $homestays = DB::table('homestays')->where('is_active', 1)->get();
        $summary = [];
        
        foreach ($homestays as $homestay) {
            $usage = DB::table('paket_wisata_homestay')
                ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->where('paket_wisata_homestay.id_homestay', $homestay->id_homestay)
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.tanggal_keberangkatan',
                    'order_items.jumlah_peserta',
                    'orders.customer_name',
                    'orders.created_at',
                    'paket_wisata_homestay.jumlah_malam'
                )
                ->get();
            
            $totalNights = $usage->sum('jumlah_malam');
            $totalRevenue = $totalNights * $homestay->harga_per_malam;
            
            $summary[] = [
                'id' => $homestay->id_homestay,
                'name' => $homestay->nama,
                'type' => 'Homestay',
                'price_per_unit' => $homestay->harga_per_malam,
                'unit_name' => 'night',
                'usage_count' => $usage->count(),
                'total_units' => $totalNights,
                'total_participants' => $usage->sum('jumlah_peserta'),
                'total_revenue' => $totalRevenue,
                'transactions' => $usage
            ];
        }
        
        return $summary;
    }
    
    /**
     * CULINARY OWNERS SUMMARY
     */
    private function getCulinaryOwnersSummary($startDate, $endDate)
    {
        $culinaries = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->select('paket_culinaries.*', 'culinaries.nama as culinary_name')
            ->get();
        
        $summary = [];
        
        foreach ($culinaries as $paket) {
            $usage = DB::table('paket_wisata_culinary')
                ->join('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->where('paket_wisata_culinary.id_paket_culinary', $paket->id)
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.tanggal_keberangkatan',
                    'order_items.jumlah_peserta',
                    'orders.customer_name',
                    'orders.created_at',
                    'paket_wisata_culinary.hari_ke'
                )
                ->get();
            
            $totalRevenue = $usage->count() * $paket->harga;
            
            $summary[] = [
                'id' => $paket->id_culinary,
                'name' => $paket->culinary_name . ' - ' . $paket->nama_paket,
                'type' => 'Culinary',
                'price_per_unit' => $paket->harga,
                'unit_name' => 'package',
                'usage_count' => $usage->count(),
                'total_participants' => $usage->sum('jumlah_peserta'),
                'total_revenue' => $totalRevenue,
                'transactions' => $usage
            ];
        }
        
        return $summary;
    }
    
    /**
     * KIOSK OWNERS SUMMARY
     */
    private function getKioskOwnersSummary($startDate, $endDate)
    {
        $kiosks = DB::table('kiosks')->get();
        $summary = [];
        
        foreach ($kiosks as $kiosk) {
            $usage = DB::table('paket_wisata_kiosk')
                ->join('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
                ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
                ->where('paket_wisata_kiosk.id_kiosk', $kiosk->id_kiosk)
                ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'order_items.id_order',
                    'order_items.nama_paket',
                    'order_items.tanggal_keberangkatan',
                    'order_items.jumlah_peserta',
                    'orders.customer_name',
                    'orders.created_at',
                    'paket_wisata_kiosk.hari_ke'
                )
                ->get();
            
            $totalRevenue = $usage->count() * $kiosk->harga_per_paket;
            
            $summary[] = [
                'id' => $kiosk->id_kiosk,
                'name' => $kiosk->nama,
                'type' => 'Kiosk',
                'price_per_unit' => $kiosk->harga_per_paket,
                'unit_name' => 'package',
                'usage_count' => $usage->count(),
                'total_participants' => $usage->sum('jumlah_peserta'),
                'total_revenue' => $totalRevenue,
                'transactions' => $usage
            ];
        }
        
        return $summary;
    }
    
    /**
     * Calculate cost for a specific order
     */
    public function ownerReport(Request $request, $type, $id)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        $report = null;
        
        switch ($type) {
            case 'boat':
                $report = $this->getBoatOwnerReport($id, $startDate, $endDate);
                break;
            case 'homestay':
                $report = $this->getHomestayOwnerReport($id, $startDate, $endDate);
                break;
            case 'culinary':
                $report = $this->getCulinaryOwnerReport($id, $startDate, $endDate);
                break;
            case 'kiosk':
                $report = $this->getKioskOwnerReport($id, $startDate, $endDate);
                break;
        }
        
        return view('admin.financial-reports.pdf.owner-detail', compact('report', 'type', 'startDate', 'endDate'));
    }

    public function exportLabaRugiPDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        $labaRugi = $this->getLabaRugi($startDate, $endDate);
        
        $pdf = Pdf::loadView('admin.financial-reports.pdf.laba-rugi', [
            'data' => $labaRugi,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Laporan-Laba-Rugi-' . Carbon::parse($startDate)->format('Y-m-d') . '-to-' . Carbon::parse($endDate)->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Export Laporan Arus Kas to PDF
     */
    public function exportArusKasPDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        $arusKas = $this->getArusKas($startDate, $endDate);
        
        $pdf = Pdf::loadView('admin.financial-reports.pdf.arus-kas', [
            'data' => $arusKas,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Laporan-Arus-Kas-' . Carbon::parse($startDate)->format('Y-m-d') . '-to-' . Carbon::parse($endDate)->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Export Laporan Owner to PDF
     */
    public function exportOwnerPDF(Request $request, $type, $id)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        $report = null;
        
        switch ($type) {
            case 'boat':
                $report = $this->getBoatOwnerReport($id, $startDate, $endDate);
                break;
            case 'homestay':
                $report = $this->getHomestayOwnerReport($id, $startDate, $endDate);
                break;
            case 'culinary':
                $report = $this->getCulinaryOwnerReport($id, $startDate, $endDate);
                break;
            case 'kiosk':
                $report = $this->getKioskOwnerReport($id, $startDate, $endDate);
                break;
        }
        
        if (!$report) {
            return redirect()->back()->with('error', 'Owner not found');
        }
        
        $pdf = Pdf::loadView('admin.financial-reports.pdf.owner-report', [
            'report' => $report,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Laporan-Owner-' . strtoupper($type) . '-' . $id . '-' . Carbon::parse($startDate)->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    private function getBoatOwnerReport($boatId, $startDate, $endDate)
    {
        $boat = DB::table('boats')->where('id_boat', $boatId)->first();
        
        if (!$boat) return null;
        
        $transactions = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_boat.id_boat', $boat->id)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'orders.total_amount',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->get();
        
        return [
            'owner_info' => [
                'id' => $boat->id_boat,
                'name' => $boat->nama,
                'type' => 'Boat',
                'capacity' => $boat->kapasitas,
                'price_per_day' => $boat->harga_sewa
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $boat->harga_sewa
            ],
            'transactions' => $transactions
        ];
    }
    
    private function getHomestayOwnerReport($homestayId, $startDate, $endDate)
    {
        $homestay = DB::table('homestays')->where('id_homestay', $homestayId)->first();
        
        if (!$homestay) return null;
        
        $transactions = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_homestay.id_homestay', $homestay->id_homestay)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta',
                'paket_wisata_homestay.jumlah_malam'
            )
            ->get();
        
        $totalNights = $transactions->sum('jumlah_malam');
        
        return [
            'owner_info' => [
                'id' => $homestay->id_homestay,
                'name' => $homestay->nama,
                'type' => 'Homestay',
                'capacity' => $homestay->kapasitas,
                'price_per_night' => $homestay->harga_per_malam
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_nights' => $totalNights,
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $totalNights * $homestay->harga_per_malam
            ],
            'transactions' => $transactions
        ];
    }
    
    private function getCulinaryOwnerReport($culinaryId, $startDate, $endDate)
    {
        $culinary = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->where('paket_culinaries.id_culinary', $culinaryId)
            ->select('paket_culinaries.*', 'culinaries.nama as culinary_name')
            ->first();
        
        if (!$culinary) return null;
        
        $transactions = DB::table('paket_wisata_culinary')
            ->join('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_culinary.id_paket_culinary', $culinary->id)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->get();
        
        return [
            'owner_info' => [
                'id' => $culinary->id_culinary,
                'name' => $culinary->culinary_name . ' - ' . $culinary->nama_paket,
                'type' => 'Culinary Package',
                'capacity' => $culinary->kapasitas,
                'price_per_package' => $culinary->harga
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $culinary->harga
            ],
            'transactions' => $transactions
        ];
    }
    
    private function getKioskOwnerReport($kioskId, $startDate, $endDate)
    {
        $kiosk = DB::table('kiosks')->where('id_kiosk', $kioskId)->first();
        
        if (!$kiosk) return null;
        
        $transactions = DB::table('paket_wisata_kiosk')
            ->join('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_kiosk.id_kiosk', $kiosk->id_kiosk)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->get();
        
        return [
            'owner_info' => [
                'id' => $kiosk->id_kiosk,
                'name' => $kiosk->nama,
                'type' => 'Kiosk',
                'capacity' => $kiosk->kapasitas,
                'price_per_package' => $kiosk->harga_per_paket
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $kiosk->harga_per_paket
            ],
            'transactions' => $transactions
        ];
    }
}