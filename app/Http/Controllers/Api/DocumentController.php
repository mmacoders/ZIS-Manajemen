<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('creator')->paginate(20);
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nomor_surat' => 'required|string|unique:documents,nomor_surat|max:100',
                'jenis' => 'required|in:masuk,keluar',
                'asal_tujuan' => 'required|string|max:255',
                'perihal' => 'required|string|max:500',
                'tanggal_surat' => 'required|date',
                'tanggal_diterima' => 'nullable|date|after_or_equal:tanggal_surat',
                'isi_ringkas' => 'nullable|string|max:2000',
                'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // Max 10MB
            ]);

            $data = $request->except('file');
            $data['created_by'] = auth()->id();
            $data['status'] = 'pending';

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validate file size and type
                if ($file->getSize() > 10485760) { // 10MB in bytes
                    return response()->json([
                        'success' => false,
                        'message' => 'Ukuran file tidak boleh lebih dari 10MB'
                    ], 422);
                }
                
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file->getClientOriginalName());
                $path = $file->storeAs('documents', $filename, 'public');
                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_size'] = $file->getSize();
            }

            $document = Document::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditambahkan',
                'data' => $document->load('creator')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan dokumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $document = Document::with('creator')->findOrFail($id);
        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $request->validate([
            'nomor_surat' => 'required|string|unique:documents,nomor_surat,' . $id,
            'jenis' => 'required|in:masuk,keluar',
            'asal_tujuan' => 'required|string',
            'perihal' => 'required|string',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'nullable|date',
            'isi_ringkas' => 'nullable|string',
            'status' => 'required|in:pending,processed,archived',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ]);

        $data = $request->except('file');

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');
            $data['file_path'] = $path;
        }

        $document->update($data);
        return response()->json($document->load('creator'));
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        
        // Delete file if exists
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function search(Request $request)
    {
        try {
            $query = Document::with('creator');

            // Search by document number
            if ($request->nomor_surat) {
                $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
            }

            // Search by subject
            if ($request->perihal) {
                $query->where('perihal', 'like', '%' . $request->perihal . '%');
            }

            // Filter by document type
            if ($request->jenis) {
                $query->where('jenis', $request->jenis);
            }

            // Filter by status
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Search by source/destination
            if ($request->asal_tujuan) {
                $query->where('asal_tujuan', 'like', '%' . $request->asal_tujuan . '%');
            }

            // Date range filter
            if ($request->start_date) {
                $query->whereDate('tanggal_surat', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->whereDate('tanggal_surat', '<=', $request->end_date);
            }

            // Global search
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_surat', 'like', '%' . $search . '%')
                      ->orWhere('perihal', 'like', '%' . $search . '%')
                      ->orWhere('asal_tujuan', 'like', '%' . $search . '%')
                      ->orWhere('isi_ringkas', 'like', '%' . $search . '%');
                });
            }

            // Sorting
            if ($request->sort_by) {
                $direction = $request->sort_direction ?? 'desc';
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('tanggal_surat', 'desc');
            }

            $perPage = $request->per_page ?? 20;
            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari dokumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($document->file_path);
    }
}