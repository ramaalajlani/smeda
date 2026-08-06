<?php



namespace App\Http\Requests\Admin;



use App\Models\User;

use App\Support\AccessControlGuard;

use Illuminate\Validation\Rule;

use Illuminate\Validation\Validator;



class SyncUserRolesRequest extends AdminAccessFormRequest

{

    public function authorize(): bool

    {

        $target = User::query()->find($this->route('id'));



        return $target

            && (int) $this->user()?->id !== (int) $target->id

            && $this->user()?->can('assignRole', $target);

    }



    public function rules(): array

    {

        return [

            'roles' => ['required', 'array', 'min:1'],

            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', AccessControlGuard::GUARD)],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],

        ];

    }



    public function withValidator(Validator $validator): void

    {

        $validator->after(function (Validator $v) {

            $roles = $this->input('roles', []);

            if (in_array('branch_manager', $roles, true)) {

                if (!$this->input('branch_id') || !$this->input('governorate_id')) {

                    $v->errors()->add('branch_id', 'يجب اختيار المحافظة والفرع عند تعيين دور مدير الفرع.');

                }

            }

        });

    }

}

