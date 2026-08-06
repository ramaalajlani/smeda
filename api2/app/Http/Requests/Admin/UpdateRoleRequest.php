<?php

namespace App\Http\Requests\Admin;

use App\Support\AccessControlGuard;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $role = Role::query()->find($this->route('id'));

        return $role && $this->user()?->can('update', $role);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $this->guardName())
                    ->ignore($this->route('id')),
                Rule::notIn(AccessControlGuard::PROTECTED_ROLES),
            ],
        ];
    }
}
