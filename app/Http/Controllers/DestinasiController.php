<?php

namespace App\Http\Controllers;

use App\Http\Requests\Destinasi\StoreDestinasiRequest;
use App\Http\Requests\Destinasi\UpdateDestinasiRequest;
use App\Models\Destinasi;
use App\Services\DestinasiService;

class DestinasiController extends Controller
{
    public function __construct(private readonly DestinasiService $destinasiService)
    {
    }

    public function index()
    {
        $destinasis = $this->destinasiService->paginateWithFotos();

        return view('admin.destination.index', compact('destinasis'));
    }

    public function create()
    {
        return view('admin.destination.create');
    }

    public function store(StoreDestinasiRequest $request)
    {
        try {
            $this->destinasiService->create(
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan destinasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Destinasi $destinasi)
    {
        $destinasi->load('fotos');

        return view('admin.destination.show', compact('destinasi'));
    }

    public function edit(Destinasi $destinasi)
    {
        $destinasi->load('fotos');

        return view('admin.destination.edit', compact('destinasi'));
    }

    public function update(UpdateDestinasiRequest $request, Destinasi $destinasi)
    {
        try {
            $this->destinasiService->update(
                $destinasi,
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil diupdate!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate destinasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Destinasi $destinasi)
    {
        try {
            $this->destinasiService->delete($destinasi);

            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil dihapus!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus destinasi: ' . $e->getMessage());
        }
    }
}
