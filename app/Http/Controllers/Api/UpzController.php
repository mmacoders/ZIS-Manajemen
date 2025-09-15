<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upz;
use Illuminate\Http\Request;

class UpzController extends Controller
{
    public function index(Request $request)
    {
        $query = Upz::with('zisTransactions');
        
        // Add search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }
        
        // Add filter by validation status
        if ($request->has('validasi')) {
            $query->where('validasi', $request->validasi);
        }
        
        // Add filter by deposit type
        if ($request->has('jenis_setoran')) {
            $query->where('jenis_setoran', $request->jenis_setoran);
        }
        
        $upz = $query->paginate(20);
        return response()->json($upz);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|unique:upz,kode',
            'alamat' => 'required|string',
            'pic_nama' => 'required|string',
            'pic_telepon' => 'required|string',
            'status' => 'required|in:aktif,nonaktif',
            'tanggal_setoran' => 'nullable|date',
            'jumlah_setoran' => 'nullable|numeric|min:0',
            'bukti_transfer' => 'nullable|string',
            'jenis_setoran' => 'nullable|in:zakat,infaq,sedekah',
            'validasi' => 'required|in:pending,verified,rejected'
        ]);

        $upz = Upz::create($request->all());
        return response()->json($upz, 201);
    }

    public function show($id)
    {
        $upz = Upz::with('zisTransactions')->findOrFail($id);
        return response()->json($upz);
    }

    public function update(Request $request, $id)
    {
        $upz = Upz::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|unique:upz,kode,' . $id,
            'alamat' => 'required|string',
            'pic_nama' => 'required|string',
            'pic_telepon' => 'required|string',
            'status' => 'required|in:aktif,nonaktif',
            'tanggal_setoran' => 'nullable|date',
            'jumlah_setoran' => 'nullable|numeric|min:0',
            'bukti_transfer' => 'nullable|string',
            'jenis_setoran' => 'nullable|in:zakat,infaq,sedekah',
            'validasi' => 'required|in:pending,verified,rejected'
        ]);

        $upz->update($request->all());
        return response()->json($upz);
    }

    public function destroy($id)
    {
        $upz = Upz::findOrFail($id);
        $upz->delete();
        return response()->json(['message' => 'UPZ deleted successfully']);
    }
}