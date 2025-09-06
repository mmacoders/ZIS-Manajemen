<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upz;
use Illuminate\Http\Request;

class UpzController extends Controller
{
    public function index()
    {
        $upz = Upz::with('zisTransactions')->paginate(20);
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
            'status' => 'required|in:aktif,nonaktif'
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
            'status' => 'required|in:aktif,nonaktif'
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