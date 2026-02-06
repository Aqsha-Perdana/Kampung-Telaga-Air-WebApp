<?php

namespace App\Http\Controllers;

use App\Models\BebanOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BebanOperasionalController extends Controller
{
    public function index(Request $request)
    {
        $query = BebanOperasional::query()->orderBy('tanggal', 'desc');

        // Filter by category
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by date range
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        // Filter by payment method
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('kode_transaksi', 'like', '%' . $request->search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_referensi', 'like', '%' . $request->search . '%');
            });
        }

        $bebans = $query->paginate(15)->withQueryString();
        $totalBeban = $query->sum('jumlah');

        // Category summary for current filter
        $kategoriSummary = $query->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->get();

        // Payment method summary
        $metodeSummary = $query->select('metode_pembayaran', DB::raw('SUM(jumlah) as total'))
            ->groupBy('metode_pembayaran')
            ->get();

        $kategoriList = [
            'Salaries and Wages',
            'Electricity',
            'Water',
            'Telephone and Internet',
            'Building Rent',
            'Office Supplies',
            'Transportation',
            'Maintenance and Repairs',
            'Insurance',
            'Taxes and Licenses',
            'Advertising and Marketing',
            'Professional Fees',
            'Depreciation',
            'Bank Charges',
            'Utilities',
            'Others'
        ];

        $metodePembayaran = ['Cash', 'Bank Transfer'];

        return view('admin.beban-operasional.index', compact(
            'bebans', 
            'totalBeban', 
            'kategoriList', 
            'metodePembayaran',
            'kategoriSummary',
            'metodeSummary'
        ));
    }

    public function create()
    {
        $kategoriList = [
            'Salaries and Wages',
            'Electricity',
            'Water',
            'Telephone and Internet',
            'Building Rent',
            'Office Supplies',
            'Transportation',
            'Maintenance and Repairs',
            'Insurance',
            'Taxes and Licenses',
            'Advertising and Marketing',
            'Professional Fees',
            'Depreciation',
            'Bank Charges',
            'Utilities',
            'Others'
        ];

        $metodePembayaran = ['Cash', 'Bank Transfer'];

        return view('admin.beban-operasional.create', compact('kategoriList', 'metodePembayaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
            'nomor_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('bukti_pembayaran')) {
            $validated['bukti_pembayaran'] = $request->file('bukti_pembayaran')
                ->store('bukti-pembayaran', 'public');
        }

        BebanOperasional::create($validated);

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully recorded');
    }

    public function show(BebanOperasional $bebanOperasional)
    {
        return view('admin.beban-operasional.show', compact('bebanOperasional'));
    }

    public function edit(BebanOperasional $bebanOperasional)
    {
        $kategoriList = [
            'Salaries and Wages',
            'Electricity',
            'Water',
            'Telephone and Internet',
            'Building Rent',
            'Office Supplies',
            'Transportation',
            'Maintenance and Repairs',
            'Insurance',
            'Taxes and Licenses',
            'Advertising and Marketing',
            'Professional Fees',
            'Depreciation',
            'Bank Charges',
            'Utilities',
            'Others'
        ];

        $metodePembayaran = ['Cash', 'Bank Transfer'];

        return view('admin.beban-operasional.edit', compact('bebanOperasional', 'kategoriList', 'metodePembayaran'));
    }

    public function update(Request $request, BebanOperasional $bebanOperasional)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
            'nomor_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('bukti_pembayaran')) {
            if ($bebanOperasional->bukti_pembayaran) {
                Storage::disk('public')->delete($bebanOperasional->bukti_pembayaran);
            }
            
            $validated['bukti_pembayaran'] = $request->file('bukti_pembayaran')
                ->store('bukti-pembayaran', 'public');
        }

        $bebanOperasional->update($validated);

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully updated');
    }

    public function destroy(BebanOperasional $bebanOperasional)
    {
        if ($bebanOperasional->bukti_pembayaran) {
            Storage::disk('public')->delete($bebanOperasional->bukti_pembayaran);
        }

        $bebanOperasional->delete();

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully deleted');
    }
}