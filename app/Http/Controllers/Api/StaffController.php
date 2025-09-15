<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('jabatan', 'like', '%' . $request->search . '%')
                  ->orWhere('bidang', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->bidang) {
            $query->where('bidang', $request->bidang);
        }
        
        if ($request->jabatan) {
            $query->where('jabatan', $request->jabatan);
        }
        
        $staff = $query->paginate(20);
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:100',
            'bidang' => 'required|string|max:100',
            'periode' => 'required|string|max:50',
            'nip' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'keterangan' => 'nullable|string'
        ]);

        $staff = Staff::create($request->all());
        return response()->json($staff, 201);
    }

    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        return response()->json($staff);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:100',
            'bidang' => 'required|string|max:100',
            'periode' => 'required|string|max:50',
            'nip' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'keterangan' => 'nullable|string'
        ]);

        $staff->update($request->all());
        return response()->json($staff);
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        return response()->json(['message' => 'Staff deleted successfully']);
    }
}