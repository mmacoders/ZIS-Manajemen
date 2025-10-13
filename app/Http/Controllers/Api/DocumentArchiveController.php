<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentArchive::with(['document', 'archivedBy']);
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('document_id')) {
            $query->where('document_id', $request->document_id);
        }
        
        $archives = $query->orderBy('archived_at', 'desc')->paginate(20);
        
        return response()->json($archives);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'file' => 'required|file',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $document = \App\Models\Document::findOrFail($request->document_id);
        
        // Store the file
        $file = $request->file('file');
        $filePath = $file->store('document_archives', 'public');
        
        // Create archive record
        $archive = new DocumentArchive();
        $archive->document_id = $request->document_id;
        $archive->file_path = $filePath;
        $archive->file_name = $file->getClientOriginalName();
        $archive->file_size = $file->getSize();
        $archive->file_type = $file->getMimeType();
        $archive->archived_by = auth()->id();
        $archive->archived_at = now();
        $archive->description = $request->description;
        $archive->category = $request->category;
        $archive->tags = $request->tags;
        $archive->version = DocumentArchive::where('document_id', $request->document_id)->max('version') + 1;
        $archive->save();
        
        return response()->json($archive, 201);
    }

    public function download(DocumentArchive $archive)
    {
        if (!Storage::disk('public')->exists($archive->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        return response()->download(
            Storage::disk('public')->path($archive->file_path),
            $archive->file_name
        );
    }

    public function destroy(DocumentArchive $archive)
    {
        // Delete the file
        if (Storage::disk('public')->exists($archive->file_path)) {
            Storage::disk('public')->delete($archive->file_path);
        }
        
        // Delete the archive record
        $archive->delete();
        
        return response()->json(['message' => 'Archive deleted successfully']);
    }
}