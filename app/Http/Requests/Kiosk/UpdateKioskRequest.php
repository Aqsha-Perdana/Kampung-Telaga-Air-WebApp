<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKioskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_paket' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hapus_foto.*' => 'nullable|exists:foto_kiosks,id',
        ];
    }
}
