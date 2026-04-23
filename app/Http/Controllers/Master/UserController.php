<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkUnit;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
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
        $roles = Role::all();
        $workUnits = WorkUnit::where('is_active', 1)->get();
        return Inertia::render('Master/User/Create', [
            'roles' => $roles,
            'workUnits' => $workUnits,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'work_unit_id' => 'nullable|exists:work_units,id',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'work_unit_id' => $validated['work_unit_id'] ?? null,
            'is_active' => 1,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('master.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        $workUnits = WorkUnit::where('is_active', 1)->get();
        return Inertia::render('Master/User/Edit', [
            'user' => $user,
            'roles' => $roles,
            'workUnits' => $workUnits,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,name',
            'work_unit_id' => 'nullable|exists:work_units,id',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'work_unit_id' => $validated['work_unit_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('master.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        if ($user->is_active == 1) {
            // Cek tiket aktif jika mau dinonaktifkan
            $activeTickets = Ticket::where('handler_id', $user->id)
                ->whereIn('status', [
                    TicketStatus::InProgress->value,
                    TicketStatus::WaitingForInfo->value,
                    TicketStatus::WaitingThirdParty->value,
                    TicketStatus::PendingApproval->value,
                ])
                ->count();

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
