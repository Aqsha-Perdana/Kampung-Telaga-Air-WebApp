<?php

namespace App\Services;

use App\Models\Destinasi;
use App\Models\FotoDestinasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DestinasiService
{
    public function paginateWithFotos(int $perPage = 10): LengthAwarePaginator
    {
        return Destinasi::with('fotos')->latest()->paginate($perPage);
    }

    public function create(array $validated, array $uploadedFotos = []): Destinasi
    {
        return DB::transaction(function () use ($validated, $uploadedFotos) {
            $destinasi = Destinasi::create([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'],
            ]);

            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('destinasi', 'public');

                FotoDestinasi::create([
                    'id_destinasi' => $destinasi->id_destinasi,
                    'foto' => $path,
                    'urutan' => $index + 1,
                ]);
            }

            return $destinasi;
        });
    }

    public function update(Destinasi $destinasi, array $validated, array $uploadedFotos = []): Destinasi
    {
        return DB::transaction(function () use ($destinasi, $validated, $uploadedFotos) {
            $destinasi->update([
                'nama' => $validated['nama'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'],
            ]);

            $fotoIdsToDelete = $validated['hapus_foto'] ?? [];
            foreach ($fotoIdsToDelete as $fotoId) {
                $foto = FotoDestinasi::find($fotoId);
                if ($foto) {
                    Storage::disk('public')->delete($foto->foto);
                    $foto->delete();
                }
            }

            $lastUrutan = (int) (FotoDestinasi::where('id_destinasi', $destinasi->id_destinasi)->max('urutan') ?? 0);
            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('destinasi', 'public');

                FotoDestinasi::create([
                    'id_destinasi' => $destinasi->id_destinasi,
                    'foto' => $path,
                    'urutan' => $lastUrutan + $index + 1,
                ]);
            }

            return $destinasi;
        });
    }

    public function delete(Destinasi $destinasi): void
    {
        DB::transaction(function () use ($destinasi) {
            foreach ($destinasi->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $destinasi->delete();
        });
    }
}
