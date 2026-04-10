<?php

namespace App\Services;

use App\Models\FotoKiosk;
use App\Models\Kiosk;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KioskService
{
    public function paginateWithFotos(int $perPage = 10): LengthAwarePaginator
    {
        return Kiosk::query()
            ->withCount('fotos')
            ->with([
                'fotos' => fn ($query) => $query
                    ->select('id', 'id_kiosk', 'foto', 'urutan')
                    ->orderBy('urutan'),
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $validated, array $uploadedFotos = []): Kiosk
    {
        return DB::transaction(function () use ($validated, $uploadedFotos) {
            $kiosk = Kiosk::create([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'harga_per_paket' => $validated['harga_per_paket'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('kiosk', 'public');

                FotoKiosk::create([
                    'id_kiosk' => $kiosk->id_kiosk,
                    'foto' => $path,
                    'urutan' => $index + 1,
                ]);
            }

            return $kiosk;
        });
    }

    public function update(Kiosk $kiosk, array $validated, array $uploadedFotos = []): Kiosk
    {
        return DB::transaction(function () use ($kiosk, $validated, $uploadedFotos) {
            $kiosk->update([
                'nama' => $validated['nama'],
                'kapasitas' => $validated['kapasitas'],
                'harga_per_paket' => $validated['harga_per_paket'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            $fotoIdsToDelete = $validated['hapus_foto'] ?? [];
            foreach ($fotoIdsToDelete as $fotoId) {
                $foto = FotoKiosk::find($fotoId);
                if ($foto) {
                    Storage::disk('public')->delete($foto->foto);
                    $foto->delete();
                }
            }

            $lastUrutan = (int) (FotoKiosk::where('id_kiosk', $kiosk->id_kiosk)->max('urutan') ?? 0);
            foreach ($uploadedFotos as $index => $foto) {
                $path = $foto->store('kiosk', 'public');

                FotoKiosk::create([
                    'id_kiosk' => $kiosk->id_kiosk,
                    'foto' => $path,
                    'urutan' => $lastUrutan + $index + 1,
                ]);
            }

            return $kiosk;
        });
    }

    public function delete(Kiosk $kiosk): void
    {
        DB::transaction(function () use ($kiosk) {
            foreach ($kiosk->fotos as $foto) {
                Storage::disk('public')->delete($foto->foto);
            }

            $kiosk->delete();
        });
    }
}
