<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class OwnerReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $type;
    protected $id;
    protected $startDate;
    protected $endDate;
    protected $report;

    public function __construct($type, $id, $startDate, $endDate)
    {
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->report = $this->getOwnerReport();
    }

    private function getOwnerReport()
    {
        switch ($this->type) {
            case 'boat':
                return $this->getBoatOwnerReport();
            case 'homestay':
                return $this->getHomestayOwnerReport();
            case 'culinary':
                return $this->getCulinaryOwnerReport();
            case 'kiosk':
                return $this->getKioskOwnerReport();
            default:
                return null;
        }
    }

    private function getBoatOwnerReport()
    {
        $boat = DB::table('boats')->where('id_boat', $this->id)->first();
        if (!$boat) return null;

        $transactions = DB::table('paket_wisata_boat')
            ->join('order_items', 'paket_wisata_boat.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_boat.id_boat', $boat->id)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return [
            'owner_info' => [
                'id' => $boat->id_boat,
                'name' => $boat->nama,
                'type' => 'Boat',
                'price_per_unit' => $boat->harga_sewa,
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $boat->harga_sewa
            ],
            'transactions' => $transactions
        ];
    }

    private function getHomestayOwnerReport()
    {
        $homestay = DB::table('homestays')->where('id_homestay', $this->id)->first();
        if (!$homestay) return null;

        $transactions = DB::table('paket_wisata_homestay')
            ->join('order_items', 'paket_wisata_homestay.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_homestay.id_homestay', $homestay->id_homestay)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta',
                'paket_wisata_homestay.jumlah_malam'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $totalNights = $transactions->sum('jumlah_malam');

        return [
            'owner_info' => [
                'id' => $homestay->id_homestay,
                'name' => $homestay->nama,
                'type' => 'Homestay',
                'price_per_unit' => $homestay->harga_per_malam,
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

    private function getCulinaryOwnerReport()
    {
        $culinary = DB::table('paket_culinaries')
            ->join('culinaries', 'paket_culinaries.id_culinary', '=', 'culinaries.id_culinary')
            ->where('paket_culinaries.id_culinary', $this->id)
            ->select('paket_culinaries.*', 'culinaries.nama as culinary_name')
            ->first();
        
        if (!$culinary) return null;

        $transactions = DB::table('paket_wisata_culinary')
            ->join('order_items', 'paket_wisata_culinary.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_culinary.id_paket_culinary', $culinary->id)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return [
            'owner_info' => [
                'id' => $culinary->id_culinary,
                'name' => $culinary->culinary_name . ' - ' . $culinary->nama_paket,
                'type' => 'Culinary',
                'price_per_unit' => $culinary->harga,
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $culinary->harga
            ],
            'transactions' => $transactions
        ];
    }

    private function getKioskOwnerReport()
    {
        $kiosk = DB::table('kiosks')->where('id_kiosk', $this->id)->first();
        if (!$kiosk) return null;

        $transactions = DB::table('paket_wisata_kiosk')
            ->join('order_items', 'paket_wisata_kiosk.id_paket', '=', 'order_items.id_paket')
            ->join('orders', 'order_items.id_order', '=', 'orders.id_order')
            ->where('paket_wisata_kiosk.id_kiosk', $kiosk->id_kiosk)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.created_at',
                'order_items.nama_paket',
                'order_items.tanggal_keberangkatan',
                'order_items.jumlah_peserta'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return [
            'owner_info' => [
                'id' => $kiosk->id_kiosk,
                'name' => $kiosk->nama,
                'type' => 'Kiosk',
                'price_per_unit' => $kiosk->harga_per_paket,
            ],
            'summary' => [
                'usage_count' => $transactions->count(),
                'total_participants' => $transactions->sum('jumlah_peserta'),
                'total_revenue' => $transactions->count() * $kiosk->harga_per_paket
            ],
            'transactions' => $transactions
        ];
    }

    public function collection()
    {
        if (!$this->report) {
            return collect([]);
        }

        $data = collect();

        // Add header info
        $data->push(['OWNER REPORT - ' . strtoupper($this->type)]);
        $data->push(['Kampung Telaga Air']);
        $data->push(['']);
        $data->push(['Owner Information']);
        $data->push(['ID', $this->report['owner_info']['id']]);
        $data->push(['Name', $this->report['owner_info']['name']]);
        $data->push(['Type', $this->report['owner_info']['type']]);
        $data->push(['Price per Unit', 'RM ' . number_format($this->report['owner_info']['price_per_unit'], 2)]);
        $data->push(['']);
        $data->push(['Report Period', Carbon::parse($this->startDate)->format('d F Y') . ' - ' . Carbon::parse($this->endDate)->format('d F Y')]);
        $data->push(['']);

        // Add summary
        $data->push(['SUMMARY']);
        $data->push(['Total Usage', $this->report['summary']['usage_count']]);
        if (isset($this->report['summary']['total_nights'])) {
            $data->push(['Total Nights', $this->report['summary']['total_nights']]);
        }
        $data->push(['Total Participants', $this->report['summary']['total_participants']]);
        $data->push(['Total Revenue', 'RM ' . number_format($this->report['summary']['total_revenue'], 2)]);
        $data->push(['']);
        $data->push(['']);

        // Add transactions
        foreach ($this->report['transactions'] as $transaction) {
            $row = [
                $transaction->id_order,
                $transaction->customer_name,
                $transaction->nama_paket,
                Carbon::parse($transaction->tanggal_keberangkatan)->format('d M Y'),
                $transaction->jumlah_peserta,
            ];

            if ($this->type == 'homestay' && isset($transaction->jumlah_malam)) {
                $row[] = $transaction->jumlah_malam;
                $row[] = 'RM ' . number_format($transaction->jumlah_malam * $this->report['owner_info']['price_per_unit'], 2);
            } else {
                $row[] = 'RM ' . number_format($this->report['owner_info']['price_per_unit'], 2);
            }

            $data->push($row);
        }

        // Add total row
        if ($this->report['transactions']->count() > 0) {
            $emptyColumns = $this->type == 'homestay' ? 6 : 5;
            $totalRow = array_fill(0, $emptyColumns, '');
            $totalRow[] = 'TOTAL: RM ' . number_format($this->report['summary']['total_revenue'], 2);
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

        if ($this->type == 'homestay') {
            $headings[] = 'Nights';
        }

        $headings[] = 'Revenue';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
            // Transaction headings row (dynamic based on data)
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
            'F' => 12,
            'G' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Add borders to transaction table
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A18:G' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Bold last row (total)
                $event->sheet->getStyle('A' . $lastRow . ':G' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE699']],
                ]);
            },
        ];
    }
}