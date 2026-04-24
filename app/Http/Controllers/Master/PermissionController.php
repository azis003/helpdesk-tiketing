<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateRolePermissionsRequest;
use App\Support\PermissionCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions:id')
            ->get()
            ->map(function (Role $role): array {
                $roleMeta = PermissionCatalog::roleMeta($role->name);

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $roleMeta['label'],
                    'order' => $roleMeta['order'],
                    'permission_ids' => $role->permissions->pluck('id')->values()->all(),
                ];
            })
            ->sortBy('order')
            ->values();

        $permissions = Permission::query()
            ->get(['id', 'name'])
            ->map(function (Permission $permission): array {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    ...PermissionCatalog::permissionMeta($permission->name),
                ];
            })
            ->sortBy(static fn (array $permission): string => sprintf(
                '%04d-%04d-%s',
                $permission['group_order'],
                $permission['order'],
                $permission['label'],
            ))
            ->values();

        $groups = $this->buildGroups($permissions);

        return Inertia::render('Master/Permission/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'groups' => $groups,
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request)
    {
        $validated = $request->validated();
        $assignments = collect($validated['assignments']);

        DB::transaction(function () use ($assignments): void {
            $rolesById = Role::query()
                ->whereIn('id', $assignments->pluck('role_id')->all())
                ->get()
                ->keyBy('id');

            foreach ($assignments as $assignment) {
                $role = $rolesById->get($assignment['role_id']);
                if (! $role) {
                    continue;
                }

                $role->syncPermissions($assignment['permission_ids']);
            }
        });

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.permissions.index')->with('success', 'Hak akses berhasil diperbarui.');
    }

    /**
     * @param Collection<int, array{id: int, group_key: string, group_label: string, group_order: int}> $permissions
     * @return Collection<int, array{key: string, label: string, order: int}>
     */
    private function buildGroups(Collection $permissions): Collection
    {
        return $permissions
            ->groupBy('group_key')
            ->map(function (Collection $items, string $groupKey): array {
                $first = $items->first();

                return [
                    'key' => $groupKey,
                    'label' => $first['group_label'],
                    'order' => $first['group_order'],
                ];
            })
            ->sortBy('order')
            ->values();
    }
}
