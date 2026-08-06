<?php

namespace App\Http\Resources\Concerns;

trait ExposesBranchScopeFields
{
    /**
     * @return array<string, mixed>
     */
    protected function branchScopeFields(): array
    {
        return [
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'governorate_id' => $this->governorate_id,
            'governorate_name' => $this->governorate?->name_ar,
        ];
    }
}
