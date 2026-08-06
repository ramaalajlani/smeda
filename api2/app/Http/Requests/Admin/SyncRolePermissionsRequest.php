<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $role = Role::query()->find($this->route('id'));

        return $role && $this->user()?->can('syncPermissions', $role);
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:0'],
            'permissions.*' => [
                'string', 'max:100',
                Rule::exists('permissions', 'name')->where('guard_name', $this->guardName()),
            ],
        ];
    }
}
