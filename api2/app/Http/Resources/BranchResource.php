<?php



namespace App\Http\Resources;



use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;



class BranchResource extends JsonResource

{

    public function toArray(Request $request): array

    {

        return [

            'id' => $this->id,

            'name' => $this->name,

            'code' => $this->code,

            'governorate_id' => $this->governorate_id,

            'is_active' => (bool) $this->is_active,

            'manager_user_id' => $this->manager_user_id,

            'notes' => $this->notes,

            'has_dependent_data' => (bool) ($this->has_dependent_data ?? false),

            'users_count' => $this->when(isset($this->users_count), $this->users_count),

            'governorate' => $this->whenLoaded('governorate', fn () => [

                'id' => $this->governorate?->id,

                'name_ar' => $this->governorate?->name_ar,

                'code' => $this->governorate?->code,

            ]),

            'manager' => $this->whenLoaded('manager', fn () => [

                'id' => $this->manager?->id,

                'name' => $this->manager?->name,

                'email' => $this->manager?->email,

            ]),

        ];

    }

}

