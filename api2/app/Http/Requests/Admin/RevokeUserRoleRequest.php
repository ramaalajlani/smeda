<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;

class RevokeUserRoleRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('revokeRole', $target);
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
