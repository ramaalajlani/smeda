<?php



namespace App\Http\Requests\Admin;



use App\Models\User;

use App\Support\AccessControlGuard;

use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules\Password;



class StoreAdminUserRequest extends AdminAccessFormRequest

{

    public function authorize(): bool

    {

        return $this->user()?->can('create', User::class) ?? false;

    }



    public function rules(): array

    {

        return [

            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email', 'max:255', 'unique:users,email'],

            'phone' => ['nullable', 'string', 'max:30'],

            'password' => ['required', 'confirmed', Password::min(8)],

            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', AccessControlGuard::GUARD)],

            'is_active' => ['sometimes', 'boolean'],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id', 'required_if:role,branch_manager'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id', 'required_if:role,branch_manager'],

            'training_center_id' => ['nullable', 'integer', 'exists:training_centers,id'],

            'training_supervisor_id' => ['nullable', 'integer', 'exists:training_supervisors,id'],

            'trainer_id' => ['nullable', 'integer', 'exists:trainers,id'],

            'trainee_id' => ['nullable', 'integer', 'exists:trainees,id'],

            'permissions' => ['sometimes', 'array'],

            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', AccessControlGuard::GUARD)],

        ];

    }

}

