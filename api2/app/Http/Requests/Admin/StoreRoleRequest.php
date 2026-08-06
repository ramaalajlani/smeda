<?php

namespace App\Http\Requests\Admin;

use App\Support\AccessControlGuard;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')->where('guard_name', $this->guardName()),
                Rule::notIn(AccessControlGuard::PROTECTED_ROLES),
            ],
        ];
    }
}
