<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawPermissions = $this->input('permissions', []);
        $assignments = [];

        if (is_array($rawPermissions)) {
            foreach ($rawPermissions as $roleId => $permissionIds) {
                if (!is_numeric($roleId)) {
                    continue;
                }

                $normalizedPermissionIds = collect(is_array($permissionIds) ? $permissionIds : [])
                    ->filter(static fn ($permissionId): bool => is_numeric($permissionId))
                    ->map(static fn ($permissionId): int => (int) $permissionId)
                    ->unique()
                    ->values()
                    ->all();

                $assignments[] = [
                    'role_id' => (int) $roleId,
                    'permission_ids' => $normalizedPermissionIds,
                ];
            }
        }

        $this->merge([
            'assignments' => $assignments,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['array'],
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'assignments.*.permission_ids' => ['array'],
            'assignments.*.permission_ids.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')],
        ];
    }
}
