<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateRolePermissionsRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        
        return Inertia::render('Master/Permission/Index', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            foreach ($validated['permissions'] as $roleId => $permissionIds) {
                $role = Role::query()->find($roleId);

                if (! $role) {
                    continue;
                }

                $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
                $role->syncPermissions($permissions);
            }
        });

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.permissions.index')->with('success', 'Permissions berhasil diupdate.');
    }
}
