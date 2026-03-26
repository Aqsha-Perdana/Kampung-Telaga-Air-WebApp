<?php

namespace App\Http\Requests\Boat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoatRequest extends FormRequest
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
            'harga_sewa' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
