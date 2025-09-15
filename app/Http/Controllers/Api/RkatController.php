<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rkat;
use Illuminate\Http\Request;

class RkatController extends Controller
{
    public function index(Request $request)
    {
        $query = Rkat::with('program');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_program', 'like', '%' . $request->search . '%')
                  ->orWhere('bidang', 'like', '%' . $request->search . '%')
                  ->orWhere('jenis_kegiatan', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->bidang) {
            $query->where('bidang', $request->bidang);
        }
        
        $rkat = $query->paginate(20);
        return response()->json($rkat);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_urut' => 'required|string|max:50',
            'bidang' => 'required|string|max:100',
            'nama_program' => 'required|string|max:255',
            'jenis_kegiatan' => 'required|string|max:255',
            'volume' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $data = $request->all();
        $data['jumlah'] = $request->volume * $request->harga_satuan;
        
        $rkat = Rkat::create($data);
        return response()->json($rkat->load('program'), 201);
    }

    public function show($id)
    {
        $rkat = Rkat::with('program')->findOrFail($id);
        return response()->json($rkat);
    }

    public function update(Request $request, $id)
    {
        $rkat = Rkat::findOrFail($id);
        
        $request->validate([
            'nomor_urut' => 'required|string|max:50',
            'bidang' => 'required|string|max:100',
            'nama_program' => 'required|string|max:255',
            'jenis_kegiatan' => 'required|string|max:255',
            'volume' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $data = $request->all();
        $data['jumlah'] = $request->volume * $request->harga_satuan;
        
        $rkat->update($data);
        return response()->json($rkat->load('program'));
    }

    public function destroy($id)
    {
        $rkat = Rkat::findOrFail($id);
        $rkat->delete();
        return response()->json(['message' => 'RKAT deleted successfully']);
    }
}