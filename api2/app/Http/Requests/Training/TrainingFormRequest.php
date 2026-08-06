<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

abstract class TrainingFormRequest extends FormRequest
{
    protected function userHasPermission(string $permission): bool
    {
        $user = $this->user();

        return $user !== null
            && method_exists($user, 'hasPermissionTo')
            && $user->hasPermissionTo($permission);
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'integer' => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
            'exists' => 'القيمة المحددة في :attribute غير صالحة.',
            'in' => 'القيمة المحددة في :attribute غير مسموحة.',
            'prohibited' => 'حقل :attribute غير مسموح إرساله.',
            'email' => 'يجب إدخال بريد إلكتروني صالح.',
            'date' => 'يجب إدخال تاريخ صالح.',
            'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
            'min' => 'حقل :attribute يجب ألا يقل عن :min.',
            'max' => 'حقل :attribute يجب ألا يزيد عن :max.',
            'after_or_equal' => 'يجب أن يكون :attribute بعد أو يساوي :date.',
            'url' => 'يجب إدخال رابط صالح.',
            'file' => 'يجب رفع ملف صالح.',
            'mimes' => 'نوع الملف غير مدعوم.',
        ];
    }
}
