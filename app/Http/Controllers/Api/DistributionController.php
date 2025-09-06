<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Program;
use App\Events\DistributionCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistributionController extends Controller
{
    public function index()
    {
        $distributions = Distribution::with(['program', 'mustahiq', 'distributor'])->paginate(20);
        return response()->json($distributions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'mustahiq_id' => 'required|exists:mustahiq,id',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_distribusi' => 'required|date',
            'keterangan' => 'nullable|string',
            'bukti_distribusi' => 'nullable|string'
        ]);

        $data = $request->all();
        $data['nomor_distribusi'] = 'DIST-' . date('Ymd') . '-' . Str::random(6);
        $data['distributed_by'] = auth()->id();
        
        $distribution = Distribution::create($data);

        // Update program dana terkumpul
        $program = Program::find($request->program_id);
        $program->increment('dana_terkumpul', $request->jumlah);

        return response()->json($distribution->load(['program', 'mustahiq', 'distributor']), 201);
    }

    public function show($id)
    {
        $distribution = Distribution::with(['program', 'mustahiq', 'distributor'])->findOrFail($id);
        return response()->json($distribution);
    }

    public function update(Request $request, $id)
    {
        $distribution = Distribution::findOrFail($id);
        $oldAmount = $distribution->jumlah;
        
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'mustahiq_id' => 'required|exists:mustahiq,id',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_distribusi' => 'required|date',
            'keterangan' => 'nullable|string',
            'bukti_distribusi' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled'
        ]);

        $distribution->update($request->all());

        // Trigger event if status changed to completed
        if ($request->status === 'completed' && $distribution->status !== 'completed') {
            event(new DistributionCompleted($distribution));
        }

        // Update program dana terkumpul if amount changed
        if ($oldAmount != $request->jumlah) {
            $program = Program::find($request->program_id);
            $program->decrement('dana_terkumpul', $oldAmount);
            $program->increment('dana_terkumpul', $request->jumlah);
        }

        return response()->json($distribution->load(['program', 'mustahiq', 'distributor']));
    }

    public function destroy($id)
    {
        $distribution = Distribution::findOrFail($id);
        
        // Decrease program dana terkumpul
        $program = Program::find($distribution->program_id);
        $program->decrement('dana_terkumpul', $distribution->jumlah);
        
        $distribution->delete();
        return response()->json(['message' => 'Distribution deleted successfully']);
    }

    public function report(Request $request)
    {
        $query = Distribution::with(['program', 'mustahiq'])
            ->where('status', 'completed');

        if ($request->start_date) {
            $query->whereDate('tanggal_distribusi', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('tanggal_distribusi', '<=', $request->end_date);
        }

        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        $distributions = $query->get();
        $summary = [
            'total_amount' => $distributions->sum('jumlah'),
            'total_distributions' => $distributions->count(),
            'by_program' => $distributions->groupBy('program.nama')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'amount' => $items->sum('jumlah')
                ];
            })
        ];

        return response()->json([
            'distributions' => $distributions,
            'summary' => $summary
        ]);
    }
}