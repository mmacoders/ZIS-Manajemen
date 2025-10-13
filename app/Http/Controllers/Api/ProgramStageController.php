<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgramStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramStageController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgramStage::with(['program', 'approver']);
        
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        
        $stages = $query->orderBy('order')->get();
        
        return response()->json($stages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'stage_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $stage = new ProgramStage();
        $stage->program_id = $request->program_id;
        $stage->stage_name = $request->stage_name;
        $stage->description = $request->description;
        $stage->order = $request->order;
        $stage->status = 'pending';
        $stage->save();

        return response()->json($stage, 201);
    }

    public function update(Request $request, ProgramStage $stage)
    {
        $request->validate([
            'stage_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer',
            'status' => 'sometimes|string|in:pending,approved,rejected',
        ]);

        // If trying to approve and stage is locked, deny
        if ($request->status === 'approved' && $stage->is_locked) {
            return response()->json(['error' => 'Cannot approve locked stage'], 400);
        }

        if ($request->has('stage_name')) {
            $stage->stage_name = $request->stage_name;
        }
        
        if ($request->has('description')) {
            $stage->description = $request->description;
        }
        
        if ($request->has('order')) {
            $stage->order = $request->order;
        }
        
        if ($request->has('status')) {
            $stage->status = $request->status;
            
            if ($request->status === 'approved') {
                $stage->approved_by = Auth::id();
                $stage->approved_at = now();
            }
        }
        
        $stage->save();

        return response()->json($stage);
    }

    public function lockStage(ProgramStage $stage)
    {
        // Only allow locking if stage is approved
        if (!$stage->isApproved()) {
            return response()->json(['error' => 'Only approved stages can be locked'], 400);
        }

        $stage->is_locked = true;
        $stage->save();

        return response()->json(['message' => 'Stage locked successfully']);
    }

    public function unlockStage(ProgramStage $stage)
    {
        $stage->is_locked = false;
        $stage->save();

        return response()->json(['message' => 'Stage unlocked successfully']);
    }
}