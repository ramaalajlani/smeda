<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $permission = Permission::query()->find($this->route('id'));

        return $permission && $this->user()?->can('update', $permission);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', $this->guardName())
                    ->ignore($this->route('id')),
            ],
        ];
    }
}
