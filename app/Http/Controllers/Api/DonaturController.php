<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donatur;
use Illuminate\Http\Request;

class DonaturController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Donatur::with('zisTransactions');

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
                      // For institutional donors, search by NPWP instead of NIK
                      ->orWhere(function($subQuery) use ($search) {
                          $subQuery->where('jenis_donatur', 'lembaga')
                                   ->where('npwp', 'like', '%' . $search . '%');
                      })
                      // For individual donors, search by NIK
                      ->orWhere(function($subQuery) use ($search) {
                          $subQuery->where('jenis_donatur', '!=', 'lembaga')
                                   ->where('nik', 'like', '%' . $search . '%');
                      })
                      ->orWhere('alamat', 'like', '%' . $search . '%')
                      ->orWhere('telepon', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            if ($request->jenis_donatur) {
                $query->where('jenis_donatur', $request->jenis_donatur);
            }

            if ($request->sort_by) {
                $direction = $request->sort_direction ?? 'asc';
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination - default 10 per page
            $perPage = $request->per_page ?? 10;
            $donatur = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $donatur
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
            $rules = [
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'email' => 'nullable|email|max:255',
                'jenis_donatur' => 'required|in:individu,lembaga,munfiq',
                'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
                'keterangan' => 'nullable|string|max:1000',
                'gaji_pokok' => 'nullable|numeric|min:0',
                'jenis_zakat' => 'nullable|in:zakat penghasilan,zakat mal,zakat fitrah,infaq,sedekah',
                'nominal_setoran' => 'nullable|numeric|min:0',
                'metode_pembayaran' => 'nullable|in:tunai,transfer bank,e-wallet,lainnya',
                'tanggal_setoran' => 'nullable|date'
            ];

            // Add NIK validation for individual donors
            if ($request->jenis_donatur == 'individu' || $request->jenis_donatur == 'munfiq') {
                $rules['nik'] = 'required|string|unique:donatur,nik|size:16';
            }

            // Add NPWP validation for institutional donors
            if ($request->jenis_donatur == 'lembaga') {
                $rules['npwp'] = 'required|string|unique:donatur,npwp|max:20';
            }

            $request->validate($rules);

            $donatur = Donatur::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Donatur berhasil ditambahkan',
                'data' => $donatur
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
            $donatur = Donatur::with('zisTransactions')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $donatur
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donatur tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $donatur = Donatur::findOrFail($id);
            
            $rules = [
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string|max:500',
                'telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                'email' => 'nullable|email|max:255',
                'jenis_donatur' => 'required|in:individu,lembaga,munfiq',
                'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
                'keterangan' => 'nullable|string|max:1000',
                'gaji_pokok' => 'nullable|numeric|min:0',
                'jenis_zakat' => 'nullable|in:zakat penghasilan,zakat mal,zakat fitrah,infaq,sedekah',
                'nominal_setoran' => 'nullable|numeric|min:0',
                'metode_pembayaran' => 'nullable|in:tunai,transfer bank,e-wallet,lainnya',
                'tanggal_setoran' => 'nullable|date'
            ];

            // Add NIK validation for individual donors
            if ($request->jenis_donatur == 'individu' || $request->jenis_donatur == 'munfiq') {
                $rules['nik'] = 'required|string|unique:donatur,nik,' . $id . '|size:16';
            }

            // Add NPWP validation for institutional donors
            if ($request->jenis_donatur == 'lembaga') {
                $rules['npwp'] = 'required|string|unique:donatur,npwp,' . $id . '|max:20';
            }

            $request->validate($rules);

            $donatur->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Donatur berhasil diperbarui',
                'data' => $donatur
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donatur tidak ditemukan'
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
            $donatur = Donatur::findOrFail($id);
            
            // Check if donatur has any transactions
            if ($donatur->zisTransactions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus donatur yang memiliki transaksi'
                ], 422);
            }
            
            $donatur->delete();
            return response()->json([
                'success' => true,
                'message' => 'Donatur berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donatur tidak ditemukan'
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
            $query = Donatur::query();
            
            if ($request->q) {
                $q = $request->q;
                $query->where(function($qBuilder) use ($q) {
                    $qBuilder->where('nama', 'like', '%' . $q . '%')
                             // For institutional donors, search by NPWP instead of NIK
                             ->orWhere(function($subQuery) use ($q) {
                                 $subQuery->where('jenis_donatur', 'lembaga')
                                          ->where('npwp', 'like', '%' . $q . '%');
                             })
                             // For individual donors, search by NIK
                             ->orWhere(function($subQuery) use ($q) {
                                 $subQuery->where('jenis_donatur', '!=', 'lembaga')
                                          ->where('nik', 'like', '%' . $q . '%');
                             })
                             ->orWhere('alamat', 'like', '%' . $q . '%')
                             ->orWhere('telepon', 'like', '%' . $q . '%')
                             ->orWhere('email', 'like', '%' . $q . '%');
                });
            }
            
            $donatur = $query->limit(10)->get(['id', 'nama', 'nik', 'npwp', 'alamat', 'jenis_donatur']);
            
            return response()->json([
                'success' => true,
                'data' => $donatur
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