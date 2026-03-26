<?php

namespace App\Exports;

use App\Services\FinancialReport\OwnerReportService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OwnerReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected string $type;
    protected string $id;
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?array $report;

    public function __construct($type, $id, $startDate, $endDate, ?OwnerReportService $ownerReportService = null)
    {
        $this->type = (string) $type;
        $this->id = (string) $id;
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();

        $service = $ownerReportService ?? app(OwnerReportService::class);
        $this->report = $service->getOwnerDetail($this->type, $this->id, $this->startDate, $this->endDate);
    }

    public function collection()
    {
        if (!$this->report) {
            return collect([]);
        }

        $data = collect();

        $data->push(['OWNER REPORT - ' . strtoupper($this->type)]);
        $data->push(['Kampung Telaga Air']);
        $data->push(['']);
        $data->push(['Owner Information']);
        $data->push(['ID', $this->report['id']]);
        $data->push(['Name', $this->report['name']]);
        $data->push(['Type', $this->report['type']]);
        $data->push(['Price per Unit', 'RM ' . number_format((float) $this->report['price_per_unit'], 2)]);
        $data->push(['']);
        $data->push(['Report Period', $this->startDate->format('d F Y') . ' - ' . $this->endDate->format('d F Y')]);
        $data->push(['']);
        $data->push(['SUMMARY']);
        $data->push(['Total Usage', $this->report['usage_count']]);

        if (isset($this->report['total_units'])) {
            $data->push(['Total ' . ucfirst($this->report['unit_name']) . 's', $this->report['total_units']]);
        }

        $data->push(['Total Participants', $this->report['total_participants']]);
        $data->push(['Owner Revenue', 'RM ' . number_format((float) $this->report['total_revenue'], 2)]);
        $data->push(['']);
        $data->push(['']);

        foreach ($this->report['transactions'] as $transaction) {
            $row = [
                $transaction->id_order,
                $transaction->customer_name,
                $transaction->nama_paket,
                Carbon::parse($transaction->tanggal_keberangkatan)->format('d M Y'),
                $transaction->jumlah_peserta,
            ];

            if (isset($this->report['total_units'])) {
                $row[] = $transaction->jumlah_malam ?? 0;
            }

            $row[] = 'RM ' . number_format((float) ($transaction->resource_revenue ?? 0), 2);
            $data->push($row);
        }

        if ($this->report['transactions']->count() > 0) {
            $emptyColumns = isset($this->report['total_units']) ? 6 : 5;
            $totalRow = array_fill(0, $emptyColumns, '');
            $totalRow[] = 'TOTAL OWNER REVENUE: RM ' . number_format((float) $this->report['total_revenue'], 2);
            $data->push($totalRow);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = [
            'Order ID',
            'Customer',
            'Package',
            'Departure Date',
            'Participants',
        ];

        if ($this->report && isset($this->report['total_units'])) {
            $headings[] = ucfirst($this->report['unit_name']) . 's';
        }

        $headings[] = 'Owner Revenue';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
            18 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4F8']]],
        ];
    }

    public function title(): string
    {
        return 'Owner Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 30,
            'D' => 15,
            'E' => 12,
            'F' => 15,
            'G' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:G' . $highestRow)->getAlignment()->setVertical('center');
                $sheet->getStyle('A18:G18')->getFont()->setBold(true);
            },
        ];
    }
}
