<?php



namespace App\Http\Resources\Admin;



use App\Support\AccessControlGuard;

use App\Support\PermissionModuleMapper;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;



class UserAccessResource extends JsonResource

{

    public function toArray(Request $request): array

    {

        $roleNames = $this->roles->pluck('name')->sort()->values();

        $directPermissions = $this->permissions->pluck('name')->sort()->values();

        $allPermissions = $this->getAllPermissions()->pluck('name')->sort()->values();



        return [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'entity_type' => $this->entity_type,

            'status' => (bool) $this->is_active,

            'is_active' => (bool) $this->is_active,

            'training_center_id' => $this->training_center_id,

            'training_supervisor_id' => $this->training_supervisor_id,

            'center_id' => $this->training_center_id,

            'trainer_id' => $this->trainer_id,

            'trainee_id' => $this->trainee_id,

            'governorate_id' => $this->governorate_id,

            'branch_id' => $this->branch_id,

            'governorate_name' => $this->whenLoaded('governorate', fn () => $this->governorate?->name_ar),

            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),

            'training_center' => $this->whenLoaded('trainingCenter', fn () => [

                'id' => $this->trainingCenter?->id,

                'name' => $this->trainingCenter?->name,

            ]),

            'training_supervisor' => $this->whenLoaded('trainingSupervisor', fn () => [
                'id' => $this->trainingSupervisor?->id,
                'name' => $this->trainingSupervisor?->name,
                'code' => $this->trainingSupervisor?->code,
                'type' => $this->trainingSupervisor?->type,
            ]),

            'governorate' => $this->whenLoaded('governorate', fn () => [

                'id' => $this->governorate?->id,

                'name_ar' => $this->governorate?->name_ar,

            ]),

            'branch' => $this->whenLoaded('branch', fn () => [

                'id' => $this->branch?->id,

                'name' => $this->branch?->name,

            ]),

            'last_login_at' => optional($this->last_login_at)?->toIso8601String(),

            'is_admin' => AccessControlGuard::userHasAdminRole($this->resource),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            'role_names' => $roleNames,

            'permissions_count' => $allPermissions->count(),

            'direct_permissions' => $directPermissions,

            'effective_permissions' => $allPermissions,

            'permissions_by_module' => PermissionModuleMapper::group($allPermissions),

            'created_at' => optional($this->created_at)?->toIso8601String(),

        ];

    }

}

