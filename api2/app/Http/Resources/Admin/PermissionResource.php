<?php

namespace App\Http\Resources\Admin;

use App\Support\PermissionModuleMapper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'module' => PermissionModuleMapper::resolveModule($this->name),
            'roles_count' => $this->when(isset($this->roles_count), $this->roles_count),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
