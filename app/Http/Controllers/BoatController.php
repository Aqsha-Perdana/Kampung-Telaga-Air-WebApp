<?php

namespace App\Http\Controllers;

use App\Http\Requests\Boat\StoreBoatRequest;
use App\Http\Requests\Boat\UpdateBoatRequest;
use App\Models\Boat;
use App\Services\BoatService;

class BoatController extends Controller
{
    public function __construct(private readonly BoatService $boatService)
    {
    }

    public function index()
    {
        $boats = $this->boatService->paginate();

        return view('admin.boat.index', compact('boats'));
    }

    public function create()
    {
        return view('admin.boat.create');
    }

    public function store(StoreBoatRequest $request)
    {
        $this->boatService->create($request->validated(), $request->file('foto'));

        return redirect()->route('boats.index')
            ->with('success', 'Boat berhasil ditambahkan!');
    }

    public function show(Boat $boat)
    {
        return view('admin.boat.show', compact('boat'));
    }

    public function edit(Boat $boat)
    {
        return view('admin.boat.edit', compact('boat'));
    }

    public function update(UpdateBoatRequest $request, Boat $boat)
    {
        $this->boatService->update($boat, $request->validated(), $request->file('foto'));

        return redirect()->route('boats.index')
            ->with('success', 'Boat berhasil diperbarui!');
    }

    public function destroy(Boat $boat)
    {
        $this->boatService->delete($boat);

        return redirect()->route('boats.index')
            ->with('success', 'Boat berhasil dihapus!');
    }
}
