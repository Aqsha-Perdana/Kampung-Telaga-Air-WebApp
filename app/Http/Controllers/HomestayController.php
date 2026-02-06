<?php

namespace App\Http\Controllers;

use App\Models\Homestay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomestayController extends Controller
{
    public function index()
    {
        $homestays = Homestay::latest()->paginate(10);
        return view('admin.homestay.index', compact('homestays'));
    }

    public function create()
    {
        return view('admin.homestay.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_malam' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('homestays', 'public');
        }

        Homestay::create($validated);

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

    public function update(Request $request, Homestay $homestay)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_malam' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($homestay->foto) {
                Storage::disk('public')->delete($homestay->foto);
            }
            $validated['foto'] = $request->file('foto')->store('homestays', 'public');
        }

        $homestay->update($validated);

        return redirect()->route('homestays.index')
            ->with('success', 'Homestay berhasil diperbarui!');
    }

    public function destroy(Homestay $homestay)
    {
        // Delete foto if exists
        if ($homestay->foto) {
            Storage::disk('public')->delete($homestay->foto);
        }

        $homestay->delete();

        return redirect()->route('homestays.index')
            ->with('success', 'Homestay berhasil dihapus!');
    }
}