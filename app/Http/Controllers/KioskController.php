<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\StoreKioskRequest;
use App\Http\Requests\Kiosk\UpdateKioskRequest;
use App\Models\Kiosk;
use App\Services\KioskService;

class KioskController extends Controller
{
    public function __construct(private readonly KioskService $kioskService)
    {
    }

    public function index()
    {
        $kiosks = $this->kioskService->paginateWithFotos();

        return view('admin.kiosk.index', compact('kiosks'));
    }

    public function create()
    {
        return view('admin.kiosk.create');
    }

    public function store(StoreKioskRequest $request)
    {
        try {
            $this->kioskService->create(
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kiosk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Kiosk $kiosk)
    {
        $kiosk->load('fotos');

        return view('admin.kiosk.show', compact('kiosk'));
    }

    public function edit(Kiosk $kiosk)
    {
        $kiosk->load('fotos');

        return view('admin.kiosk.edit', compact('kiosk'));
    }

    public function update(UpdateKioskRequest $request, Kiosk $kiosk)
    {
        try {
            $this->kioskService->update(
                $kiosk,
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil diupdate!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate kiosk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Kiosk $kiosk)
    {
        try {
            $this->kioskService->delete($kiosk);

            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil dihapus!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus kiosk: ' . $e->getMessage());
        }
    }
}
