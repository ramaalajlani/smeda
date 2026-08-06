<?php

namespace App\Support;

/**
 * Public self-registration account types — aligned with Spatie roles in RolePermissionSeeder.
 * Staff / admin roles are assigned by platform administrators only.
 */
class SelfRegistrationCatalog
{
    /** @return array<string, array{label: array{ar: string, en: string}, types: array<string, array<string, mixed>>}> */
    public static function groups(): array
    {
        return [
            'training' => [
                'label' => ['ar' => 'منظومة التدريب والاعتماد', 'en' => 'Training & certification'],
                'types' => [
                    'trainee' => [
                        'label' => ['ar' => 'متدرب', 'en' => 'Trainee'],
                        'description' => [
                            'ar' => 'التسجيل في الدورات والحصول على شهادات معتمدة.',
                            'en' => 'Enroll in courses and earn accredited certificates.',
                        ],
                        'role' => 'trainee_user',
                        'entity_type' => 'trainee_user',
                        'has_detail_wizard' => true,
                        'redirect_after' => null,
                    ],
                    'trainer' => [
                        'label' => ['ar' => 'مدرب', 'en' => 'Trainer'],
                        'description' => [
                            'ar' => 'الانضمام لشبكة المدربين المعتمدين لدى الهيئة.',
                            'en' => 'Join the authority\'s certified trainer network.',
                        ],
                        'role' => 'trainer_user',
                        'entity_type' => 'trainer_user',
                        'has_detail_wizard' => true,
                        'redirect_after' => null,
                    ],
                    'center' => [
                        'label' => ['ar' => 'مركز تدريبي', 'en' => 'Training center'],
                        'description' => [
                            'ar' => 'تسجيل مركز تدريبي للاعتماد وإدارة البرامج والشهادات.',
                            'en' => 'Register a training center for accreditation and program management.',
                        ],
                        'role' => 'center_user',
                        'entity_type' => 'center_user',
                        'has_detail_wizard' => true,
                        'redirect_after' => null,
                    ],
                ],
            ],
            'finance' => [
                'label' => ['ar' => 'التمويل', 'en' => 'Finance'],
                'types' => [
                    'project_owner' => [
                        'label' => ['ar' => 'صاحب مشروع — طلب تمويل', 'en' => 'Project owner — funding application'],
                        'description' => [
                            'ar' => 'تقديم طلب تمويل ومتابعة مراحل المراجعة والسحابة التمويلية.',
                            'en' => 'Submit and track a funding application through the finance cloud.',
                        ],
                        'role' => 'project_owner',
                        'entity_type' => 'project_owner',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/finance/finance-apply.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لتقديم طلب التمويل وإكمال بيانات مشروعك.',
                            'en' => 'After creating your account you will submit a funding application.',
                        ],
                    ],
                ],
            ],
            'incubation' => [
                'label' => ['ar' => 'ريادة الأعمال والاحتضان', 'en' => 'Entrepreneurship & incubation'],
                'types' => [
                    'incubation_applicant' => [
                        'label' => ['ar' => 'رائد أعمال — التقدّم للاحتضان', 'en' => 'Entrepreneur — incubation application'],
                        'description' => [
                            'ar' => 'التقدّم لحاضنة أعمال وإدارة طلبات الانضمام.',
                            'en' => 'Apply to a business incubator and manage your applications.',
                        ],
                        'role' => 'project_owner',
                        'entity_type' => 'project_owner',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/incubation/incubation-apply.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لاختيار حاضنة وتقديم طلب الاحتضان.',
                            'en' => 'After creating your account you will apply for incubation.',
                        ],
                    ],
                    'entrepreneur_tech' => [
                        'label' => ['ar' => 'رائد أعمال تقني — استبيان المشروع', 'en' => 'Tech entrepreneur — project survey'],
                        'description' => [
                            'ar' => 'توصيف المشاريع التقنية والابتكارية (14 محوراً).',
                            'en' => 'Complete the tech/innovation project profiling survey.',
                        ],
                        'role' => 'project_owner',
                        'entity_type' => 'project_owner',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/incubation/entrepreneur-profile.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لاستبيان توصيف مشروعك التقني.',
                            'en' => 'After creating your account you will complete the tech project survey.',
                        ],
                    ],
                ],
            ],
            'consulting' => [
                'label' => ['ar' => 'الاستشارات', 'en' => 'Consulting'],
                'types' => [
                    'consultant' => [
                        'label' => ['ar' => 'مكتب استشاري', 'en' => 'Consulting office'],
                        'description' => [
                            'ar' => 'تسجيل مكتب استشاري معتمد وتقديم الخدمات للمستفيدين.',
                            'en' => 'Register a consulting office and serve beneficiaries.',
                        ],
                        'role' => 'consultant_office',
                        'entity_type' => 'consultant_office',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/consulting/consulting-office-create.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لتسجيل بيانات مكتبك الاستشاري وانتظار الاعتماد.',
                            'en' => 'After creating your account you will register your consulting office for approval.',
                        ],
                    ],
                    'consulting_client' => [
                        'label' => ['ar' => 'طالب استشارة / مستفيد', 'en' => 'Consulting client'],
                        'description' => [
                            'ar' => 'طلب استشارة من المكاتب المعتمدة ومتابعة الطلبات.',
                            'en' => 'Request consulting services from accredited offices.',
                        ],
                        'role' => 'trainee_user',
                        'entity_type' => 'consulting_client',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/consulting/consulting-request-create.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لإنشاء طلب استشارة جديد.',
                            'en' => 'After creating your account you will create a consulting request.',
                        ],
                    ],
                ],
            ],
            'workforce' => [
                'label' => ['ar' => 'القوى العاملة', 'en' => 'Workforce'],
                'types' => [
                    'jobseeker' => [
                        'label' => ['ar' => 'باحث عن عمل', 'en' => 'Job seeker'],
                        'description' => [
                            'ar' => 'إنشاء ملف مهني والتقدّم لفرص العمل.',
                            'en' => 'Build your profile and apply for job opportunities.',
                        ],
                        'role' => 'trainee_user',
                        'entity_type' => 'job_seeker',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/workforce/job-request.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لإكمال ملفك المهني والتقدّم للفرص.',
                            'en' => 'After creating your account you will complete your profile and apply for jobs.',
                        ],
                    ],
                    'employer' => [
                        'label' => ['ar' => 'منشأة / صاحب عمل — نشر فرص', 'en' => 'Employer — post jobs'],
                        'description' => [
                            'ar' => 'نشر فرص عمل واستقبال طلبات المرشحين.',
                            'en' => 'Post job openings and review candidate applications.',
                        ],
                        'role' => 'project_owner',
                        'entity_type' => 'project_owner',
                        'has_detail_wizard' => false,
                        'redirect_after' => 'services/workforce/job-post.php',
                        'step2_intro' => [
                            'ar' => 'بعد إنشاء حسابك ستُوجَّه لنشر فرصة عمل أو إدارة فرص منشأتك.',
                            'en' => 'After creating your account you will post and manage job openings.',
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function accountTypeKeys(): array
    {
        $keys = [];
        foreach (self::groups() as $group) {
            foreach ($group['types'] as $key => $_) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /** @return list<string> */
    public static function validationKeys(): array
    {
        return array_values(array_unique(array_merge(
            self::accountTypeKeys(),
            ['entrepreneur'],
        )));
    }

    public static function normalizeAccountType(string $accountType): string
    {
        if ($accountType === 'entrepreneur') {
            return 'project_owner';
        }

        return $accountType;
    }

    public static function meta(string $key): ?array
    {
        $key = self::normalizeAccountType($key);

        foreach (self::groups() as $group) {
            if (isset($group['types'][$key])) {
                return $group['types'][$key];
            }
        }

        return null;
    }

    /** @return array{role: string, entity_type: string} */
    public static function resolveMapping(string $accountType): array
    {
        $key = self::normalizeAccountType($accountType);
        $meta = self::meta($key);

        if (!$meta) {
            throw new \InvalidArgumentException('نوع الحساب غير مدعوم.');
        }

        return [
            'role' => $meta['role'],
            'entity_type' => $meta['entity_type'],
        ];
    }

    /** @return list<string> */
    public static function skipDetailWizardTypes(): array
    {
        $keys = [];
        foreach (self::groups() as $group) {
            foreach ($group['types'] as $key => $meta) {
                if (empty($meta['has_detail_wizard'])) {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }
}
