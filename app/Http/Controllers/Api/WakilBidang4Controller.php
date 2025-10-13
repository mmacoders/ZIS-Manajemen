<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use App\Models\Asset;
use App\Models\Document;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WakilBidang4Controller extends Controller
{
    public function dashboard()
    {
        $data = [
            'summary' => $this->getSummary(),
            'compliance_metrics' => $this->getComplianceMetrics(),
            'asset_management' => $this->getAssetManagement(),
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json($data);
    }

    private function getSummary()
    {
        return [
            'total_staff' => Staff::count(),
            'active_staff' => Staff::where('status', 'aktif')->count(),
            'total_assets' => Asset::count(),
            'total_documents' => Document::count(),
            'pending_documents' => Document::where('status', 'pending')->count(),
        ];
    }

    private function getComplianceMetrics()
    {
        $totalLetters = IncomingLetter::count() + OutgoingLetter::count();
        $processedLetters = IncomingLetter::where('status', 'processed')->count() + 
                           OutgoingLetter::where('status', 'sent')->count();
        
        // SOP compliance would be tracked through document validation
        $validatedDocuments = Document::where('status', 'validated')->count();
        $totalDocuments = Document::count();
        
        return [
            'letter_management' => [
                'total_letters' => $totalLetters,
                'processed_letters' => $processedLetters,
                'processing_rate' => $totalLetters > 0 ? round(($processedLetters / $totalLetters) * 100, 2) : 0,
            ],
            'document_compliance' => [
                'total_documents' => $totalDocuments,
                'validated_documents' => $validatedDocuments,
                'compliance_rate' => $totalDocuments > 0 ? round(($validatedDocuments / $totalDocuments) * 100, 2) : 0,
            ],
        ];
    }

    private function getAssetManagement()
    {
        // Asset by status
        $assetsByStatus = Asset::selectRaw('status, COUNT(*) as count, SUM(nilai) as total_value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
            
        // Asset by category
        $assetsByCategory = Asset::selectRaw('kategori, COUNT(*) as count, SUM(nilai) as total_value')
            ->groupBy('kategori')
            ->get();

        return [
            'total_assets' => Asset::count(),
            'total_asset_value' => Asset::sum('nilai'),
            'assets_by_status' => $assetsByStatus,
            'assets_by_category' => $assetsByCategory,
        ];
    }

    private function getRecentActivities()
    {
        // Combine different activities
        $activities = [];
        
        // Recent staff activities
        $staffActivities = Staff::latest()->take(2)->get()->map(function($staff) {
            return [
                'id' => $staff->id,
                'type' => 'staff',
                'description' => "Staff baru: {$staff->nama}",
                'position' => $staff->jabatan,
                'date' => $staff->created_at,
            ];
        });
        
        // Recent letter activities
        $letterActivities = IncomingLetter::latest()->take(3)->get()->map(function($letter) {
            return [
                'id' => $letter->id,
                'type' => 'incoming_letter',
                'description' => "Surat masuk: {$letter->nomor_surat}",
                'subject' => $letter->perihal,
                'date' => $letter->tanggal_surat,
            ];
        });
        
        // Recent asset activities
        $assetActivities = Asset::latest()->take(3)->get()->map(function($asset) {
            return [
                'id' => $asset->id,
                'type' => 'asset',
                'description' => "Aset baru: {$asset->nama}",
                'value' => $asset->nilai,
                'date' => $asset->tanggal_perolehan,
            ];
        });
        
        // Recent document activities
        $documentActivities = Document::latest()->take(2)->get()->map(function($document) {
            return [
                'id' => $document->id,
                'type' => 'document',
                'description' => "Dokumen: {$document->judul}",
                'status' => $document->status,
                'date' => $document->created_at,
            ];
        });
        
        $activities = collect(array_merge(
            $staffActivities->toArray(),
            $letterActivities->toArray(),
            $assetActivities->toArray(),
            $documentActivities->toArray()
        ))->sortByDesc('date')->take(10)->values();
        
        return $activities;
    }
}