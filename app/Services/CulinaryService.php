<?php

namespace App\Services;

use App\Models\Culinary;
use App\Models\FotoCulinary;
use App\Models\PaketCulinary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CulinaryService
{
    public function paginateWithRelations(int $perPage = 10): LengthAwarePaginator
    {
        return Culinary::with(['fotos', 'pakets'])->latest()->paginate($perPage);
    }

    public function create(array $validated, array $uploadedFotos = []): Culinary
    {
        return DB::transaction(function () use ($validated, $uploadedFotos) {
            $culinary = Culinary::create([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            $paketNama = $validated['paket_nama'] ?? [];
            foreach ($paketNama as $index => $namaPaket) {
                PaketCulinary::create([
                    'id_culinary' => $culinary->id_culinary,
                    'nama_paket' => $namaPaket,
                    'kapasitas' => $validated['paket_kapasitas'][$index],
                    'harga' => $validated['paket_harga'][$index],
                    'deskripsi_paket' => $validated['paket_deskripsi'][$index] ?? null,
                ]);
            }

            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('culinary', 'public');

                FotoCulinary::create([
                    'id_culinary' => $culinary->id_culinary,
                    'foto' => $path,
                    'urutan' => $index + 1,
                ]);
            }

            return $culinary;
        });
    }

    public function update(Culinary $culinary, array $validated, array $uploadedFotos = []): Culinary
    {
        return DB::transaction(function () use ($culinary, $validated, $uploadedFotos) {
            $culinary->update([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            $deletedPaketIds = $validated['hapus_paket'] ?? [];
            if (!empty($deletedPaketIds)) {
                PaketCulinary::whereIn('id', $deletedPaketIds)->delete();
            }

            $paketNama = $validated['paket_nama'] ?? [];
            foreach ($paketNama as $index => $namaPaket) {
                $paketId = $validated['paket_id'][$index] ?? null;

                if ($paketId && !in_array($paketId, $deletedPaketIds)) {
                    PaketCulinary::where('id', $paketId)->update([
                        'nama_paket' => $namaPaket,
                        'kapasitas' => $validated['paket_kapasitas'][$index],
                        'harga' => $validated['paket_harga'][$index],
                        'deskripsi_paket' => $validated['paket_deskripsi'][$index] ?? null,
                    ]);
                    continue;
                }

                if (!$paketId) {
                    PaketCulinary::create([
                        'id_culinary' => $culinary->id_culinary,
                        'nama_paket' => $namaPaket,
                        'kapasitas' => $validated['paket_kapasitas'][$index],
                        'harga' => $validated['paket_harga'][$index],
                        'deskripsi_paket' => $validated['paket_deskripsi'][$index] ?? null,
                    ]);
                }
            }

            $fotoIdsToDelete = $validated['hapus_foto'] ?? [];
            foreach ($fotoIdsToDelete as $fotoId) {
                $foto = FotoCulinary::find($fotoId);
                if ($foto) {
                    Storage::disk('public')->delete($foto->foto);
                    $foto->delete();
                }
            }

            $lastUrutan = (int) (FotoCulinary::where('id_culinary', $culinary->id_culinary)->max('urutan') ?? 0);
            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('culinary', 'public');

                FotoCulinary::create([
                    'id_culinary' => $culinary->id_culinary,
                    'foto' => $path,
                    'urutan' => $lastUrutan + $index + 1,
                ]);
            }

            return $culinary;
        });
    }

    public function delete(Culinary $culinary): void
    {
        DB::transaction(function () use ($culinary) {
            foreach ($culinary->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $culinary->delete();
        });
    }
}
