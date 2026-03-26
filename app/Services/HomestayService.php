<?php

namespace App\Services;

use App\Models\Homestay;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class HomestayService
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Homestay::latest()->paginate($perPage);
    }

    public function create(array $validated, $uploadedFoto = null): Homestay
    {
        if ($uploadedFoto) {
            $validated['foto'] = $uploadedFoto->store('homestays', 'public');
        }

        return Homestay::create($validated);
    }

    public function update(Homestay $homestay, array $validated, $uploadedFoto = null): Homestay
    {
        if ($uploadedFoto) {
            if ($homestay->foto) {
                Storage::disk('public')->delete($homestay->foto);
            }

            $validated['foto'] = $uploadedFoto->store('homestays', 'public');
        }

        $homestay->update($validated);

        return $homestay;
    }

    public function delete(Homestay $homestay): void
    {
        if ($homestay->foto) {
            Storage::disk('public')->delete($homestay->foto);
        }

        $homestay->delete();
    }
}
