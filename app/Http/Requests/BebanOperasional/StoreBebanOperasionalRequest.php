<?php

namespace App\Http\Requests\BebanOperasional;

use Illuminate\Foundation\Http\FormRequest;

class StoreBebanOperasionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
            'nomor_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
