<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncomingLetterController extends Controller
{
    public function index(Request $request)
    {
        $query = IncomingLetter::with('program');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_agenda', 'like', '%' . $request->search . '%')
                  ->orWhere('asal_surat', 'like', '%' . $request->search . '%')
                  ->orWhere('perihal', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }
        
        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }
        
        $incomingLetters = $query->paginate(20);
        return response()->json($incomingLetters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'asal_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'file_dokumen' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:baru,diproses,selesai',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $data = $request->all();
        $data['nomor_agenda'] = 'IN-' . date('Ymd') . '-' . Str::random(6);
        
        $incomingLetter = IncomingLetter::create($data);
        return response()->json($incomingLetter->load('program'), 201);
    }

    public function show($id)
    {
        $incomingLetter = IncomingLetter::with('program')->findOrFail($id);
        return response()->json($incomingLetter);
    }

    public function update(Request $request, $id)
    {
        $incomingLetter = IncomingLetter::findOrFail($id);
        
        $request->validate([
            'tanggal' => 'required|date',
            'asal_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'file_dokumen' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:baru,diproses,selesai',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $incomingLetter->update($request->all());
        return response()->json($incomingLetter->load('program'));
    }

    public function destroy($id)
    {
        $incomingLetter = IncomingLetter::findOrFail($id);
        $incomingLetter->delete();
        return response()->json(['message' => 'Incoming letter deleted successfully']);
    }
}