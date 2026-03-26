<?php

namespace App\Http\Controllers;

use App\Http\Requests\BebanOperasional\StoreBebanOperasionalRequest;
use App\Http\Requests\BebanOperasional\UpdateBebanOperasionalRequest;
use App\Models\BebanOperasional;
use App\Services\BebanOperasionalService;
use Illuminate\Http\Request;

class BebanOperasionalController extends Controller
{
    public function __construct(private readonly BebanOperasionalService $bebanOperasionalService)
    {
    }

    public function index(Request $request)
    {
        return view('admin.beban-operasional.index', $this->bebanOperasionalService->getIndexData(
            $request->only(['kategori', 'tanggal_mulai', 'tanggal_akhir', 'metode_pembayaran', 'search'])
        ));
    }

    public function create()
    {
        $kategoriList = BebanOperasionalService::KATEGORI_LIST;
        $metodePembayaran = BebanOperasionalService::METODE_PEMBAYARAN;

        return view('admin.beban-operasional.create', compact('kategoriList', 'metodePembayaran'));
    }

    public function store(StoreBebanOperasionalRequest $request)
    {
        $this->bebanOperasionalService->create($request->validated(), $request->file('bukti_pembayaran'));

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully recorded');
    }

    public function show(BebanOperasional $bebanOperasional)
    {
        return view('admin.beban-operasional.show', compact('bebanOperasional'));
    }

    public function edit(BebanOperasional $bebanOperasional)
    {
        $kategoriList = BebanOperasionalService::KATEGORI_LIST;
        $metodePembayaran = BebanOperasionalService::METODE_PEMBAYARAN;

        return view('admin.beban-operasional.edit', compact('bebanOperasional', 'kategoriList', 'metodePembayaran'));
    }

    public function update(UpdateBebanOperasionalRequest $request, BebanOperasional $bebanOperasional)
    {
        $this->bebanOperasionalService->update(
            $bebanOperasional,
            $request->validated(),
            $request->file('bukti_pembayaran')
        );

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully updated');
    }

    public function destroy(BebanOperasional $bebanOperasional)
    {
        $this->bebanOperasionalService->delete($bebanOperasional);

        return redirect()->route('beban-operasional.index')
            ->with('success', 'Operating expense has been successfully deleted');
    }
}
