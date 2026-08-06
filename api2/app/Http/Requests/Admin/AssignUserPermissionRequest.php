<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class AssignUserPermissionRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('assignPermission', $target);
    }

    public function rules(): array
    {
        return [
            'permission' => [
                'required', 'string', 'max:100',
                Rule::exists('permissions', 'name')->where('guard_name', $this->guardName()),
            ],
        ];
    }
}
