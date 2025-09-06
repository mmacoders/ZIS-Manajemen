<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZisTransaction;
use App\Events\ZisTransactionCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ZisTransactionController extends Controller
{
    public function index()
    {
        $transactions = ZisTransaction::with(['muzakki', 'upz', 'verifier'])->paginate(20);
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'muzakki_id' => 'required|exists:muzakki,id',
                'upz_id' => 'nullable|exists:upz,id',
                'jenis_zis' => 'required|in:zakat,infaq,sedekah',
                'jumlah' => 'required|numeric|min:1000', // Minimum 1000
                'tanggal_transaksi' => 'required|date|before_or_equal:today',
                'keterangan' => 'nullable|string|max:1000',
                'bukti_transfer' => 'nullable|string|max:255'
            ]);

            $data = $request->all();
            $data['nomor_transaksi'] = 'ZIS-' . date('Ymd') . '-' . Str::random(6);
            $data['status'] = 'pending';
            
            $transaction = ZisTransaction::create($data);
            
            // Broadcast event for real-time notifications
            event(new ZisTransactionCreated($transaction));
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi ZIS berhasil ditambahkan',
                'data' => $transaction->load(['muzakki', 'upz'])
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
                'message' => 'Terjadi kesalahan saat menyimpan transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $transaction = ZisTransaction::with(['muzakki', 'upz', 'verifier'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function update(Request $request, $id)
    {
        $transaction = ZisTransaction::findOrFail($id);
        
        $request->validate([
            'muzakki_id' => 'required|exists:muzakki,id',
            'upz_id' => 'nullable|exists:upz,id',
            'jenis_zis' => 'required|in:zakat,infaq,sedekah',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_transaksi' => 'required|date',
            'keterangan' => 'nullable|string',
            'bukti_transfer' => 'nullable|string'
        ]);

        $transaction->update($request->all());
        return response()->json($transaction->load(['muzakki', 'upz']));
    }

    public function verify(Request $request, $id)
    {
        $transaction = ZisTransaction::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:verified,rejected'
        ]);

        $transaction->update([
            'status' => $request->status,
            'verified_by' => auth()->id(),
            'verified_at' => now()
        ]);

        return response()->json($transaction->load(['muzakki', 'upz', 'verifier']));
    }

    public function destroy($id)
    {
        $transaction = ZisTransaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function report(Request $request)
    {
        $query = ZisTransaction::with(['muzakki', 'upz'])
            ->where('status', 'verified');

        if ($request->start_date) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        if ($request->jenis_zis) {
            $query->where('jenis_zis', $request->jenis_zis);
        }

        $transactions = $query->get();
        $summary = [
            'total_amount' => $transactions->sum('jumlah'),
            'total_transactions' => $transactions->count(),
            'by_type' => $transactions->groupBy('jenis_zis')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'amount' => $items->sum('jumlah')
                ];
            })
        ];

        return response()->json([
            'transactions' => $transactions,
            'summary' => $summary
        ]);
    }
}