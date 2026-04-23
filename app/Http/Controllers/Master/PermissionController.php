<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        return Inertia::render('Master/Permission/Index', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        foreach ($request->permissions as $roleId => $permissionIds) {
            $role = Role::findById($roleId);
            if ($role) {
                // permissionIds can be array of ids or array of names, syncPermissions accepts both.
                // assuming the frontend sends array of permission ids
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                $role->syncPermissions($permissions);
            }
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.permissions.index')->with('success', 'Permissions berhasil diupdate.');
    }
}
