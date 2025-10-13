<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZisTransaction;
use App\Models\Donatur;
use App\Models\Upz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ZisTransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ZisTransaction::with(['donatur', 'upz']);

            // Search functionality
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_transaksi', 'like', '%' . $search . '%')
                      ->orWhereHas('donatur', function($q) use ($search) {
                          $q->where('nama', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('upz', function($q) use ($search) {
                          $q->where('nama', 'like', '%' . $search . '%');
                      });
                });
            }

            // Filter by jenis_zis
            if ($request->jenis_zis) {
                $query->where('jenis_zis', $request->jenis_zis);
            }

            // Filter by status
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->start_date) {
                $query->where('tanggal_transaksi', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('tanggal_transaksi', '<=', $request->end_date);
            }

            // Sorting
            if ($request->sort_by) {
                $direction = $request->sort_direction ?? 'asc';
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->per_page ?? 10;
            $transactions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions
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
            // Conditional validation based on whether donor is provided
            $rules = [
                'jenis_zis' => 'required|in:zakat,infaq,sedekah',
                'jumlah' => 'required|numeric|min:0',
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'nullable|string|max:1000',
                'bukti_transfer' => 'nullable|string|max:255'
            ];
            
            // Only require donor_id if it's provided (not null/empty)
            if ($request->has('donatur_id') && $request->donatur_id) {
                $rules['donatur_id'] = 'required|exists:donatur,id';
            }
            
            $request->validate($rules);

            // Generate receipt number with new format
            $tanggal = Carbon::parse($request->tanggal_transaksi);
            $day = str_pad($tanggal->day, 2, '0', STR_PAD_LEFT);
            $month = str_pad($tanggal->month, 2, '0', STR_PAD_LEFT);
            $year = substr($tanggal->year, -1); // Last digit of year
            
            // Get donor type code
            // 01 = perorangan (individual/munfiq)
            // 02 = lembaga (institution)
            // 03 = operasional (operational)
            $donaturCode = '03'; // Default to operational
            if ($request->has('donatur_id') && $request->donatur_id) {
                $donatur = Donatur::find($request->donatur_id);
                if ($donatur) {
                    switch ($donatur->jenis_donatur) {
                        case 'lembaga':
                            $donaturCode = '02';
                            break;
                        case 'individu':
                        case 'munfiq':
                            $donaturCode = '01';
                            break;
                    }
                }
            }
            
            // Get transaction count for the day
            $dailyCount = ZisTransaction::whereDate('tanggal_transaksi', $tanggal->toDateString())->count() + 1;
            $dailyCountFormatted = str_pad($dailyCount, 7, '0', STR_PAD_LEFT);
            
            // New receipt number format: DD/MM/Y/DC (Day/Month/YearLastDigit/DonaturCode)/(DailyCount)
            $nomor_kwitansi = "{$day}/{$month}/{$year}/{$donaturCode}/{$dailyCountFormatted}";
            
            // Prepare data for creation
            $data = $request->all();
            $data['nomor_transaksi'] = $nomor_kwitansi;
            
            $transaction = ZisTransaction::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan',
                'data' => $transaction->load(['donatur', 'upz'])
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
            $transaction = ZisTransaction::with(['donatur', 'upz', 'verifier'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transaction = ZisTransaction::findOrFail($id);
            
            // Conditional validation based on whether donor is provided
            $rules = [
                'jenis_zis' => 'required|in:zakat,infaq,sedekah',
                'jumlah' => 'required|numeric|min:0',
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'nullable|string|max:1000',
                'bukti_transfer' => 'nullable|string|max:255',
                'status' => 'nullable|in:pending,verified,rejected',
                'verified_by' => 'nullable|exists:users,id',
                'verified_at' => 'nullable|date'
            ];
            
            // Only require donor_id if it's provided (not null/empty)
            if ($request->has('donatur_id') && $request->donatur_id) {
                $rules['donatur_id'] = 'required|exists:donatur,id';
            }
            
            $request->validate($rules);

            // Update data
            $transaction->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui',
                'data' => $transaction->load(['donatur', 'upz'])
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
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
            $transaction = ZisTransaction::findOrFail($id);
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verify(Request $request, $id)
    {
        try {
            $transaction = ZisTransaction::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:verified,rejected',
                'verified_by' => 'required|exists:users,id'
            ]);

            $transaction->update([
                'status' => $request->status,
                'verified_by' => $request->verified_by,
                'verified_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diverifikasi',
                'data' => $transaction->load(['donatur', 'upz', 'verifier'])
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
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
                'message' => 'Terjadi kesalahan saat memverifikasi transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTotalAmountByPeriod(Request $request)
    {
        try {
            $query = ZisTransaction::where('status', 'verified');

            // Filter by date range
            if ($request->start_date) {
                $query->where('tanggal_transaksi', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('tanggal_transaksi', '<=', $request->end_date);
            }

            // Filter by jenis_zis
            if ($request->jenis_zis) {
                $query->where('jenis_zis', $request->jenis_zis);
            }

            $totalAmount = $query->sum('jumlah');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_amount' => $totalAmount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total jumlah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}