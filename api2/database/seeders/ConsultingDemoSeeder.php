<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ConsultingContract;
use App\Models\ConsultingMessage;
use App\Models\ConsultingOffer;
use App\Models\ConsultingOffice;
use App\Models\ConsultingReport;
use App\Models\ConsultingRequest;
use App\Models\ConsultingReview;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsultingDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $damascusGov = Governorate::query()->where('code', 'damascus')->first();
            $aleppoGov   = Governorate::query()->where('code', 'aleppo')->first();
            $homsGov     = Governorate::query()->where('code', 'homs')->first();

            $damascusBranch = Branch::query()->where('code', 'BR-DAMASCUS')->first();
            $branchManager  = User::query()->where('email', 'branch.damascus@system.com')->first();
            $general        = User::query()->where('email', 'general@system.com')->first();

            $owner = User::query()->updateOrCreate(
                ['email' => 'project.owner@system.com'],
                [
                    'name' => 'صاحب مشروع تجريبي',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'project_owner',
                    'parent_user_id' => $general?->id,
                    'governorate_id' => $damascusGov?->id,
                    'branch_id' => $damascusBranch?->id,
                    'is_active' => true,
                ]
            );
            $owner->syncRoles(['project_owner']);

            $offices = $this->seedOffices([
                [
                    'code' => 'CO-DAM-01',
                    'name' => 'مكتب الشام للدراسات الاقتصادية',
                    'governorate_id' => $damascusGov?->id,
                    'license_number' => 'LIC-DAM-2024-001',
                    'address' => 'دمشق — المالكي',
                    'phone' => '011-2345678',
                    'email' => 'sham.consult@demo.local',
                    'overall_rating' => 4.60,
                    'total_requests_completed' => 12,
                    'on_time_rate' => 92,
                    'report_accept_rate' => 88,
                    'specializations' => ['CON-01', 'CON-02', 'CON-07'],
                ],
                [
                    'code' => 'CO-DAM-02',
                    'name' => 'مركز دمشق للاستشارات الإدارية',
                    'governorate_id' => $damascusGov?->id,
                    'license_number' => 'LIC-DAM-2024-002',
                    'address' => 'دمشق — أبو رمانة',
                    'phone' => '011-3456789',
                    'email' => 'admin.consult@demo.local',
                    'overall_rating' => 4.20,
                    'total_requests_completed' => 8,
                    'on_time_rate' => 85,
                    'report_accept_rate' => 90,
                    'specializations' => ['CON-11', 'CON-03'],
                ],
                [
                    'code' => 'CO-ALP-01',
                    'name' => 'مكتب حلب للاستشارات الفنية',
                    'governorate_id' => $aleppoGov?->id,
                    'license_number' => 'LIC-ALP-2024-001',
                    'address' => 'حلب — العزيزية',
                    'phone' => '021-4567890',
                    'email' => 'aleppo.tech@demo.local',
                    'overall_rating' => 4.80,
                    'total_requests_completed' => 15,
                    'on_time_rate' => 95,
                    'report_accept_rate' => 93,
                    'specializations' => ['CON-08', 'CON-10'],
                ],
                [
                    'code' => 'CO-HMS-01',
                    'name' => 'مكتب حمص للمحاسبة والضرائب',
                    'governorate_id' => $homsGov?->id,
                    'license_number' => 'LIC-HMS-2024-001',
                    'address' => 'حمص — الغوتة',
                    'phone' => '031-5678901',
                    'email' => 'homs.tax@demo.local',
                    'overall_rating' => 4.00,
                    'total_requests_completed' => 6,
                    'on_time_rate' => 80,
                    'report_accept_rate' => 85,
                    'specializations' => ['CON-04', 'CON-05'],
                ],
                [
                    'code' => 'CO-DAM-03',
                    'name' => 'مكتب التصنيف الصناعي ISIC',
                    'governorate_id' => $damascusGov?->id,
                    'license_number' => 'LIC-DAM-2024-003',
                    'address' => 'دمشق — كفرسوسة',
                    'phone' => '011-6789012',
                    'email' => 'isic@demo.local',
                    'overall_rating' => 4.50,
                    'total_requests_completed' => 10,
                    'on_time_rate' => 90,
                    'report_accept_rate' => 91,
                    'specializations' => ['CON-06', 'CON-12'],
                ],
            ]);

            $officeDam1 = $offices['CO-DAM-01'];
            $officeDam2 = $offices['CO-DAM-02'];
            $officeAlp1 = $offices['CO-ALP-01'];

            $this->seedRequest([
                'request_code' => 'CON-DEMO-00001',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'category_code' => 'CON-01',
                'request_type' => 'new_project',
                'title' => 'دراسة جدوى مشروع مطعم صHealthy',
                'description' => 'طلب مسودة لدراسة جدوى اقتصادية لمشروع مطعم يقدم وجبات صحية في دمشق.',
                'project_name' => 'Healthy Bite',
                'economic_activity' => 'مطاعم وخدمات غذائية',
                'budget_min' => 5000000,
                'budget_max' => 8000000,
                'expected_duration_days' => 21,
                'status' => 'draft',
            ]);

            $this->seedRequest([
                'request_code' => 'CON-DEMO-00002',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'branch_manager_id' => $branchManager?->id,
                'category_code' => 'CON-02',
                'request_type' => 'new_project',
                'title' => 'خطة عمل مشروع تصنيع أثاث',
                'description' => 'طلب خطة عمل تفصيلية لمشروع ورشة تصنيع أثاث منزلي.',
                'project_name' => 'أثاث دمشقي',
                'economic_activity' => 'صناعة الأثاث',
                'budget_min' => 10000000,
                'budget_max' => 15000000,
                'expected_duration_days' => 30,
                'status' => 'submitted',
                'submitted_at' => now()->subDays(3),
            ]);

            $awaiting = $this->seedRequest([
                'request_code' => 'CON-DEMO-00003',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'branch_manager_id' => $branchManager?->id,
                'category_code' => 'CON-07',
                'request_type' => 'financing',
                'title' => 'استشارة تمويلية لمشروع تقني',
                'description' => 'تقييم الجدوى التمويلية لتطبيق إلكتروني للخدمات اللوجستية.',
                'project_name' => 'LogiTech App',
                'economic_activity' => 'تقنية المعلومات',
                'budget_min' => 3000000,
                'budget_max' => 5000000,
                'expected_duration_days' => 14,
                'status' => 'awaiting_offers',
                'submitted_at' => now()->subDays(7),
                'offers_deadline' => now()->addDays(7),
            ]);

            $this->seedOffer($awaiting, $officeDam1, [
                'methodology_text' => 'تحليل السوق والتدفقات النقدية وخطة التمويل على 3 سيناريوهات.',
                'proposed_duration_days' => 14,
                'price' => 2500000,
                'status' => 'pending',
                'submitted_at' => now()->subDays(2),
            ]);

            $this->seedOffer($awaiting, $officeDam2, [
                'methodology_text' => 'مراجعة نموذج العمل وإعداد ملف تمويلي للجهات المانحة.',
                'proposed_duration_days' => 18,
                'price' => 2800000,
                'status' => 'pending',
                'submitted_at' => now()->subDay(),
            ]);

            $inProgress = $this->seedRequest([
                'request_code' => 'CON-DEMO-00004',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'branch_manager_id' => $branchManager?->id,
                'category_code' => 'CON-11',
                'request_type' => 'existing',
                'title' => 'استشارة إدارية لإعادة هيكلة شركة تجارية',
                'description' => 'مراجعة الهيكل التنظيمي واقتراح خطة تحسين العمليات.',
                'project_name' => 'شركة النور التجارية',
                'economic_activity' => 'تجارة عامة',
                'budget_min' => 2000000,
                'budget_max' => 3500000,
                'expected_duration_days' => 20,
                'status' => 'in_progress',
                'submitted_at' => now()->subDays(20),
            ]);

            $acceptedOffer = $this->seedOffer($inProgress, $officeDam2, [
                'methodology_text' => 'جلسات تشخيص، خريطة عمليات، وخطة تنفيذ على 4 أسابيع.',
                'proposed_duration_days' => 20,
                'price' => 3200000,
                'status' => 'accepted',
                'submitted_at' => now()->subDays(15),
            ]);

            $contract = $this->seedContract($inProgress, $acceptedOffer, $owner, [
                'signed_by_client_at' => now()->subDays(12),
                'signed_by_office_at' => now()->subDays(11),
                'start_date' => now()->subDays(10)->toDateString(),
                'expected_end_date' => now()->addDays(10)->toDateString(),
                'total_value' => 3200000,
                'payment_status' => 'partial',
            ]);

            ConsultingMessage::query()->updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'sender_id' => $owner->id,
                    'message_text' => 'مرحباً، نود تأكيد موعد الجلسة الأولى الأسبوع القادم.',
                ],
                [
                    'sender_role' => 'client',
                    'sent_at' => now()->subDays(9),
                    'is_read' => true,
                    'read_at' => now()->subDays(9),
                ]
            );

            $completed = $this->seedRequest([
                'request_code' => 'CON-DEMO-00005',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'branch_manager_id' => $branchManager?->id,
                'category_code' => 'CON-01',
                'request_type' => 'new_project',
                'title' => 'دراسة جدوى مصنع تعبئة زيت الزيتون',
                'description' => 'دراسة جدوى كاملة لمشروع مصنع تعبئة وتصدير زيت الزيتون.',
                'project_name' => 'زيتو السوري',
                'economic_activity' => 'صناعة غذائية',
                'budget_min' => 15000000,
                'budget_max' => 25000000,
                'expected_duration_days' => 25,
                'status' => 'completed',
                'submitted_at' => now()->subDays(45),
                'completed_at' => now()->subDays(5),
            ]);

            $completedOffer = $this->seedOffer($completed, $officeDam1, [
                'methodology_text' => 'تحليل فني ومالي شامل مع تقييم المخاطر وخطة التشغيل.',
                'proposed_duration_days' => 25,
                'price' => 4500000,
                'status' => 'accepted',
                'submitted_at' => now()->subDays(40),
            ]);

            $completedContract = $this->seedContract($completed, $completedOffer, $owner, [
                'signed_by_client_at' => now()->subDays(38),
                'signed_by_office_at' => now()->subDays(38),
                'start_date' => now()->subDays(35)->toDateString(),
                'expected_end_date' => now()->subDays(10)->toDateString(),
                'actual_end_date' => now()->subDays(6)->toDateString(),
                'total_value' => 4500000,
                'payment_status' => 'paid',
            ]);

            ConsultingReport::query()->updateOrCreate(
                ['contract_id' => $completedContract->id],
                [
                    'request_id' => $completed->id,
                    'report_pdf_path' => 'demo/consulting/reports/CON-DEMO-00005.pdf',
                    'submission_date' => now()->subDays(7),
                    'review_status' => 'approved',
                    'reviewer_id' => $branchManager?->id,
                    'review_date' => now()->subDays(6),
                    'reviewer_notes' => 'تقرير شامل ومطابق للمعايير.',
                    'recommendation_type' => 'financing',
                    'recommendation_details' => 'يوصى بتحويل المشروع لمسار التمويل بعد استكمال بعض المستندات.',
                ]
            );

            ConsultingReview::query()->updateOrCreate(
                [
                    'contract_id' => $completedContract->id,
                    'reviewer_id' => $owner->id,
                ],
                [
                    'office_id' => $officeDam1->id,
                    'overall_rating' => 5,
                    'quality_rating' => 5,
                    'time_rating' => 4,
                    'communication_rating' => 5,
                    'comment' => 'تعامل ممتاز وتقرير احترافي.',
                    'is_published' => true,
                ]
            );

            $officeDam1->recalculateRating();

            // طلب إضافي من فرع حلب لاختبار التصفية
            $this->seedRequest([
                'request_code' => 'CON-DEMO-00006',
                'user_id' => $owner->id,
                'governorate_id' => $aleppoGov?->id,
                'branch_id' => Branch::query()->where('code', 'BR-ALEPPO')->value('id'),
                'category_code' => 'CON-08',
                'request_type' => 'existing',
                'title' => 'استشارة فنية لخط إنتاج',
                'description' => 'مراجعة خط إنتاج صغير في حلب وتحسين الكفاءة.',
                'project_name' => 'مصنع حلب الصغير',
                'economic_activity' => 'صناعة',
                'budget_min' => 1500000,
                'budget_max' => 2500000,
                'expected_duration_days' => 15,
                'status' => 'offer_submitted',
                'submitted_at' => now()->subDays(10),
            ]);

            $this->seedOffer(
                ConsultingRequest::query()->where('request_code', 'CON-DEMO-00006')->firstOrFail(),
                $officeAlp1,
                [
                    'methodology_text' => 'زيارة ميدانية وتحليل خط الإنتاج وإعداد تقرير تحسين.',
                    'proposed_duration_days' => 12,
                    'price' => 1800000,
                    'status' => 'pending',
                    'submitted_at' => now()->subDays(4),
                ]
            );

            $this->seedRequest([
                'request_code' => 'CON-DEMO-00007',
                'user_id' => $owner->id,
                'governorate_id' => $homsGov?->id,
                'branch_id' => Branch::query()->where('code', 'BR-HOMS')->value('id'),
                'category_code' => 'CON-04',
                'request_type' => 'existing',
                'title' => 'استشارة ضريبية لمنشأة صناعية',
                'description' => 'مراجعة الوضع الضريبي وإعداد خطة امتثال لمنشأة في حمص.',
                'project_name' => 'مصنع حمص للبلاستيك',
                'economic_activity' => 'صناعة بلاستيك',
                'budget_min' => 800000,
                'budget_max' => 1200000,
                'expected_duration_days' => 10,
                'status' => 'needs_info',
                'submitted_at' => now()->subDays(2),
                'branch_notes' => 'يُطلب إرفاق آخر بيان ضريبي.',
            ]);

            $this->seedRequest([
                'request_code' => 'CON-DEMO-00008',
                'user_id' => $owner->id,
                'governorate_id' => $damascusGov?->id,
                'branch_id' => $damascusBranch?->id,
                'branch_manager_id' => $branchManager?->id,
                'category_code' => 'CON-03',
                'request_type' => 'new_project',
                'title' => 'دراسة سوق لمنتج غذائي جديد',
                'description' => 'تحليل حجم السوق والمنافسين والتسعير لمنتج غذائي معلّب.',
                'project_name' => 'معلّبات الشام',
                'economic_activity' => 'صناعة غذائية',
                'budget_min' => 2500000,
                'budget_max' => 4000000,
                'expected_duration_days' => 18,
                'status' => 'rejected',
                'submitted_at' => now()->subDays(30),
                'branch_notes' => 'نقص في المستندات الأساسية.',
            ]);

            ConsultingOffice::query()->updateOrCreate(
                ['code' => 'CO-DAM-04'],
                [
                    'name' => 'مكتب الريادة للاستشارات التسويقية',
                    'governorate_id' => $damascusGov?->id,
                    'license_number' => 'LIC-DAM-2024-004',
                    'address' => 'دمشق — المزة',
                    'phone' => '011-7890123',
                    'email' => 'marketing.consult@demo.local',
                    'overall_rating' => 4.30,
                    'total_requests_completed' => 4,
                    'on_time_rate' => 87,
                    'report_accept_rate' => 82,
                    'status' => 'pending',
                    'bio' => 'مكتب قيد الاعتماد — تخصص تسويق وعلامات تجارية.',
                    'accreditation_date' => null,
                ]
            );
        });
    }

    /** @return array<string, ConsultingOffice> */
    private function seedOffices(array $rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $specs = $row['specializations'] ?? [];
            unset($row['specializations']);

            $office = ConsultingOffice::query()->updateOrCreate(
                ['code' => $row['code']],
                array_merge($row, [
                    'status' => 'active',
                    'accreditation_date' => now()->subMonths(6)->toDateString(),
                    'bio' => 'مكتب استشاري معتمد ضمن شبكة SMEDA التجريبية.',
                ])
            );

            foreach ($specs as $code) {
                $office->specializations()->updateOrCreate(
                    ['category_code' => $code],
                    ['years_experience' => 5, 'is_verified' => true]
                );
            }

            $map[$row['code']] = $office->fresh(['specializations']);
        }

        return $map;
    }

    private function seedRequest(array $data): ConsultingRequest
    {
        $code = $data['request_code'];

        return ConsultingRequest::query()->updateOrCreate(
            ['request_code' => $code],
            $data
        );
    }

    private function seedOffer(ConsultingRequest $request, ConsultingOffice $office, array $data): ConsultingOffer
    {
        return ConsultingOffer::query()->updateOrCreate(
            [
                'request_id' => $request->id,
                'office_id' => $office->id,
            ],
            $data
        );
    }

    private function seedContract(
        ConsultingRequest $request,
        ConsultingOffer $offer,
        User $client,
        array $data
    ): ConsultingContract {
        return ConsultingContract::query()->updateOrCreate(
            ['request_id' => $request->id],
            array_merge($data, [
                'offer_id' => $offer->id,
                'office_id' => $offer->office_id,
                'client_user_id' => $client->id,
                'contract_code' => 'CONC-DEMO-' . str_pad((string) $request->id, 4, '0', STR_PAD_LEFT),
            ])
        );
    }
}
