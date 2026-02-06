<?php

namespace App\Http\Controllers;

use App\Models\Culinary;
use App\Models\PaketCulinary;
use App\Models\FotoCulinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CulinaryController extends Controller
{
    public function index()
    {
        $culinaries = Culinary::with(['fotos', 'pakets'])->latest()->paginate(10);
        return view('admin.culinary.index', compact('culinaries'));
    }

    public function create()
    {
        return view('admin.culinary.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'paket_nama.*' => 'required|string|max:255',
            'paket_kapasitas.*' => 'required|integer|min:1',
            'paket_harga.*' => 'required|numeric|min:0',
            'paket_deskripsi.*' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Simpan culinary
            $culinary = Culinary::create([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'] ?? null
            ]);

            // Simpan paket-paket
            if ($request->has('paket_nama')) {
                foreach ($request->paket_nama as $index => $namaPaket) {
                    PaketCulinary::create([
                        'id_culinary' => $culinary->id_culinary,
                        'nama_paket' => $namaPaket,
                        'kapasitas' => $request->paket_kapasitas[$index],
                        'harga' => $request->paket_harga[$index],
                        'deskripsi_paket' => $request->paket_deskripsi[$index] ?? null
                    ]);
                }
            }

            // Simpan multiple foto
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('culinary', 'public');
                    
                    FotoCulinary::create([
                        'id_culinary' => $culinary->id_culinary,
                        'foto' => $path,
                        'urutan' => $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kuliner: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $culinary = Culinary::with(['fotos', 'pakets'])->findOrFail($id);
        return view('admin.culinary.show', compact('culinary'));
    }

    public function edit($id)
    {
        $culinary = Culinary::with(['fotos', 'pakets'])->findOrFail($id);
        return view('admin.culinary.edit', compact('culinary'));
    }

    public function update(Request $request, $id)
    {
        $culinary = Culinary::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hapus_foto.*' => 'nullable|exists:foto_culinaries,id',
            'paket_id.*' => 'nullable|exists:paket_culinaries,id',
            'paket_nama.*' => 'required|string|max:255',
            'paket_kapasitas.*' => 'required|integer|min:1',
            'paket_harga.*' => 'required|numeric|min:0',
            'paket_deskripsi.*' => 'nullable|string',
            'hapus_paket.*' => 'nullable|exists:paket_culinaries,id'
        ]);

        DB::beginTransaction();
        try {
            // Update culinary
            $culinary->update([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'] ?? null
            ]);

            // Hapus paket yang dipilih
            if ($request->has('hapus_paket')) {
                PaketCulinary::whereIn('id', $request->hapus_paket)->delete();
            }

            // Update atau tambah paket
            if ($request->has('paket_nama')) {
                foreach ($request->paket_nama as $index => $namaPaket) {
                    $paketId = $request->paket_id[$index] ?? null;
                    
                    if ($paketId && !in_array($paketId, $request->hapus_paket ?? [])) {
                        // Update existing paket
                        PaketCulinary::where('id', $paketId)->update([
                            'nama_paket' => $namaPaket,
                            'kapasitas' => $request->paket_kapasitas[$index],
                            'harga' => $request->paket_harga[$index],
                            'deskripsi_paket' => $request->paket_deskripsi[$index] ?? null
                        ]);
                    } elseif (!$paketId) {
                        // Create new paket
                        PaketCulinary::create([
                            'id_culinary' => $culinary->id_culinary,
                            'nama_paket' => $namaPaket,
                            'kapasitas' => $request->paket_kapasitas[$index],
                            'harga' => $request->paket_harga[$index],
                            'deskripsi_paket' => $request->paket_deskripsi[$index] ?? null
                        ]);
                    }
                }
            }

            // Hapus foto yang dipilih
            if ($request->has('hapus_foto')) {
                foreach ($request->hapus_foto as $fotoId) {
                    $foto = FotoCulinary::find($fotoId);
                    if ($foto) {
                        Storage::disk('public')->delete($foto->foto);
                        $foto->delete();
                    }
                }
            }

            // Tambah foto baru
            if ($request->hasFile('fotos')) {
                $lastUrutan = FotoCulinary::where('id_culinary', $culinary->id_culinary)
                    ->max('urutan') ?? 0;

                foreach ($request->file('fotos') as $index => $foto) {
                    $path = $foto->store('culinary', 'public');
                    
                    FotoCulinary::create([
                        'id_culinary' => $culinary->id_culinary,
                        'foto' => $path,
                        'urutan' => $lastUrutan + $index + 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate kuliner: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $culinary = Culinary::findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus semua foto
            foreach ($culinary->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $culinary->delete(); // Cascade delete akan menghapus foto dan paket di database

            DB::commit();
            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus kuliner: ' . $e->getMessage());
        }
    }
}