<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AssignUserRoleRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('assignRole', $target);
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required', 'string', 'max:100',
                Rule::exists('roles', 'name')->where('guard_name', $this->guardName()),
            ],
        ];
    }
}
