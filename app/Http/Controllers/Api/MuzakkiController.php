<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Muzakki;
use Illuminate\Http\Request;

class MuzakkiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Muzakki::with('zisTransactions');

            // Field-specific search functionality
            if ($request->search_name) {
                $query->where('nama', 'like', '%' . $request->search_name . '%');
            }

            if ($request->search_address) {
                $query->where('alamat', 'like', '%' . $request->search_address . '%');
            }

            if ($request->search_contact) {
                $search_contact = $request->search_contact;
                $query->where(function($q) use ($search_contact) {
                    $q->where('telepon', 'like', '%' . $search_contact . '%')
                      ->orWhere('email', 'like', '%' . $search_contact . '%');
                });
            }

            // Legacy search for backward compatibility
            if ($request->search && !$request->search_name && !$request->search_address && !$request->search_contact) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('nik', 'like', '%' . $search . '%')
                      ->orWhere('alamat', 'like', '%' . $search . '%')
                      ->orWhere('telepon', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            if ($request->jenis) {
                $query->where('jenis', $request->jenis);
            }

            if ($request->sort_by) {
                $direction = $request->sort_direction ?? 'asc';
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination - default 10 per page
            $perPage = $request->per_page ?? 10;
            $muzakki = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $muzakki
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
                'nik' => 'required|string|unique:muzakki,nik|size:16',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'email' => 'nullable|email|max:255',
                'jenis' => 'required|in:individu,perusahaan',
                'keterangan' => 'nullable|string|max:1000'
            ]);

            $muzakki = Muzakki::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Muzakki berhasil ditambahkan',
                'data' => $muzakki
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
        $muzakki = Muzakki::with('zisTransactions')->findOrFail($id);
        return response()->json($muzakki);
    }

    public function update(Request $request, $id)
    {
        try {
            $muzakki = Muzakki::findOrFail($id);
            
            $request->validate([
                'nama' => 'required|string|max:255',
                'nik' => 'required|string|unique:muzakki,nik,' . $id . '|size:16',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'email' => 'nullable|email|max:255',
                'jenis' => 'required|in:individu,perusahaan',
                'keterangan' => 'nullable|string|max:1000'
            ]);

            $muzakki->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Muzakki berhasil diperbarui',
                'data' => $muzakki
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Muzakki tidak ditemukan'
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
            $muzakki = Muzakki::findOrFail($id);
            
            // Check if muzakki has any transactions
            if ($muzakki->zisTransactions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus muzakki yang memiliki transaksi'
                ], 422);
            }
            
            $muzakki->delete();
            return response()->json([
                'success' => true,
                'message' => 'Muzakki berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Muzakki tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = Muzakki::query();

            if ($request->nama) {
                $query->where('nama', 'like', '%' . $request->nama . '%');
            }

            if ($request->nik) {
                $query->where('nik', 'like', '%' . $request->nik . '%');
            }

            if ($request->jenis) {
                $query->where('jenis', $request->jenis);
            }

            if ($request->alamat) {
                $query->where('alamat', 'like', '%' . $request->alamat . '%');
            }

            $results = $query->with('zisTransactions')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}