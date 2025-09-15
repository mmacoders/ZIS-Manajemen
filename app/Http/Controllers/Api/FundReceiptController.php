<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FundReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = FundReceipt::with(['muzakki', 'upz']);
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_bukti', 'like', '%' . $request->search . '%')
                  ->orWhere('sumber_dana', 'like', '%' . $request->search . '%')
                  ->orWhere('jenis_dana', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status_penerimaan) {
            $query->where('status_penerimaan', $request->status_penerimaan);
        }
        
        if ($request->start_date) {
            $query->whereDate('tanggal_setor', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->whereDate('tanggal_setor', '<=', $request->end_date);
        }
        
        $fundReceipts = $query->paginate(20);
        return response()->json($fundReceipts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'status_penerimaan' => 'required|in:diterima,ditolak,pending',
            'tanggal_setor' => 'required|date',
            'sumber_dana' => 'required|string|max:255',
            'jumlah_setor' => 'required|numeric|min:0',
            'jenis_dana' => 'required|string|max:100',
            'muzakki_id' => 'nullable|exists:muzakki,id',
            'upz_id' => 'nullable|exists:upz,id',
            'keterangan' => 'nullable|string',
            'bukti_transfer' => 'nullable|string'
        ]);

        $data = $request->all();
        $data['nomor_bukti'] = 'FR-' . date('Ymd') . '-' . Str::random(6);
        
        $fundReceipt = FundReceipt::create($data);
        return response()->json($fundReceipt->load(['muzakki', 'upz']), 201);
    }

    public function show($id)
    {
        $fundReceipt = FundReceipt::with(['muzakki', 'upz'])->findOrFail($id);
        return response()->json($fundReceipt);
    }

    public function update(Request $request, $id)
    {
        $fundReceipt = FundReceipt::findOrFail($id);
        
        $request->validate([
            'status_penerimaan' => 'required|in:diterima,ditolak,pending',
            'tanggal_setor' => 'required|date',
            'sumber_dana' => 'required|string|max:255',
            'jumlah_setor' => 'required|numeric|min:0',
            'jenis_dana' => 'required|string|max:100',
            'muzakki_id' => 'nullable|exists:muzakki,id',
            'upz_id' => 'nullable|exists:upz,id',
            'keterangan' => 'nullable|string',
            'bukti_transfer' => 'nullable|string'
        ]);

        $fundReceipt->update($request->all());
        return response()->json($fundReceipt->load(['muzakki', 'upz']));
    }

    public function destroy($id)
    {
        $fundReceipt = FundReceipt::findOrFail($id);
        $fundReceipt->delete();
        return response()->json(['message' => 'Fund receipt deleted successfully']);
    }
}