<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\AccessControlGuard;
use Illuminate\Validation\Rule;

class SyncUserPermissionsRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target
            && (int) $this->user()?->id !== (int) $target->id
            && $this->user()?->can('assignPermission', $target);
    }

    public function rules(): array
    {
        return [
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', AccessControlGuard::GUARD)],
        ];
    }
}
