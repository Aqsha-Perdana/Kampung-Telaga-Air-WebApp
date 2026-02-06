<?php

namespace App\Http\Controllers;

use App\Models\Footage360;
use Illuminate\Http\Request;

class View360Controller extends Controller
{
    public function show(Footage360 $footage360)
    {
        // Load relasi destinasi
        $footage360->load('destinasi');
        
        // Ambil footage lain dari destinasi yang sama
        $relatedFootages = Footage360::where('id_destinasi', $footage360->id_destinasi)
                                     ->where('id_footage360', '!=', $footage360->id_footage360)
                                     ->where('is_active', true)
                                     ->orderBy('urutan')
                                     ->get();
        
        return view('view360', compact('footage360', 'relatedFootages'));
    }
}