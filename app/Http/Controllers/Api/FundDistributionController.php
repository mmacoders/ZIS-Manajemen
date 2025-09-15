<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FundDistributionController extends Controller
{
    public function index(Request $request)
    {
        $query = FundDistribution::with('program');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_bukti', 'like', '%' . $request->search . '%')
                  ->orWhere('bidang_program', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }
        
        if ($request->start_date) {
            $query->whereDate('tanggal_penyaluran', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->whereDate('tanggal_penyaluran', '<=', $request->end_date);
        }
        
        $fundDistributions = $query->paginate(20);
        return response()->json($fundDistributions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'bidang_program' => 'required|string|max:100',
            'anggaran_dialokasikan' => 'required|numeric|min:0',
            'nominal_bantuan' => 'required|numeric|min:0',
            'tanggal_penyaluran' => 'required|date',
            'bukti_penyaluran' => 'nullable|string',
            'status' => 'required|in:pending,disetujui,ditolak',
            'keterangan' => 'nullable|string'
        ]);

        $data = $request->all();
        $data['nomor_bukti'] = 'FD-' . date('Ymd') . '-' . Str::random(6);
        
        $fundDistribution = FundDistribution::create($data);
        return response()->json($fundDistribution->load('program'), 201);
    }

    public function show($id)
    {
        $fundDistribution = FundDistribution::with('program')->findOrFail($id);
        return response()->json($fundDistribution);
    }

    public function update(Request $request, $id)
    {
        $fundDistribution = FundDistribution::findOrFail($id);
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'bidang_program' => 'required|string|max:100',
            'anggaran_dialokasikan' => 'required|numeric|min:0',
            'nominal_bantuan' => 'required|numeric|min:0',
            'tanggal_penyaluran' => 'required|date',
            'bukti_penyaluran' => 'nullable|string',
            'status' => 'required|in:pending,disetujui,ditolak',
            'keterangan' => 'nullable|string'
        ]);

        $fundDistribution->update($request->all());
        return response()->json($fundDistribution->load('program'));
    }

    public function destroy($id)
    {
        $fundDistribution = FundDistribution::findOrFail($id);
        $fundDistribution->delete();
        return response()->json(['message' => 'Fund distribution deleted successfully']);
    }
}