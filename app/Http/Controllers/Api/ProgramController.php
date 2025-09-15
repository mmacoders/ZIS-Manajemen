<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with(['creator', 'distributions'])->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $programs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_program' => 'required|in:distribusi,pemberdayaan',
            'bidang_program' => 'required|in:kemanusiaan,pendidikan_distribusi,dakwah_dan_advokasi,kesehatan_distribusi,ekonomi_produk,pendidikan_pemberdayaan,kesehatan_pemberdayaan',
            'deskripsi' => 'required|string',
            'target_dana' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:draft,aktif,selesai,batal'
        ]);

        $data = $request->all();
        // Using Auth facade directly for better Intelephense compatibility
        $data['created_by'] = Auth::id();
        
        $program = Program::create($data);
        return response()->json([
            'success' => true,
            'data' => $program->load('creator')
        ], 201);
    }

    public function show($id)
    {
        $program = Program::with(['creator', 'distributions.mustahiq'])->find($id);
        
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }

    public function update(Request $request, $id)
    {
        $program = Program::find($id);
        
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found'
            ], 404);
        }
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_program' => 'required|in:distribusi,pemberdayaan',
            'bidang_program' => 'required|in:kemanusiaan,pendidikan_distribusi,dakwah_dan_advokasi,kesehatan_distribusi,ekonomi_produk,pendidikan_pemberdayaan,kesehatan_pemberdayaan',
            'deskripsi' => 'required|string',
            'target_dana' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:draft,aktif,selesai,batal'
        ]);

        $program->update($request->all());
        return response()->json([
            'success' => true,
            'data' => $program->load('creator')
        ]);
    }

    public function destroy($id)
    {
        $program = Program::find($id);
        
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found'
            ], 404);
        }
        
        // Check if program has any distributions
        if ($program->distributions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus program yang memiliki distribusi'
            ], 422);
        }
        
        $program->delete();
        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully'
        ]);
    }
}