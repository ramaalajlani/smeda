<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ExposesBranchScopeFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingApplicationResource extends JsonResource
{
    use ExposesBranchScopeFields;

    public function toArray(Request $request): array
    {
        $attrs = $this->resource->getAttributes();
        $hasFullRow = array_key_exists('national_id', $attrs);

        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'applicant_user_id' => $this->applicant_user_id,
            'applicant_name' => $this->applicant_name,
            // لا نستدعي Gate::can لكل صف في القائمة (كان يسبب N+1 ويبطّئ الجلب جداً)
            'national_id' => $this->when($hasFullRow, $this->national_id),
            'phone' => $this->phone,
            'email' => $this->email,
            'project_name' => $this->project_name,
            'project_type' => $this->project_type,
            'project_sector' => $this->project_sector,
            'project_size' => $this->project_size,
            'business_stage' => $this->business_stage,
            'project_status' => $this->project_status,
            'requested_amount' => $this->requested_amount,
            'currency' => $this->currency,
            'financing_type' => $this->financing_type,
            'financing_mode' => $this->financing_mode,
            'repayment_period_months' => $this->repayment_period_months,
            'purpose' => $this->when(array_key_exists('purpose', $attrs), $this->purpose),
            'description' => $this->when(array_key_exists('description', $attrs), $this->description),
            'status' => $this->status,
            'current_stage' => $this->current_stage,
            'submitted_at' => optional($this->submitted_at)?->format('Y-m-d H:i:s'),
            ...$this->branchScopeFields(),
            'details' => $this->whenLoaded('details'),
            'documents' => $this->whenLoaded('documents'),
            'consultant_assignments' => $this->whenLoaded('consultantAssignments'),
            'partner_assignments' => $this->whenLoaded('partnerAssignments'),
            'funded_loans' => $this->whenLoaded('fundedLoans'),
            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
