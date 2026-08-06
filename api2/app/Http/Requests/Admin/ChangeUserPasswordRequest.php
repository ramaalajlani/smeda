<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rules\Password;

class ChangeUserPasswordRequest extends AdminAccessFormRequest
{
    public function authorize(): bool
    {
        $target = User::query()->find($this->route('id'));

        return $target && $this->user()?->can('changePassword', $target);
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
