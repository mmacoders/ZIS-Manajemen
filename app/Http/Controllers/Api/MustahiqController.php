<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mustahiq;
use Illuminate\Http\Request;

class MustahiqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Mustahiq::with('distributions');

            // Search functionality
            if ($request->search_name) {
                $query->where('nama', 'like', '%' . $request->search_name . '%');
            }

            if ($request->search_address) {
                $query->where('alamat', 'like', '%' . $request->search_address . '%');
            }

            if ($request->search_contact) {
                $query->where('telepon', 'like', '%' . $request->search_contact . '%');
            }

            if ($request->kategori) {
                $query->where('kategori', $request->kategori);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Sorting
            if ($request->sort_by) {
                $direction = $request->sort_direction ?? 'asc';
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination - default 10 per page
            $perPage = $request->per_page ?? 10;
            $mustahiq = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $mustahiq
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'nik' => 'required|string|unique:mustahiq,nik|size:16',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'kategori' => 'required|in:fakir,miskin,amil,muallaf,riqab,gharim,fisabilillah,ibnu_sabil',
                'keterangan' => 'nullable|string|max:1000',
                'status' => 'required|in:aktif,nonaktif'
            ]);

            $mustahiq = Mustahiq::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Mustahiq berhasil ditambahkan',
                'data' => $mustahiq
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $mustahiq = Mustahiq::with('distributions.program')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $mustahiq
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mustahiq tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $mustahiq = Mustahiq::findOrFail($id);
            
            $request->validate([
                'nama' => 'required|string|max:255',
                'nik' => 'required|string|unique:mustahiq,nik,' . $id . '|size:16',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'kategori' => 'required|in:fakir,miskin,amil,muallaf,riqab,gharim,fisabilillah,ibnu_sabil',
                'keterangan' => 'nullable|string|max:1000',
                'status' => 'required|in:aktif,nonaktif'
            ]);

            $mustahiq->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Mustahiq berhasil diperbarui',
                'data' => $mustahiq
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mustahiq tidak ditemukan'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mustahiq = Mustahiq::findOrFail($id);
            
            // Check if mustahiq has any distributions
            if ($mustahiq->distributions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus mustahiq yang memiliki distribusi'
                ], 422);
            }
            
            $mustahiq->delete();
            return response()->json([
                'success' => true,
                'message' => 'Mustahiq berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mustahiq tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}