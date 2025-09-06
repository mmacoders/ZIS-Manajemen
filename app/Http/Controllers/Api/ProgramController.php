<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with(['creator', 'distributions'])->paginate(20);
        return response()->json($programs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'target_dana' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status' => 'required|in:draft,aktif,selesai,batal'
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();
        
        $program = Program::create($data);
        return response()->json($program->load('creator'), 201);
    }

    public function show($id)
    {
        $program = Program::with(['creator', 'distributions.mustahiq'])->findOrFail($id);
        return response()->json($program);
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'target_dana' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status' => 'required|in:draft,aktif,selesai,batal'
        ]);

        $program->update($request->all());
        return response()->json($program->load('creator'));
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();
        return response()->json(['message' => 'Program deleted successfully']);
    }
}