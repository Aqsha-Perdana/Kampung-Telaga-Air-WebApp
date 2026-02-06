<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;


class FinancialReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function sheets(): array
    {
        return [
            new ProfitLossSheet($this->startDate, $this->endDate),
            new CashFlowSheet($this->startDate, $this->endDate),
            new RevenueBreakdownSheet($this->startDate, $this->endDate),
        ];
    }
}

/**
 * Statement of Profit or Loss Sheet
 */
class ProfitLossSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function title(): string
    {
        return 'Profit & Loss Statement';
    }
    
    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['STATEMENT OF PROFIT OR LOSS AND OTHER COMPREHENSIVE INCOME'],
            ['For the period from ' . Carbon::parse($this->startDate)->format('d F Y') . ' to ' . Carbon::parse($this->endDate)->format('d F Y')],
            ['All amounts in Malaysian Ringgit (MYR)'],
            [],
            ['Account', 'Amount (MYR)'],
        ];
    }
    
    public function collection()
    {
        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
        
        $revenue = $orders->sum('base_amount');
        
        $costOfSales = 0;
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $costOfSales += $orderCost['total'];
        }
        
        $grossProfit = $revenue - $costOfSales;
        
        $operatingExpenses = DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->sum('jumlah');
        
        $operatingProfit = $grossProfit - $operatingExpenses;
        $profitBeforeTax = $operatingProfit;
        $taxExpense = 0;
        $netProfit = $profitBeforeTax - $taxExpense;
        
        return collect([
            ['REVENUE', ''],
            ['  Tour Package Sales', number_format($revenue, 2)],
            ['  Other Revenue', '0.00'],
            ['Total Revenue', number_format($revenue, 2)],
            ['', ''],
            ['COST OF SALES', ''],
            ['  Boat Services', '(' . number_format($this->getCostByType($orders, 'boats'), 2) . ')'],
            ['  Homestay Services', '(' . number_format($this->getCostByType($orders, 'homestays'), 2) . ')'],
            ['  Culinary Services', '(' . number_format($this->getCostByType($orders, 'culinary'), 2) . ')'],
            ['  Kiosk Services', '(' . number_format($this->getCostByType($orders, 'kiosks'), 2) . ')'],
            ['Total Cost of Sales', '(' . number_format($costOfSales, 2) . ')'],
            ['', ''],
            ['GROSS PROFIT', number_format($grossProfit, 2)],
            ['Gross Profit Margin %', number_format(($revenue > 0 ? ($grossProfit / $revenue) * 100 : 0), 2) . '%'],
            ['', ''],
            ['OPERATING EXPENSES', ''],
            ['  Total Operating Expenses', '(' . number_format($operatingExpenses, 2) . ')'],
            ['', ''],
            ['OPERATING PROFIT (EBIT)', number_format($operatingProfit, 2)],
            ['Operating Profit Margin %', number_format(($revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0), 2) . '%'],
            ['', ''],
            ['PROFIT BEFORE TAX', number_format($profitBeforeTax, 2)],
            ['', ''],
            ['TAX EXPENSE', '(' . number_format($taxExpense, 2) . ')'],
            ['', ''],
            ['NET PROFIT FOR THE PERIOD', number_format($netProfit, 2)],
            ['Net Profit Margin %', number_format(($revenue > 0 ? ($netProfit / $revenue) * 100 : 0), 2) . '%'],
        ]);
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            7 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
            18 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4EDDA']]],
            22 => ['font' => ['bold' => true]],
            25 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CCE5FF']]],
            29 => ['font' => ['bold' => true]],
            32 => ['font' => ['bold' => true]],
            35 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C3E6CB']]],
        ];
    }
    
    private function calculateOrderCost($orderId)
    {
        // Same logic as in controller
        $orderItems = DB::table('order_items')->where('id_order', $orderId)->get();
        $totalCost = 0;
        $breakdown = ['boats' => 0, 'homestays' => 0, 'culinary' => 0, 'kiosks' => 0];
        
        foreach ($orderItems as $item) {
            $boats = DB::table('paket_wisata_boat')
                ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
                ->where('paket_wisata_boat.id_paket', $item->id_paket)
                ->get();
            foreach ($boats as $boat) {
                $totalCost += $boat->harga_sewa;
                $breakdown['boats'] += $boat->harga_sewa;
            }
            
            $homestays = DB::table('paket_wisata_homestay')
                ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
                ->where('paket_wisata_homestay.id_paket', $item->id_paket)
                ->get();
            foreach ($homestays as $homestay) {
                $cost = $homestay->harga_per_malam * $homestay->jumlah_malam;
                $totalCost += $cost;
                $breakdown['homestays'] += $cost;
            }
            
            $culinary = DB::table('paket_wisata_culinary')
                ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
                ->where('paket_wisata_culinary.id_paket', $item->id_paket)
                ->get();
            foreach ($culinary as $cul) {
                $totalCost += $cul->harga;
                $breakdown['culinary'] += $cul->harga;
            }
            
            $kiosks = DB::table('paket_wisata_kiosk')
                ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
                ->where('paket_wisata_kiosk.id_paket', $item->id_paket)
                ->get();
            foreach ($kiosks as $kiosk) {
                $totalCost += $kiosk->harga_per_paket;
                $breakdown['kiosks'] += $kiosk->harga_per_paket;
            }
        }
        
        return ['total' => $totalCost, 'breakdown' => $breakdown];
    }
    
    private function getCostByType($orders, $type)
    {
        $total = 0;
        foreach ($orders as $order) {
            $cost = $this->calculateOrderCost($order->id_order);
            $total += $cost['breakdown'][$type];
        }
        return $total;
    }
}

/**
 * Statement of Cash Flows Sheet
 */
class CashFlowSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function title(): string
    {
        return 'Cash Flow Statement';
    }
    
    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['STATEMENT OF CASH FLOWS'],
            ['For the period from ' . Carbon::parse($this->startDate)->format('d F Y') . ' to ' . Carbon::parse($this->endDate)->format('d F Y')],
            ['All amounts in Malaysian Ringgit (MYR)'],
            [],
            ['Account', 'Amount (MYR)'],
        ];
    }
    
    public function collection()
    {
        $cashFromCustomers = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->sum('base_amount');
        
        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->get();
        
        $cashToSuppliers = 0;
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $cashToSuppliers += $orderCost['total'];
        }
        
        $operatingExpenses = DB::table('beban_operasionals')
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->sum('jumlah');
        
        $netCashOperating = $cashFromCustomers - $cashToSuppliers - $operatingExpenses;
        $netCashInvesting = 0;
        $netCashFinancing = 0;
        $netCashMovement = $netCashOperating + $netCashInvesting + $netCashFinancing;
        
        return collect([
            ['CASH FLOWS FROM OPERATING ACTIVITIES', ''],
            ['  Cash Receipts from Customers', number_format($cashFromCustomers, 2)],
            ['  Cash Payments to Suppliers', '(' . number_format($cashToSuppliers, 2) . ')'],
            ['  Cash Payments for Operating Expenses', '(' . number_format($operatingExpenses, 2) . ')'],
            ['Net Cash from Operating Activities', number_format($netCashOperating, 2)],
            ['', ''],
            ['CASH FLOWS FROM INVESTING ACTIVITIES', ''],
            ['  Purchase of Assets', '0.00'],
            ['  Sale of Assets', '0.00'],
            ['Net Cash from Investing Activities', number_format($netCashInvesting, 2)],
            ['', ''],
            ['CASH FLOWS FROM FINANCING ACTIVITIES', ''],
            ['  Proceeds from Borrowings', '0.00'],
            ['  Repayment of Borrowings', '0.00'],
            ['Net Cash from Financing Activities', number_format($netCashFinancing, 2)],
            ['', ''],
            ['NET INCREASE/(DECREASE) IN CASH', number_format($netCashMovement, 2)],
            ['Cash at Beginning of Period', '0.00'],
            ['CASH AT END OF PERIOD', number_format($netCashMovement, 2)],
        ]);
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            7 => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4EDDA']]],
            13 => ['font' => ['bold' => true]],
            17 => ['font' => ['bold' => true]],
            21 => ['font' => ['bold' => true]],
            23 => ['font' => ['bold' => true, 'size' => 11]],
            25 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C3E6CB']]],
        ];
    }
    
    private function calculateOrderCost($orderId)
    {
        $orderItems = DB::table('order_items')->where('id_order', $orderId)->get();
        $totalCost = 0;
        
        foreach ($orderItems as $item) {
            $boats = DB::table('paket_wisata_boat')
                ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
                ->where('paket_wisata_boat.id_paket', $item->id_paket)
                ->sum('boats.harga_sewa');
            $totalCost += $boats;
            
            $homestays = DB::table('paket_wisata_homestay')
                ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
                ->where('paket_wisata_homestay.id_paket', $item->id_paket)
                ->get();
            foreach ($homestays as $homestay) {
                $totalCost += $homestay->harga_per_malam * $homestay->jumlah_malam;
            }
            
            $culinary = DB::table('paket_wisata_culinary')
                ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
                ->where('paket_wisata_culinary.id_paket', $item->id_paket)
                ->sum('paket_culinaries.harga');
            $totalCost += $culinary;
            
            $kiosks = DB::table('paket_wisata_kiosk')
                ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
                ->where('paket_wisata_kiosk.id_paket', $item->id_paket)
                ->sum('kiosks.harga_per_paket');
            $totalCost += $kiosks;
        }
        
        return ['total' => $totalCost];
    }
}

/**
 * Revenue Breakdown Sheet
 */
class RevenueBreakdownSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function title(): string
    {
        return 'Revenue Breakdown';
    }
    
    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['DETAILED REVENUE BREAKDOWN'],
            ['For the period from ' . Carbon::parse($this->startDate)->format('d F Y') . ' to ' . Carbon::parse($this->endDate)->format('d F Y')],
            [],
            ['Order ID', 'Customer', 'Date', 'Revenue (MYR)', 'Cost of Sales (MYR)', 'Gross Profit (MYR)', 'Display Currency', 'Display Amount'],
        ];
    }
    
    public function collection()
    {
        $orders = DB::table('orders')
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
        
        $data = [];
        foreach ($orders as $order) {
            $orderCost = $this->calculateOrderCost($order->id_order);
            $data[] = [
                $order->id_order,
                $order->customer_name,
                Carbon::parse($order->created_at)->format('d M Y'),
                number_format($order->base_amount, 2),
                number_format($orderCost['total'], 2),
                number_format($order->base_amount - $orderCost['total'], 2),
                $order->display_currency ?? 'MYR',
                number_format($order->display_amount ?? $order->base_amount, 2),
            ];
        }
        
        return collect($data);
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }
    
    private function calculateOrderCost($orderId)
    {
        $orderItems = DB::table('order_items')->where('id_order', $orderId)->get();
        $totalCost = 0;
        
        foreach ($orderItems as $item) {
            $boats = DB::table('paket_wisata_boat')
                ->join('boats', 'paket_wisata_boat.id_boat', '=', 'boats.id')
                ->where('paket_wisata_boat.id_paket', $item->id_paket)
                ->sum('boats.harga_sewa');
            $totalCost += $boats;
            
            $homestays = DB::table('paket_wisata_homestay')
                ->join('homestays', 'paket_wisata_homestay.id_homestay', '=', 'homestays.id_homestay')
                ->where('paket_wisata_homestay.id_paket', $item->id_paket)
                ->get();
            foreach ($homestays as $homestay) {
                $totalCost += $homestay->harga_per_malam * $homestay->jumlah_malam;
            }
            
            $culinary = DB::table('paket_wisata_culinary')
                ->join('paket_culinaries', 'paket_wisata_culinary.id_paket_culinary', '=', 'paket_culinaries.id')
                ->where('paket_wisata_culinary.id_paket', $item->id_paket)
                ->sum('paket_culinaries.harga');
            $totalCost += $culinary;
            
            $kiosks = DB::table('paket_wisata_kiosk')
                ->join('kiosks', 'paket_wisata_kiosk.id_kiosk', '=', 'kiosks.id_kiosk')
                ->where('paket_wisata_kiosk.id_paket', $item->id_paket)
                ->sum('kiosks.harga_per_paket');
            $totalCost += $kiosks;
        }
        
        return ['total' => $totalCost];
    }
}