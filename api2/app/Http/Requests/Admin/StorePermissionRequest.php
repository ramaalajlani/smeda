<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class StorePermissionRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Permission::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('permissions', 'name')->where('guard_name', $this->guardName()),
            ],
        ];
    }
}
