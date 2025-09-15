<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('rkat');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_aset', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_aset', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->kondisi) {
            $query->where('kondisi', $request->kondisi);
        }
        
        if ($request->rkat_id) {
            $query->where('rkat_id', $request->rkat_id);
        }
        
        $assets = $query->paginate(20);
        return response()->json($assets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_aset' => 'required|string|max:255',
            'kode_aset' => 'required|string|max:100',
            'tahun_pengadaan' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kondisi' => 'required|in:baik,rusak,perlu_perbaikan',
            'lokasi' => 'required|string|max:255',
            'nilai_aset' => 'required|numeric|min:0',
            'rkat_id' => 'nullable|exists:rkat,id',
            'keterangan' => 'nullable|string'
        ]);

        $asset = Asset::create($request->all());
        return response()->json($asset->load('rkat'), 201);
    }

    public function show($id)
    {
        $asset = Asset::with('rkat')->findOrFail($id);
        return response()->json($asset);
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        
        $request->validate([
            'nama_aset' => 'required|string|max:255',
            'kode_aset' => 'required|string|max:100',
            'tahun_pengadaan' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kondisi' => 'required|in:baik,rusak,perlu_perbaikan',
            'lokasi' => 'required|string|max:255',
            'nilai_aset' => 'required|numeric|min:0',
            'rkat_id' => 'nullable|exists:rkat,id',
            'keterangan' => 'nullable|string'
        ]);

        $asset->update($request->all());
        return response()->json($asset->load('rkat'));
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();
        return response()->json(['message' => 'Asset deleted successfully']);
    }
}