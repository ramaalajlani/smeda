<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * قائمة خفيفة لشجرة الأبناء — بدون getAllPermissions() لكل صف.
 */
class UserAccessListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleNames = $this->relationLoaded('roles')
            ? $this->roles->pluck('name')->sort()->values()
            : collect();

        $directPermissions = $this->relationLoaded('permissions')
            ? $this->permissions->pluck('name')->sort()->values()
            : collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'entity_type' => $this->entity_type,
            'status' => (bool) $this->is_active,
            'is_active' => (bool) $this->is_active,
            'governorate_id' => $this->governorate_id,
            'branch_id' => $this->branch_id,
            'parent_user_id' => $this->parent_user_id,
            'governorate_name' => $this->whenLoaded('governorate', fn () => $this->governorate?->name_ar),
            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name_ar' => $this->governorate?->name_ar,
            ]),
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch?->id,
                'name' => $this->branch?->name,
            ]),
            'roles' => $roleNames->map(fn ($name) => ['name' => $name])->values(),
            'role_names' => $roleNames,
            'permissions' => $directPermissions->map(fn ($name) => ['name' => $name])->values(),
            'direct_permissions' => $directPermissions,
            'children' => $this->whenLoaded('children', fn () => $this->children->map(fn ($c) => [
                'id' => $c->id,
                'parent_user_id' => $c->parent_user_id,
            ])->values()),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
