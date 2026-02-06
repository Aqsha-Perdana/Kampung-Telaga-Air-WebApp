<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoatController extends Controller
{
    public function index()
    {
        $boats = Boat::latest()->paginate(10);
        return view('admin.boat.index', compact('boats'));
    }

    public function create()
    {
        return view('admin.boat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_sewa' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('boats', 'public');
        }

        Boat::create($validated);

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

    public function update(Request $request, Boat $boat)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_sewa' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($boat->foto) {
                Storage::disk('public')->delete($boat->foto);
            }
            $validated['foto'] = $request->file('foto')->store('boats', 'public');
        }

        $boat->update($validated);

        return redirect()->route('boats.index')
            ->with('success', 'Boat berhasil diperbarui!');
    }

    public function destroy(Boat $boat)
    {
        // Delete foto if exists
        if ($boat->foto) {
            Storage::disk('public')->delete($boat->foto);
        }

        $boat->delete();

        return redirect()->route('boats.index')
            ->with('success', 'Boat berhasil dihapus!');
    }
}