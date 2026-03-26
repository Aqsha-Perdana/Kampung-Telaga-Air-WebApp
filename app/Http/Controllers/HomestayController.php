<?php

namespace App\Http\Controllers;

use App\Http\Requests\Homestay\StoreHomestayRequest;
use App\Http\Requests\Homestay\UpdateHomestayRequest;
use App\Models\Homestay;
use App\Services\HomestayService;

class HomestayController extends Controller
{
    public function __construct(private readonly HomestayService $homestayService)
    {
    }

    public function index()
    {
        $homestays = $this->homestayService->paginate();

        return view('admin.homestay.index', compact('homestays'));
    }

    public function create()
    {
        return view('admin.homestay.create');
    }

    public function store(StoreHomestayRequest $request)
    {
        $this->homestayService->create($request->validated(), $request->file('foto'));

        return redirect()->route('homestays.index')
            ->with('success', 'Homestay berhasil ditambahkan!');
    }

    public function show(Homestay $homestay)
    {
        return view('admin.homestay.show', compact('homestay'));
    }

    public function edit(Homestay $homestay)
    {
        return view('admin.homestay.edit', compact('homestay'));
    }

    public function update(UpdateHomestayRequest $request, Homestay $homestay)
    {
        $this->homestayService->update($homestay, $request->validated(), $request->file('foto'));

        return redirect()->route('homestays.index')
            ->with('success', 'Homestay berhasil diperbarui!');
    }

    public function destroy(Homestay $homestay)
    {
        $this->homestayService->delete($homestay);

        return redirect()->route('homestays.index')
            ->with('success', 'Homestay berhasil dihapus!');
    }
}
