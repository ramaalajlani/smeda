<?php

namespace App\Http\Requests\Admin;

use App\Models\User;

class UpdateUserStatusRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('updateStatus', $target);
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
