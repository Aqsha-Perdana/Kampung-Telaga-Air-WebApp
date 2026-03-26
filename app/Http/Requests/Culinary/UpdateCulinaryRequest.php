<?php

namespace App\Http\Requests\Culinary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCulinaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
            'hapus_paket.*' => 'nullable|exists:paket_culinaries,id',
        ];
    }
}
