<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OutgoingLetterController extends Controller
{
    public function index(Request $request)
    {
        $query = OutgoingLetter::with('program');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_surat', 'like', '%' . $request->search . '%')
                  ->orWhere('tujuan', 'like', '%' . $request->search . '%')
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
        
        $outgoingLetters = $query->paginate(20);
        return response()->json($outgoingLetters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'file_dokumen' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,dikirim,diterima',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $data = $request->all();
        $data['nomor_surat'] = 'OUT-' . date('Ymd') . '-' . Str::random(6);
        
        $outgoingLetter = OutgoingLetter::create($data);
        return response()->json($outgoingLetter->load('program'), 201);
    }

    public function show($id)
    {
        $outgoingLetter = OutgoingLetter::with('program')->findOrFail($id);
        return response()->json($outgoingLetter);
    }

    public function update(Request $request, $id)
    {
        $outgoingLetter = OutgoingLetter::findOrFail($id);
        
        $request->validate([
            'tanggal' => 'required|date',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'file_dokumen' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,dikirim,diterima',
            'program_id' => 'nullable|exists:programs,id'
        ]);

        $outgoingLetter->update($request->all());
        return response()->json($outgoingLetter->load('program'));
    }

    public function destroy($id)
    {
        $outgoingLetter = OutgoingLetter::findOrFail($id);
        $outgoingLetter->delete();
        return response()->json(['message' => 'Outgoing letter deleted successfully']);
    }
}