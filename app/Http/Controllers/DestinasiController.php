<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use App\Models\FotoDestinasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DestinasiController extends Controller
{
    public function index()
    {
        $destinasis = Destinasi::with('fotos')->latest()->paginate(10);
        return view('admin.destination.index', compact('destinasis'));
    }

    public function create()
    {
        return view('admin.destination.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Simpan destinasi
            $destinasi = Destinasi::create([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi']
            ]);

            // Simpan multiple foto
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('destinasi', 'public');
                    
                    FotoDestinasi::create([
                        'id_destinasi' => $destinasi->id_destinasi,
                        'foto' => $path,
                        'urutan' => $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan destinasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $destinasi = Destinasi::with('fotos')->findOrFail($id);
        return view('admin.destination.show', compact('destinasi'));
    }

    public function edit($id)
    {
        $destinasi = Destinasi::with('fotos')->findOrFail($id);
        return view('admin.destination.edit', compact('destinasi'));
    }

    public function update(Request $request, $id)
    {
        $destinasi = Destinasi::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hapus_foto.*' => 'nullable|exists:foto_destinasis,id'
        ]);

        DB::beginTransaction();
        try {
            // Update destinasi
            $destinasi->update([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi']
            ]);

            // Hapus foto yang dipilih
            if ($request->has('hapus_foto')) {
                foreach ($request->hapus_foto as $fotoId) {
                    $foto = FotoDestinasi::find($fotoId);
                    if ($foto) {
                        Storage::disk('public')->delete($foto->foto);
                        $foto->delete();
                    }
                }
            }

            // Tambah foto baru
            if ($request->hasFile('fotos')) {
                $lastUrutan = FotoDestinasi::where('id_destinasi', $destinasi->id_destinasi)
                    ->max('urutan') ?? 0;

                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('destinasi', 'public');
                    
                    FotoDestinasi::create([
                        'id_destinasi' => $destinasi->id_destinasi,
                        'foto' => $path,
                        'urutan' => $lastUrutan + $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate destinasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $destinasi = Destinasi::findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus semua foto
            foreach ($destinasi->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $destinasi->delete(); // Cascade delete akan menghapus foto di database

            DB::commit();
            return redirect()->route('destinasis.index')
                ->with('success', 'Destinasi berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus destinasi: ' . $e->getMessage());
        }
    }
}