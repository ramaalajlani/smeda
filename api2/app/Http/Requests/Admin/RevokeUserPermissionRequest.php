<?php

namespace App\Http\Requests\Admin;

use App\Models\User;

class RevokeUserPermissionRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('revokePermission', $target);
    }

    public function rules(): array
    {
        return [];
    }
}
