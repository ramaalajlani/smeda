<?php

if (!function_exists('get_service_landing')) {
    function get_service_landing(string $slug): ?array
    {
        $all = service_landings_catalog();

        return $all[$slug] ?? null;
    }
}

if (!function_exists('service_landings_catalog')) {
    function service_landings_catalog(): array
    {
        return [
            'training' => [
                'slug' => 'training',
                'page_title' => 'التدريب والتأهيل — دليل الرحلة',
                'badge' => 'تدريب',
                'badge_icon' => 'bi-mortarboard-fill',
                'hero_title' => 'مسارك نحو شهادة تدريبية معتمدة من الهيئة',
                'hero_text' => 'من إنشاء الحساب إلى إصدار الشهادة — خارطة واضحة تساعدك على متابعة كل خطوة في منظومة التدريب والتأهيل الرسمية.',
                'hero_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&q=85&fit=crop',
                'theme' => 'soft',
                'benefits' => [
                    'دورات معتمدة عبر شبكة مراكز في جميع المحافظات',
                    'شهادات إلكترونية قابلة للتحقق فورياً',
                    'مسارات منفصلة للمتدرب والمدرب ومركز التدريب',
                    'متابعة الطلبات والاعتماد من لوحة حسابك',
                ],
                'roadmap_title' => 'خارطة رحلة التدريب والتأهيل',
                'roadmap_note' => 'اتبع الخطوات بالترتيب — كل مرحلة تمهّد للتي تليها حتى تحصل على الشهادة المعتمدة.',
                'steps' => [
                    ['icon' => 'bi-person-plus-fill', 'title' => 'إنشاء الحساب', 'desc' => 'سجّل كمتدرب أو مدرب أو مركز تدريب عبر البوابة الإلكترونية.'],
                    ['icon' => 'bi-patch-check-fill', 'title' => 'طلب الاعتماد', 'desc' => 'قدّم ملف الاعتماد الرسمي إن كانت فئتك تتطلب مراجعة من الهيئة.'],
                    ['icon' => 'bi-search', 'title' => 'اختيار البرنامج', 'desc' => 'استعرض المراكز والدورات المعتمدة واختر البرنامج المناسب لاحتياجك.'],
                    ['icon' => 'bi-calendar-check-fill', 'title' => 'إتمام التدريب', 'desc' => 'احضر الدورة وأكمل متطلباتها وفق جدول المركز المعتمد.'],
                    ['icon' => 'bi-award-fill', 'title' => 'إصدار الشهادة', 'desc' => 'تُصدر شهادتك إلكترونياً ويمكن التحقق منها عبر بوابة الهيئة.'],
                ],
                'cards' => [
                    ['title' => 'مراكز التدريب', 'desc' => 'استعرض المراكز المعتمدة', 'url' => 'services/training/training-centers-list.php', 'img' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&fit=crop'],
                    ['title' => 'البرامج التدريبية', 'desc' => 'تصفّح الدورات المتاحة', 'url' => 'services/training/training-programs-list.php', 'img' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&q=85&fit=crop'],
                    ['title' => 'التحقق من الشهادات', 'desc' => 'تحقق من صحة أي شهادة', 'url' => 'services/training/training-verification.php', 'img' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'استعرض البرامج', 'url' => 'services/training/training-programs-list.php', 'icon' => 'bi-mortarboard-fill'],
                'cta_secondary' => ['label' => 'إنشاء حساب', 'url' => 'register.php', 'icon' => 'bi-person-plus-fill'],
            ],
            'finance' => [
                'slug' => 'finance',
                'page_title' => 'التمويل الميسّر — دليل الرحلة',
                'badge' => 'تمويل',
                'badge_icon' => 'bi-bank2',
                'hero_title' => 'من طلب التمويل إلى الجهة الممولة — مسار رقمي موثّق',
                'hero_text' => 'تقدّم طلبك إلكترونياً، تابع مراجعة الفرع والاعتماد، وصولاً إلى عرض التمويل من البنوك والجهات الشريكة.',
                'hero_image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1200&q=85&fit=crop',
                'theme' => 'dark',
                'benefits' => [
                    'تقديم كامل عبر الإنترنت دون مراجعات مكتبية',
                    'مراجعة أولية من الفرع ثم اعتماد التمويل',
                    'سحابة قروض غير ممولة للجهات التمويلية',
                    'متابعة حالة الطلب خطوة بخطوة',
                ],
                'roadmap_title' => 'خارطة رحلة طلب التمويل',
                'roadmap_note' => 'كل مرحلة توضّح المطلوب من مقدّم الطلب. بعد الإرسال تتم المراجعة والاعتماد ثم العرض في سحابة التمويل.',
                'steps' => [
                    ['icon' => 'bi-file-earmark-text-fill', 'title' => 'تعريف الطلب', 'desc' => 'نوع التمويل، حالة المشروع، وقطاع النشاط.'],
                    ['icon' => 'bi-person-vcard-fill', 'title' => 'بيانات مقدّم الطلب', 'desc' => 'الاسم، الصفة القانونية، المهنة، وبيانات التواصل.'],
                    ['icon' => 'bi-cash-stack', 'title' => 'بيانات التمويل', 'desc' => 'الغاية، السقف، العملة، وهيكل السداد المقترح.'],
                    ['icon' => 'bi-clipboard-check', 'title' => 'مراجعة واعتماد', 'desc' => 'بعد الإرسال يُراجع الطلب من الفرع ثم يُعتمد قبل السحابة.'],
                    ['icon' => 'bi-receipt', 'title' => 'النشاط والفواتير', 'desc' => 'إثبات النشاط وبيانات الشركة والمستندات الداعمة.'],
                    ['icon' => 'bi-graph-up', 'title' => 'البيانات المالية', 'desc' => 'الميزانية وقائمة الدخل بشكل منظم.'],
                    ['icon' => 'bi-people-fill', 'title' => 'العمالة والتأهيل', 'desc' => 'القوى العاملة والمؤهلات والتوزيع العددي.'],
                    ['icon' => 'bi-mortarboard', 'title' => 'الاحتياجات التدريبية', 'desc' => 'الفجوات التدريبية للإداريين والفنيين.'],
                    ['icon' => 'bi-send-check-fill', 'title' => 'المراجعة والإرسال', 'desc' => 'فحص اكتمال الملف قبل الإرسال الرسمي.'],
                ],
                'cards' => [
                    ['title' => 'قدّم طلب تمويل', 'desc' => 'ابدأ النموذج الإلكتروني', 'url' => 'services/finance/finance-apply.php', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop'],
                    ['title' => 'سحابة القروض', 'desc' => 'الفرص غير الممولة', 'url' => 'services/finance/finance-cloud.php', 'img' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&q=85&fit=crop'],
                    ['title' => 'مؤشرات التمويل', 'desc' => 'إحصاءات وشفافية', 'url' => 'services/finance/finance-metrics.php', 'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'ابدأ طلب التمويل', 'url' => 'services/finance/finance-apply.php', 'icon' => 'bi-cash-stack'],
                'cta_secondary' => ['label' => 'منظومة التمويل', 'url' => 'services/finance/finance.php', 'icon' => 'bi-grid-fill'],
            ],
            'incubation' => [
                'slug' => 'incubation',
                'page_title' => 'حاضنات الأعمال — دليل الرحلة',
                'badge' => 'ريادة',
                'badge_icon' => 'bi-buildings-fill',
                'hero_title' => 'من اكتشاف الحاضنة إلى الانضمام — مسار احتضان رسمي',
                'hero_text' => 'استعرض الحاضنات، قدّم طلبك إلكترونياً، وتابع القبول والبرامج الداعمة من حسابك على المنصة.',
                'hero_image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200&q=85&fit=crop',
                'theme' => 'white',
                'benefits' => [
                    'حاضنات في قطاعات ومحافظات متعددة',
                    'إرشاد وتدريب واستشارات تحت سقف واحد',
                    'متابعة طلبات الانضمام إلكترونياً',
                    'برامج دعم للمشاريع في مراحلها الأولى',
                ],
                'roadmap_title' => 'خارطة رحلة الاحتضان',
                'roadmap_note' => 'خمس خطوات من اكتشاف الحاضنة المناسبة حتى بدء برنامج الاحتضان.',
                'steps' => [
                    ['icon' => 'bi-compass-fill', 'title' => 'اكتشف الحاضنات', 'desc' => 'تصفّح القائمة وفلتر حسب القطاع والمحافظة.'],
                    ['icon' => 'bi-funnel-fill', 'title' => 'اختر الأنسب', 'desc' => 'قارن البرامج والخدمات وحدّد الحاضنة المناسبة.'],
                    ['icon' => 'bi-file-earmark-text-fill', 'title' => 'قدّم طلبك', 'desc' => 'املأ نموذج التقديم وارفق المستندات المطلوبة.'],
                    ['icon' => 'bi-hourglass-split', 'title' => 'انتظر القبول', 'desc' => 'تُراجع الهيئة الطلب وتصلك النتيجة عبر حسابك.'],
                    ['icon' => 'bi-graph-up-arrow', 'title' => 'ابدأ الاحتضان', 'desc' => 'انضم للبرنامج واستفد من الإرشاد والموارد.'],
                ],
                'cards' => [
                    ['title' => 'استعراض الحاضنات', 'desc' => 'جميع الحاضنات المتاحة', 'url' => 'services/incubation/incubators.php', 'img' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&fit=crop'],
                    ['title' => 'طلب احتضان', 'desc' => 'قدّم طلبك الآن', 'url' => 'services/incubation/incubation-apply.php', 'img' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=85&fit=crop'],
                    ['title' => 'بوابة ريادة الأعمال', 'desc' => 'مسار الفكرة والمشروع', 'url' => 'services/incubation/entrepreneurship-hub.php', 'img' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'استعرض الحاضنات', 'url' => 'services/incubation/incubators.php', 'icon' => 'bi-buildings-fill'],
                'cta_secondary' => ['label' => 'قدّم طلب احتضان', 'url' => 'services/incubation/incubation-apply.php', 'icon' => 'bi-send-fill'],
            ],
            'consulting' => [
                'slug' => 'consulting',
                'page_title' => 'الاستشارات التجارية — دليل الرحلة',
                'badge' => 'استشارات',
                'badge_icon' => 'bi-headset',
                'hero_title' => 'استشارات معتمدة — من الطلب إلى التقرير',
                'hero_text' => 'تواصل مع مكاتب استشارية معتمدة من الهيئة للحصول على دعم قانوني ومالي وإداري لمشروعك.',
                'hero_image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1200&q=85&fit=crop',
                'theme' => 'soft',
                'benefits' => [
                    'مكاتب استشارية معتمدة ومرخّصة',
                    'طلب استشارة إلكتروني مع متابعة الحالة',
                    'تخصصات: قانونية، مالية، إدارية، وتسويقية',
                    'ربط تلقائي مع مسار التمويل عند الحاجة',
                ],
                'roadmap_title' => 'خارطة رحلة طلب الاستشارة',
                'roadmap_note' => 'اتبع المسار من تسجيل الدخول حتى استلام التقرير أو عرض السعر.',
                'steps' => [
                    ['icon' => 'bi-box-arrow-in-right', 'title' => 'تسجيل الدخول', 'desc' => 'أنشئ حساباً أو سجّل دخولك إلى البوابة.'],
                    ['icon' => 'bi-ui-checks', 'title' => 'تحديد نوع الاستشارة', 'desc' => 'اختر المجال: قانوني، مالي، إداري، أو تسويقي.'],
                    ['icon' => 'bi-building-check', 'title' => 'اختيار المكتب أو الإحالة', 'desc' => 'اختر مكتباً معتمداً أو تُحال آلياً وفق تخصصك.'],
                    ['icon' => 'bi-chat-dots-fill', 'title' => 'متابعة الطلب', 'desc' => 'تابع الحالة والمراسلات من «طلباتي».'],
                    ['icon' => 'bi-file-earmark-check-fill', 'title' => 'استلام المخرجات', 'desc' => 'تقرير، عرض سعر، أو توصيات رسمية حسب نوع الطلب.'],
                ],
                'cards' => [
                    ['title' => 'طلب استشارة', 'desc' => 'قدّم طلبك الآن', 'url' => 'services/consulting/consulting-request-create.php', 'img' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=600&q=85&fit=crop'],
                    ['title' => 'المكاتب المعتمدة', 'desc' => 'استعرض القائمة', 'url' => 'services/consulting/consulting-offices-list.php', 'img' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&fit=crop'],
                    ['title' => 'طلباتي', 'desc' => 'متابعة الاستشارات', 'url' => 'services/consulting/consulting-requests-list.php', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'طلب استشارة', 'url' => 'services/consulting/consulting-request-create.php', 'icon' => 'bi-headset'],
                'cta_secondary' => ['label' => 'المكاتب المعتمدة', 'url' => 'services/consulting/consulting-offices-list.php', 'icon' => 'bi-building'],
            ],
            'certificates' => [
                'slug' => 'certificates',
                'page_title' => 'الشهادات المعتمدة — دليل الرحلة',
                'badge' => 'شهادات',
                'badge_icon' => 'bi-patch-check-fill',
                'hero_title' => 'شهادة إلكترونية معتمدة — قابلة للتحقق فورياً',
                'hero_text' => 'من إتمام الدورة إلى إصدار الشهادة والتحقق العام — مسار رقمي موثوق يعزّز مصداقية مؤهلاتك.',
                'hero_image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1200&q=85&fit=crop',
                'theme' => 'white',
                'benefits' => [
                    'إصدار إلكتروني فوري بعد إتمام الدورة',
                    'رقم فريد للتحقق من أي جهة',
                    'ربط مباشر بسجل المتدرب في المنصة',
                    'مطابقة لمعايير الهيئة الرسمية',
                ],
                'roadmap_title' => 'خارطة رحلة الشهادة المعتمدة',
                'roadmap_note' => 'الشهادة تُصدر بعد استيفاء متطلبات الدورة المعتمدة.',
                'steps' => [
                    ['icon' => 'bi-mortarboard-fill', 'title' => 'إتمام دورة معتمدة', 'desc' => 'سجّل وأكمل دورة عبر مركز تدريبي معتمد.'],
                    ['icon' => 'bi-check2-circle', 'title' => 'اعتماد المركز', 'desc' => 'يُثبت المركز إتمامك للمتطلبات عبر المنصة.'],
                    ['icon' => 'bi-award-fill', 'title' => 'إصدار الشهادة', 'desc' => 'تُولَّد الشهادة إلكترونياً برقم تحقق فريد.'],
                    ['icon' => 'bi-shield-check', 'title' => 'التحقق العام', 'desc' => 'أي جهة يمكنها التحقق عبر بوابة «التحقق من الشهادات».'],
                ],
                'cards' => [
                    ['title' => 'التحقق من شهادة', 'desc' => 'أدخل رقم الشهادة', 'url' => 'services/training/training-verification.php', 'img' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&q=85&fit=crop'],
                    ['title' => 'البرامج المعتمدة', 'desc' => 'ابحث عن دورة', 'url' => 'services/training/training-programs-list.php', 'img' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=85&fit=crop'],
                    ['title' => 'التوقيع الإلكتروني', 'desc' => 'إدارة توقيعك', 'url' => 'services/training/my-electronic-signature.php', 'img' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'تحقق من شهادة', 'url' => 'services/training/training-verification.php', 'icon' => 'bi-patch-check-fill'],
                'cta_secondary' => ['label' => 'البرامج التدريبية', 'url' => 'services/training/training-programs-list.php', 'icon' => 'bi-mortarboard-fill'],
            ],
            'needs-map' => [
                'slug' => 'needs-map',
                'page_title' => 'خريطة الاحتياجات — دليل الرحلة',
                'badge' => 'GIS',
                'badge_icon' => 'bi-geo-alt-fill',
                'hero_title' => 'رصد احتياجات المشاريع — قرارات مبنية على بيانات حقيقية',
                'hero_text' => 'سجّل احتياج مشروعك أو محافظتك على الخريطة التفاعلية، وراقب المؤشرات لدعم التخطيط والتمويل.',
                'hero_image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=85&fit=crop',
                'theme' => 'soft',
                'benefits' => [
                    'خريطة تفاعلية على مستوى المحافظات',
                    'تسجيل احتياجات المشاريع إلكترونياً',
                    'لوحة مؤشرات وإحصاءات',
                    'دعم قرارات التمويل والتخطيط',
                ],
                'roadmap_title' => 'خارطة استخدام منظومة GIS',
                'roadmap_note' => 'المسار يختلف حسب صلاحياتك — المستفيد يسجّل احتياجاً، والمراجع يتابع ويعتمد.',
                'steps' => [
                    ['icon' => 'bi-box-arrow-in-right', 'title' => 'تسجيل الدخول', 'desc' => 'ادخل بحسابك للوصول للخريطة والصلاحيات.'],
                    ['icon' => 'bi-map-fill', 'title' => 'فتح الخريطة', 'desc' => 'استعرض الاحتياجات المسجّلة على مستوى الجغرافيا.'],
                    ['icon' => 'bi-pin-map-fill', 'title' => 'تسجيل احتياج', 'desc' => 'حدّد الموقع ونوع الاحتياج وأرفق التفاصيل.'],
                    ['icon' => 'bi-speedometer2', 'title' => 'متابعة المؤشرات', 'desc' => 'راقب حالة الاحتياج من لوحة المؤشرات.'],
                    ['icon' => 'bi-bar-chart-fill', 'title' => 'دعم القرار', 'desc' => 'تُستخدم البيانات في التخطيط والبرامج والتمويل.'],
                ],
                'cards' => [
                    ['title' => 'الخريطة التفاعلية', 'desc' => 'افتح خريطة الاحتياجات', 'url' => 'services/gis/needs-map.php', 'img' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&q=85&fit=crop'],
                    ['title' => 'تسجيل احتياج', 'desc' => 'أضف احتياجاً جديداً', 'url' => 'services/gis/need-create.php', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop'],
                    ['title' => 'لوحة المؤشرات', 'desc' => 'إحصاءات وتحليلات', 'url' => 'services/gis/needs-dashboard.php', 'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=85&fit=crop'],
                ],
                'cta_primary' => ['label' => 'افتح الخريطة', 'url' => 'services/gis/needs-map.php', 'icon' => 'bi-geo-alt-fill'],
                'cta_secondary' => ['label' => 'تسجيل احتياج', 'url' => 'services/gis/need-create.php', 'icon' => 'bi-pin-map-fill'],
            ],
        ];
    }
}
