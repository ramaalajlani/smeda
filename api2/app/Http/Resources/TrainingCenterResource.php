<?php

namespace App\Http\Resources;

use App\Support\SignedPrintUrl;
use App\Support\TrainingLocationFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAccreditationValid = (bool) ($this->is_accreditation_valid ?? false);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'city' => $this->city,
            'address' => $this->address,
            'location' => TrainingLocationFormatter::forCenter($this->resource, $request->user()),
            'phone' => $this->phone,
            'email' => $this->email,
            'classification' => $this->classification,
            'accreditation_status' => $this->accreditation_status,
            'supervisor_id' => $this->supervisor_id,
            'supervisor' => $this->whenLoaded('supervisor', function () {
                return [
                    'id' => $this->supervisor?->id,
                    'name' => $this->supervisor?->name,
                    'code' => $this->supervisor?->code,
                    'type' => $this->supervisor?->type,
                ];
            }),

            'supports_offline_training' => (bool) $this->supports_offline_training,
            'supports_online_training' => (bool) $this->supports_online_training,
            'is_active' => (bool) $this->is_active,
            'is_accreditation_valid' => $isAccreditationValid,

            'accreditation_start_date' => optional($this->accreditation_start_date)?->format('Y-m-d'),
            'accreditation_end_date' => optional($this->accreditation_end_date)?->format('Y-m-d'),

            'eligibility_status' => $isAccreditationValid && $this->accreditation_status === 'approved' && $this->is_active
                ? 'eligible'
                : 'not_eligible',

            'eligibility_label' => $isAccreditationValid && $this->accreditation_status === 'approved' && $this->is_active
                ? 'مركز معتمد وصالح لاستقبال التدريب'
                : 'المركز غير صالح حالياً للاعتماد الكامل',

            'certificate_url' => SignedPrintUrl::trainingCenterCertificate($this->id),
            'pdf_url' => SignedPrintUrl::trainingCenterCertificatePdf($this->id),

            'platforms' => $this->whenLoaded('platforms', function () {
                return $this->platforms->map(function ($platform) {
                    return [
                        'id' => $platform->id,
                        'platform_name' => $platform->platform_name,
                        'platform_url' => $platform->platform_url,
                        'status' => $platform->status,
                        'approved_at' => optional($platform->approved_at)?->format('Y-m-d'),
                        'expires_at' => optional($platform->expires_at)?->format('Y-m-d'),
                        'is_valid' => (bool) ($platform->is_valid ?? false),
                        'notes' => $platform->notes,
                    ];
                })->values();
            }),

            'stats' => [
                'trainers_count' => $this->whenCounted('trainers', $this->trainers_count),
                'courses_count' => $this->whenCounted('courses', $this->courses_count),
                'certificates_count' => $this->whenCounted('certificates', $this->certificates_count),
            ],

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}