<?php

namespace App\Http\Requests\Signing;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserElectronicSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'signature' => [
                'required',
                'file',
                'max:2048',
                'mimes:png,jpg,jpeg,webp',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'signature.required' => 'يرجى اختيار ملف التوقيع.',
            'signature.max' => 'حجم ملف التوقيع يجب ألا يتجاوز 2MB.',
            'signature.mimes' => 'الصيغ المسموحة: png, jpg, jpeg, webp.',
        ];
    }
}
