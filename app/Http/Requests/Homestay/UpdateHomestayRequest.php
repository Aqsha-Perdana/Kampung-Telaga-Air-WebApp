<?php

namespace App\Http\Requests\Homestay;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomestayRequest extends FormRequest
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
            'harga_per_malam' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
