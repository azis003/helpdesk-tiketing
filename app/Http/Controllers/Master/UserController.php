<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreUserRequest;
use App\Http\Requests\Master\UpdateUserRequest;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'workUnit']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Master/User/Index', [
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->get();
        $workUnits = WorkUnit::query()->active()->orderBy('name')->get();

        return Inertia::render('Master/User/Create', [
            'roles' => $roles,
            'workUnits' => $workUnits,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $user = User::query()->create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'password' => $validated['password'],
                'work_unit_id' => $validated['work_unit_id'] ?? null,
                'is_active' => true,
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()->route('master.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::query()->orderBy('name')->get();
        $workUnits = WorkUnit::query()->active()->orderBy('name')->get();

        return Inertia::render('Master/User/Edit', [
            'user' => $user,
            'roles' => $roles,
            'workUnits' => $workUnits,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'work_unit_id' => $validated['work_unit_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        DB::transaction(function () use ($user, $data, $validated): void {
            $user->update($data);
            $user->syncRoles([$validated['role']]);
        });

        return redirect()->route('master.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        if ($user->is_active) {
            $activeTickets = $user->activeAssignedTickets()->count();

            if ($activeTickets > 0) {
                return redirect()->back()->withErrors([
                    'is_active' => "User ini masih punya {$activeTickets} tiket aktif. Reassign tiket terlebih dahulu."
                ]);
            }
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back()->with('success', 'Status user berhasil diubah.');
    }
}
