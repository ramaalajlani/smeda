<?php

namespace App\Http\Resources;

use App\Support\TrainingDataScope;
use App\Support\SignedPrintUrl;
use App\Support\TrainingLocationFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TraineeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $canSeeSensitive = TrainingDataScope::canViewTraineeSensitive($user, $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'mother_name' => $this->mother_name,
            'trainee_code' => $this->trainee_code,
            'national_id' => $this->when($canSeeSensitive, $this->national_id),
            'phone' => $this->when($canSeeSensitive, $this->phone),
            'email' => $this->when($canSeeSensitive, $this->email),
            'location' => TrainingLocationFormatter::forTrainee($this->resource, $user),
            'birth_date' => $this->when($canSeeSensitive, optional($this->birth_date)?->format('Y-m-d')),
            'gender' => $this->gender,
            'education_level' => $this->education_level,
            'status' => $this->status,
            'notes' => $this->when($canSeeSensitive, $this->notes),

            'card_url' => SignedPrintUrl::traineeCard($this->id),
            'pdf_url' => SignedPrintUrl::traineeCardPdf($this->id),

            'latest_certificate' => $this->whenLoaded('certificates', function () use ($user) {
                $latestCertificate = $this->certificates
                    ->sortByDesc('id')
                    ->first();

                if (!$latestCertificate) {
                    return null;
                }

                return [
                    'id' => $latestCertificate->id,
                    'certificate_number' => $latestCertificate->certificate_number,
                    'reference_number' => $latestCertificate->reference_number,
                    'verification_code' => ($user && TrainingDataScope::canViewCertificateSecrets($user, $latestCertificate))
                        ? $latestCertificate->verification_code
                        : null,
                    'certificate_type' => $latestCertificate->certificate_type,
                    'result' => $latestCertificate->result,
                    'score' => $latestCertificate->score,
                    'hours_awarded' => $latestCertificate->hours_awarded,
                    'status' => $latestCertificate->status,
                    'issue_date' => optional($latestCertificate->issue_date)?->format('Y-m-d'),
                    'is_verified' => (bool) $latestCertificate->is_verified,
                    'printable_url' => SignedPrintUrl::certificatePrint($latestCertificate->id),
                    'pdf_url' => SignedPrintUrl::certificatePdf($latestCertificate->id),
                ];
            }),

            'courses' => $this->whenLoaded('courses', function () {
                return $this->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'course_code' => $course->course_code,
                        'title' => $course->title,
                        'delivery_mode' => $course->delivery_mode,
                        'start_date' => optional($course->start_date)?->format('Y-m-d'),
                        'end_date' => optional($course->end_date)?->format('Y-m-d'),
                        'status' => $course->status,
                        'pivot' => [
                            'attendance_status' => $course->pivot?->attendance_status,
                            'result' => $course->pivot?->result,
                            'score' => $course->pivot?->score,
                            'attended_hours' => $course->pivot?->attended_hours,
                            'notes' => $course->pivot?->notes,
                        ],
                    ];
                })->values();
            }),

            'certificates' => $this->whenLoaded('certificates', function () use ($user) {
                return $this->certificates->map(function ($certificate) use ($user) {
                    return [
                        'id' => $certificate->id,
                        'certificate_number' => $certificate->certificate_number,
                        'reference_number' => $certificate->reference_number,
                        'verification_code' => $user && TrainingDataScope::canViewCertificateSecrets($user, $certificate)
                            ? $certificate->verification_code
                            : null,
                        'certificate_type' => $certificate->certificate_type,
                        'result' => $certificate->result,
                        'score' => $certificate->score,
                        'hours_awarded' => $certificate->hours_awarded,
                        'status' => $certificate->status,
                        'issue_date' => optional($certificate->issue_date)?->format('Y-m-d'),
                        'is_verified' => (bool) $certificate->is_verified,
                        'printable_url' => SignedPrintUrl::certificatePrint($certificate->id),
                        'pdf_url' => SignedPrintUrl::certificatePdf($certificate->id),
                    ];
                })->values();
            }),

            'stats' => [
                'courses_count' => $this->whenCounted('courses', $this->courses_count),
                'certificates_count' => $this->whenCounted('certificates', $this->certificates_count),
            ],

            'created_at' => optional($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
