<?php

namespace App\Services;

use App\Models\Boat;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class BoatService
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Boat::latest()->paginate($perPage);
    }

    public function create(array $validated, $uploadedFoto = null): Boat
    {
        if ($uploadedFoto) {
            $validated['foto'] = $uploadedFoto->store('boats', 'public');
        }

        return Boat::create($validated);
    }

    public function update(Boat $boat, array $validated, $uploadedFoto = null): Boat
    {
        if ($uploadedFoto) {
            if ($boat->foto) {
                Storage::disk('public')->delete($boat->foto);
            }

            $validated['foto'] = $uploadedFoto->store('boats', 'public');
        }

        $boat->update($validated);

        return $boat;
    }

    public function delete(Boat $boat): void
    {
        if ($boat->foto) {
            Storage::disk('public')->delete($boat->foto);
        }

        $boat->delete();
    }
}
