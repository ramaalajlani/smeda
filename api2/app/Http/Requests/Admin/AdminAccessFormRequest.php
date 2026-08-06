<?php

namespace App\Http\Requests\Admin;

use App\Support\AccessControlGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class AdminAccessFormRequest extends FormRequest
{
    protected function guardName(): string
    {
        return AccessControlGuard::GUARD;
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'unique' => 'القيمة مستخدمة مسبقاً.',
            'prohibited' => 'حقل :attribute غير مسموح.',
        ];
    }
}
