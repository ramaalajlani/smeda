<?php

namespace App\Http\Resources\Admin;

use App\Support\AccessControlGuard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'is_protected' => AccessControlGuard::isProtectedRole($this->name),
            'users_count' => $this->when(isset($this->users_count), $this->users_count),
            'permissions_count' => $this->when(isset($this->permissions_count), $this->permissions_count),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
