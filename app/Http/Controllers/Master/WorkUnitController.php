<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreWorkUnitMemberRequest;
use App\Http\Requests\Master\StoreWorkUnitRequest;
use App\Http\Requests\Master\UpdateWorkUnitRequest;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WorkUnitController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\WorkUnit::withCount('members');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $workUnits = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Master/WorkUnit/Index', [
            'workUnits' => $workUnits,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/WorkUnit/Create');
    }

    public function store(StoreWorkUnitRequest $request)
    {
        WorkUnit::query()->create($request->validated());

        return redirect()->route('master.work-units.index')->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function edit(WorkUnit $workUnit)
    {
        return Inertia::render('Master/WorkUnit/Edit', ['workUnit' => $workUnit]);
    }

    public function update(UpdateWorkUnitRequest $request, WorkUnit $workUnit)
    {
        $workUnit->update($request->validated());

        return redirect()->route('master.work-units.index')->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function toggleActive(WorkUnit $workUnit)
    {
        $workUnit->update(['is_active' => !$workUnit->is_active]);
        return redirect()->back()->with('success', 'Status unit kerja berhasil diubah.');
    }

    public function members(WorkUnit $workUnit)
    {
        $workUnit->load(['members' => fn ($query) => $query->orderBy('name')]);
        $users = User::query()
            ->active()
            ->whereNull('work_unit_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Master/WorkUnit/Members', [
            'workUnit' => $workUnit,
            'users' => $users,
        ]);
    }

    public function storeMember(StoreWorkUnitMemberRequest $request, WorkUnit $workUnit)
    {
        $user = User::query()->findOrFail($request->validated()['user_id']);

        if ($user->work_unit_id !== null) {
            return redirect()->back()->withErrors([
                'user_id' => 'Pengguna sudah terdaftar pada unit kerja lain.',
            ]);
        }

        DB::transaction(function () use ($user, $workUnit): void {
            $user->update(['work_unit_id' => $workUnit->id]);
        });

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke Unit Kerja.');
    }

    public function destroyMember(WorkUnit $workUnit, User $user)
    {
        if ($user->work_unit_id !== $workUnit->id) {
            return redirect()->back()->withErrors([
                'user_id' => 'Pengguna ini bukan anggota dari unit kerja tersebut.',
            ]);
        }

        $user->update(['work_unit_id' => null]);

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari Unit Kerja.');
    }
}
