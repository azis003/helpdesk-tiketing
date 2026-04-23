<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\WorkUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkUnitController extends Controller
{
    public function index()
    {
        $workUnits = WorkUnit::paginate(10);
        return Inertia::render('Master/WorkUnit/Index', ['workUnits' => $workUnits]);
    }

    public function create()
    {
        return Inertia::render('Master/WorkUnit/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:work_units,code',
        ]);

        WorkUnit::create($validated);

        return redirect()->route('master.work-units.index')->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function edit(WorkUnit $workUnit)
    {
        return Inertia::render('Master/WorkUnit/Edit', ['workUnit' => $workUnit]);
    }

    public function update(Request $request, WorkUnit $workUnit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:work_units,code,' . $workUnit->id,
        ]);

        $workUnit->update($validated);

        return redirect()->route('master.work-units.index')->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function toggleActive(WorkUnit $workUnit)
    {
        $workUnit->update(['is_active' => !$workUnit->is_active]);
        return redirect()->back()->with('success', 'Status unit kerja berhasil diubah.');
    }

    public function members(WorkUnit $workUnit)
    {
        $workUnit->load('members.user');
        $users = User::where('is_active', 1)->get();
        return Inertia::render('Master/WorkUnit/Members', [
            'workUnit' => $workUnit,
            'users' => $users,
        ]);
    }

    public function storeMember(Request $request, WorkUnit $workUnit)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Cek duplicate manual atau pakai syncWithoutDetaching
        $workUnit->members()->syncWithoutDetaching([$request->user_id]);

        // Update work_unit_id di tabel users
        User::where('id', $request->user_id)->update(['work_unit_id' => $workUnit->id]);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke Unit Kerja.');
    }

    public function destroyMember(WorkUnit $workUnit, User $user)
    {
        $workUnit->members()->detach($user->id);
        
        // Opsional: jika ingin mengosongkan work_unit_id di user
        if ($user->work_unit_id == $workUnit->id) {
            $user->update(['work_unit_id' => null]);
        }

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari Unit Kerja.');
    }
}
