<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Footage360;
use App\Models\Destinasi;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Footage360Controller extends Controller
{
    public function index()
    {
        $footages = Footage360::with('destinasi')->latest()->paginate(10);
        return view('admin.footage360.index', compact('footages'));
    }

    public function create()
    {
        $destinasis = Destinasi::orderBy('nama')->get();
        return view('admin.footage360.create', compact('destinasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_destinasi' => 'required|exists:destinasis,id_destinasi',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_foto' => 'required|file',
            'file_lrv' => 'nullable|file',
            'urutan' => 'nullable|integer',
        ]);

        $data = $request->only(['id_destinasi', 'judul', 'deskripsi', 'urutan']);
        $data['is_active'] = $request->has('is_active') ? true : false;

        try {
            // Upload foto 360 ke Cloudinary
            if ($request->hasFile('file_foto')) {
                $uploadedFile = Cloudinary::upload(
                    $request->file('file_foto')->getRealPath(),
                    [
                        'folder' => 'footage360',
                        'resource_type' => 'auto',
                        'transformation' => [
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );
                
                $data['file_foto'] = $uploadedFile->getSecurePath();
                $data['cloudinary_public_id'] = $uploadedFile->getPublicId();
            }

            // Upload LRV ke Cloudinary (optional)
            if ($request->hasFile('file_lrv')) {
                $uploadedLrv = Cloudinary::upload(
                    $request->file('file_lrv')->getRealPath(),
                    [
                        'folder' => 'footage360/lrv',
                        'resource_type' => 'auto'
                    ]
                );
                
                $data['file_lrv'] = $uploadedLrv->getSecurePath();
                $data['cloudinary_public_id_lrv'] = $uploadedLrv->getPublicId();
            }

            Footage360::create($data);

            return redirect()->route('footage360.index')
                            ->with('success', 'Footage 360° berhasil diupload ke Cloudinary!');
                            
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal upload: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function edit(Footage360 $footage360)
    {
        $destinasis = Destinasi::orderBy('nama')->get();
        return view('admin.footage360.edit', compact('footage360', 'destinasis'));
    }

    public function update(Request $request, Footage360 $footage360)
    {
        $request->validate([
            'id_destinasi' => 'required|exists:destinasis,id_destinasi',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_foto' => 'nullable|file',
            'file_lrv' => 'nullable|file',
            'urutan' => 'nullable|integer',
        ]);

        $data = $request->only(['id_destinasi', 'judul', 'deskripsi', 'urutan']);
        $data['is_active'] = $request->has('is_active') ? true : false;

        try {
            // Update foto 360
            if ($request->hasFile('file_foto')) {
                // Hapus foto lama dari Cloudinary
                if ($footage360->cloudinary_public_id) {
                    Cloudinary::destroy($footage360->cloudinary_public_id);
                }
                
                // Upload foto baru
                $uploadedFile = Cloudinary::upload(
                    $request->file('file_foto')->getRealPath(),
                    [
                        'folder' => 'footage360',
                        'resource_type' => 'auto',
                        'transformation' => [
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );
                
                $data['file_foto'] = $uploadedFile->getSecurePath();
                $data['cloudinary_public_id'] = $uploadedFile->getPublicId();
            }

            // Update LRV
            if ($request->hasFile('file_lrv')) {
                // Hapus LRV lama dari Cloudinary
                if ($footage360->cloudinary_public_id_lrv) {
                    Cloudinary::destroy($footage360->cloudinary_public_id_lrv);
                }
                
                // Upload LRV baru
                $uploadedLrv = Cloudinary::upload(
                    $request->file('file_lrv')->getRealPath(),
                    [
                        'folder' => 'footage360/lrv',
                        'resource_type' => 'auto'
                    ]
                );
                
                $data['file_lrv'] = $uploadedLrv->getSecurePath();
                $data['cloudinary_public_id_lrv'] = $uploadedLrv->getPublicId();
            }

            $footage360->update($data);

            return redirect()->route('footage360.index')
                            ->with('success', 'Footage 360° berhasil diupdate!');
                            
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal update: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function destroy(Footage360 $footage360)
    {
        try {
            // Hapus file dari Cloudinary
            if ($footage360->cloudinary_public_id) {
                Cloudinary::destroy($footage360->cloudinary_public_id);
            }
            if ($footage360->cloudinary_public_id_lrv) {
                Cloudinary::destroy($footage360->cloudinary_public_id_lrv);
            }

            $footage360->delete();

            return redirect()->route('footage360.index')
                            ->with('success', 'Footage 360° berhasil dihapus dari Cloudinary!');
                            
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}