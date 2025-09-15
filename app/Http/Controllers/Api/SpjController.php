<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spj;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpjController extends Controller
{
    public function index(Request $request)
    {
        $query = Spj::with('program');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_spj', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_penerima', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status_validasi) {
            $query->where('status_validasi', $request->status_validasi);
        }
        
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }
        
        if ($request->start_date) {
            $query->whereDate('tanggal_spj', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->whereDate('tanggal_spj', '<=', $request->end_date);
        }
        
        $spj = $query->paginate(20);
        return response()->json($spj);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'nominal' => 'required|numeric|min:0',
            'tanggal_spj' => 'required|date',
            'status_validasi' => 'required|in:sudah,belum',
            'keterangan' => 'nullable|string',
            'dokumen_spj' => 'nullable|string'
        ]);

        $data = $request->all();
        $data['nomor_spj'] = 'SPJ-' . date('Ymd') . '-' . Str::random(6);
        
        $spj = Spj::create($data);
        return response()->json($spj->load('program'), 201);
    }

    public function show($id)
    {
        $spj = Spj::with('program')->findOrFail($id);
        return response()->json($spj);
    }

    public function update(Request $request, $id)
    {
        $spj = Spj::findOrFail($id);
        
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'nominal' => 'required|numeric|min:0',
            'tanggal_spj' => 'required|date',
            'status_validasi' => 'required|in:sudah,belum',
            'keterangan' => 'nullable|string',
            'dokumen_spj' => 'nullable|string'
        ]);

        $spj->update($request->all());
        return response()->json($spj->load('program'));
    }

    public function destroy($id)
    {
        $spj = Spj::findOrFail($id);
        $spj->delete();
        return response()->json(['message' => 'SPJ deleted successfully']);
    }
}