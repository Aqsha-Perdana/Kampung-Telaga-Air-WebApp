<?php

namespace App\Http\Controllers;

use App\Models\Kiosk;
use App\Models\FotoKiosk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    public function index()
    {
        $kiosks = Kiosk::with('fotos')->latest()->paginate(10);
        return view('admin.kiosk.index', compact('kiosks'));
    }

    public function create()
    {
        return view('admin.kiosk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_paket' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Simpan kiosk
            $kiosk = Kiosk::create([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'harga_per_paket' => $validated['harga_per_paket'],
                'deskripsi' => $validated['deskripsi'] ?? null
            ]);

            // Simpan multiple foto
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('kiosk', 'public');
                    
                    FotoKiosk::create([
                        'id_kiosk' => $kiosk->id_kiosk,
                        'foto' => $path,
                        'urutan' => $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kiosk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $kiosk = Kiosk::with('fotos')->findOrFail($id);
        return view('admin.kiosk.show', compact('kiosk'));
    }

    public function edit($id)
    {
        $kiosk = Kiosk::with('fotos')->findOrFail($id);
        return view('admin.kiosk.edit', compact('kiosk'));
    }

    public function update(Request $request, $id)
    {
        $kiosk = Kiosk::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_paket' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hapus_foto.*' => 'nullable|exists:foto_kiosks,id'
        ]);

        DB::beginTransaction();
        try {
            // Update kiosk
            $kiosk->update([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'harga_per_paket' => $validated['harga_per_paket'],
                'deskripsi' => $validated['deskripsi'] ?? null
            ]);

            // Hapus foto yang dipilih
            if ($request->has('hapus_foto')) {
                foreach ($request->hapus_foto as $fotoId) {
                    $foto = FotoKiosk::find($fotoId);
                    if ($foto) {
                        Storage::disk('public')->delete($foto->foto);
                        $foto->delete();
                    }
                }
            }

            // Tambah foto baru
            if ($request->hasFile('fotos')) {
                $lastUrutan = FotoKiosk::where('id_kiosk', $kiosk->id_kiosk)
                    ->max('urutan') ?? 0;

                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('kiosk', 'public');
                    
                    FotoKiosk::create([
                        'id_kiosk' => $kiosk->id_kiosk,
                        'foto' => $path,
                        'urutan' => $lastUrutan + $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate kiosk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $kiosk = Kiosk::findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus semua foto
            foreach ($kiosk->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $kiosk->delete(); // Cascade delete akan menghapus foto di database

            DB::commit();
            return redirect()->route('kiosks.index')
                ->with('success', 'Kiosk berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus kiosk: ' . $e->getMessage());
        }
    }
}