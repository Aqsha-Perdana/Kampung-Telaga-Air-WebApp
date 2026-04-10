<?php

namespace App\Exports;

use App\Services\Sales\SalesQueryService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesRecordExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    private array $filters;
    private SalesQueryService $salesQueryService;

    public function __construct(array $filters, ?SalesQueryService $salesQueryService = null)
    {
        $this->filters = $filters;
        $this->salesQueryService = $salesQueryService ?? app(SalesQueryService::class);
    }

    public function collection()
    {
        $payload = $this->salesQueryService->getDashboardData($this->filters);

        return collect($payload['recentTransactions'] ?? [])
            ->map(function ($order) {
                return [
                    'order_id' => $order->id_order,
                    'created_at' => Carbon::parse($order->created_at)->format('d M Y H:i'),
                    'customer_name' => $order->customer_name,
                    'package_names' => $order->package_names,
                    'payment_method' => payment_method_label($order->payment_method),
                    'payment_channel' => payment_channel_label($order->payment_channel),
                    'payment_descriptor' => payment_descriptor($order->payment_method, $order->payment_channel),
                    'amount_myr' => number_format((float) ($order->base_amount ?? 0), 2, '.', ''),
                    'gateway_fee_myr' => number_format((float) ($order->gateway_fee_amount ?? 0), 2, '.', ''),
                    'gateway_fee_source' => gateway_fee_source_label($order->gateway_fee_source ?? null),
                    'net_settlement_myr' => number_format((float) ($order->gateway_net_amount ?? 0), 2, '.', ''),
                    'gross_profit_before_fee_myr' => number_format((float) ($order->original_profit ?? 0), 2, '.', ''),
                    'net_profit_after_fee_myr' => number_format((float) ($order->company_profit ?? 0), 2, '.', ''),
                    'status' => strtoupper(str_replace('_', ' ', (string) ($order->status ?? '-'))),
                    'display_currency' => $order->display_currency ?: 'MYR',
                    'display_amount' => number_format((float) ($order->display_amount ?? $order->base_amount ?? 0), 2, '.', ''),
                ];
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Created At',
            'Customer',
            'Package',
            'Payment Method',
            'Payment Channel',
            'Payment Descriptor',
            'Amount (MYR)',
            'Gateway Fee (MYR)',
            'Gateway Fee Source',
            'Net Settlement (MYR)',
            'Gross Profit Before Fee (MYR)',
            'Net Profit After Fee (MYR)',
            'Status',
            'Display Currency',
            'Display Amount',
        ];
    }

    public function title(): string
    {
        return 'Sales Record';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'EAF2FF'],
                ],
            ],
        ];
    }
}
