<?php



namespace App\Http\Requests\Admin;



use App\Models\User;

use Illuminate\Validation\Rule;



class UpdateAdminUserRequest extends AdminAccessFormRequest

{

    public function authorize(): bool

    {

        $target = User::query()->find($this->route('id'));



        return $target && $this->user()?->can('update', $target);

    }



    public function rules(): array

    {

        $userId = (int) $this->route('id');



        return [

            'name' => ['sometimes', 'string', 'max:255'],

            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],

            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],

            'is_active' => ['sometimes', 'boolean'],

            'governorate_id' => ['sometimes', 'nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],

            'training_center_id' => ['sometimes', 'nullable', 'integer', 'exists:training_centers,id'],

            'training_supervisor_id' => ['sometimes', 'nullable', 'integer', 'exists:training_supervisors,id'],

            'trainer_id' => ['sometimes', 'nullable', 'integer', 'exists:trainers,id'],

            'trainee_id' => ['sometimes', 'nullable', 'integer', 'exists:trainees,id'],

            'password' => ['prohibited'],

            'password_confirmation' => ['prohibited'],

        ];

    }

}

