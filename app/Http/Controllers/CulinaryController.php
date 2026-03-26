<?php

namespace App\Http\Controllers;

use App\Http\Requests\Culinary\StoreCulinaryRequest;
use App\Http\Requests\Culinary\UpdateCulinaryRequest;
use App\Models\Culinary;
use App\Services\CulinaryService;

class CulinaryController extends Controller
{
    public function __construct(private readonly CulinaryService $culinaryService)
    {
    }

    public function index()
    {
        $culinaries = $this->culinaryService->paginateWithRelations();

        return view('admin.culinary.index', compact('culinaries'));
    }

    public function create()
    {
        return view('admin.culinary.create');
    }

    public function store(StoreCulinaryRequest $request)
    {
        try {
            $this->culinaryService->create(
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kuliner: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Culinary $culinary)
    {
        $culinary->load(['fotos', 'pakets']);

        return view('admin.culinary.show', compact('culinary'));
    }

    public function edit(Culinary $culinary)
    {
        $culinary->load(['fotos', 'pakets']);

        return view('admin.culinary.edit', compact('culinary'));
    }

    public function update(UpdateCulinaryRequest $request, Culinary $culinary)
    {
        try {
            $this->culinaryService->update(
                $culinary,
                $request->validated(),
                $request->file('fotos', [])
            );

            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil diupdate!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate kuliner: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Culinary $culinary)
    {
        try {
            $this->culinaryService->delete($culinary);

            return redirect()->route('culinaries.index')
                ->with('success', 'Kuliner berhasil dihapus!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus kuliner: ' . $e->getMessage());
        }
    }
}
