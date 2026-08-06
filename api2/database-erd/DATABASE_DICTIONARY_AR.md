# قاموس بيانات SMEDC — قاعدة بيانات الهيئة

> **مصدر البيانات:** Laravel migrations (`api2/database/migrations/`)
> **تاريخ التوليد:** 2026-07-17 09:15:12
> **عدد الجداول:** 89

---

## جدول `administrative_units`

**الوظيفة:** جدول: administrative units
**مصدر التعريف:** `2026_06_23_100000_create_needs_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `governorate_id` | bigint unsigned | NO | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `district_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `unit_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `countryside_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `locality_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)

---

## جدول `agreements`

**الوظيفة:** جدول: agreements
**مصدر التعريف:** `2026_06_19_100000_create_agreements_and_financial_records_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `partner_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `agreement_type` | varchar(255) | NO | general | - | - | - | - | - | - |
| `status` | varchar(255) | NO | draft | - | - | - | - | - | - |
| `start_date` | date | YES | - | - | - | - | - | - | - |
| `end_date` | date | YES | - | - | - | - | - | - | - |
| `amount` | decimal(14,2) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `scope_type` | varchar(255) | NO | national | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)

---

## جدول `audit_logs`

**الوظيفة:** سجل التدقيق
**مصدر التعريف:** `2026_05_29_140000_create_audit_logs_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `action` | varchar(100) | NO | - | - | - | - | - | - | - |
| `auditable_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `auditable_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `ip_address` | varchar(45) | YES | - | - | - | - | - | - | - |
| `user_agent` | text | YES | - | - | - | - | - | - | - |
| `old_values` | json | YES | - | - | - | - | - | - | - |
| `new_values` | json | YES | - | - | - | - | - | - | - |
| `metadata` | json | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | NO | - | - | - | - | - | - | تاريخ الإنشاء |
| `module` | varchar(80) | YES | - | - | - | - | - | - | - |
| `description` | varchar(500) | YES | - | - | - | - | - | - | - |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← (polymorphic) (Polymorphic, قوية)

---

## جدول `branches`

**الوظيفة:** فروع الهيئة
**مصدر التعريف:** `2026_06_17_100000_create_governorates_and_branches_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `governorate_id` | bigint unsigned | NO | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `name` | varchar(150) | NO | - | - | - | - | - | - | - |
| `code` | varchar(30) | NO | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `manager_user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |

**العلاقات:**
- → administrative_units (One-to-Many, مؤكدة)
- → agreements (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → consultant_offices (One-to-Many, مؤكدة)
- → consulting_requests (One-to-Many, مؤكدة)
- → financial_records (One-to-Many, مؤكدة)
- → funding_applications (One-to-Many, مؤكدة)
- → incubators (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → news (One-to-Many, مؤكدة)
- → success_stories (One-to-Many, مؤكدة)
- → training_supervisors (One-to-Many, مؤكدة)
- → users (One-to-Many, مؤكدة)

---

## جدول `cache`

**الوظيفة:** جدول: cache
**مصدر التعريف:** `0001_01_01_000001_create_cache_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `key` | varchar(255) | NO | - | - | - | - | - | - | - |
| `value` | mediumtext | NO | - | - | - | - | - | - | - |
| `expiration` | int | NO | - | - | - | - | - | - | - |

---

## جدول `cache_locks`

**الوظيفة:** جدول: cache locks
**مصدر التعريف:** `0001_01_01_000001_create_cache_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `key` | varchar(255) | NO | - | - | - | - | - | - | - |
| `owner` | varchar(255) | NO | - | - | - | - | - | - | - |
| `expiration` | int | NO | - | - | - | - | - | - | - |

---

## جدول `certificate_approvals`

**الوظيفة:** جدول: certificate approvals
**مصدر التعريف:** `2026_04_09_194054_create_certificate_approvals_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `certificate_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `approved_by` | bigint unsigned | NO | - | - | - | - | - | - | - |
| `decision_at` | timestamp | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← certificates (One-to-Many, قوية)
- ← users (One-to-Many (inverse), قوية)
- → document_electronic_signatures (Polymorphic One-to-One, قوية)

---

## جدول `certificates`

**الوظيفة:** الشهادات الصادرة
**مصدر التعريف:** `2026_04_09_131731_create_certificates_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `trainee_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_kit_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_program_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_course_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `certificate_number` | varchar(100) | NO | - | - | - | - | - | - | - |
| `reference_number` | varchar(100) | YES | - | - | - | - | - | - | - |
| `verification_code` | varchar(100) | YES | - | - | - | - | - | - | - |
| `score` | decimal(5,2) | YES | - | - | - | - | - | - | - |
| `issue_date` | date | YES | - | - | - | - | - | - | - |
| `certificate_date` | date | YES | - | - | - | - | - | - | - |
| `qr_code_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `certificate_file_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_verified` | boolean | NO | false | - | - | - | - | - | - |
| `verified_at` | timestamp | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `security_hash` | varchar(64) | YES | - | - | - | - | - | - | - |
| `certificate_code` | varchar(180) | YES | - | - | - | - | - | - | - |
| `center_code` | varchar(80) | YES | - | - | - | - | - | - | - |
| `trainer_code` | varchar(80) | YES | - | - | - | - | - | - | - |
| `kit_code` | varchar(80) | YES | - | - | - | - | - | - | - |
| `course_code` | varchar(80) | YES | - | - | - | - | - | - | - |
| `trainee_code` | varchar(80) | YES | - | - | - | - | - | - | - |
| `qr_url` | varchar(500) | YES | - | - | - | - | - | - | - |
| `issued_at` | timestamp | YES | - | - | - | - | - | - | - |

**العلاقات:**
- ← trainees (One-to-Many (inverse), قوية)
- ← training_centers (One-to-Many (inverse), قوية)
- ← trainers (One-to-Many (inverse), قوية)
- ← training_kits (One-to-Many (inverse), قوية)
- ← training_programs (One-to-Many (inverse), قوية)
- ← training_courses (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- → certificate_approvals (One-to-Many, قوية)
- ← training_courses (One-to-Many, محتملة)

---

## جدول `consultant_assignments`

**الوظيفة:** جدول: consultant assignments
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `consultant_office_id` | bigint unsigned | NO | - | - | consultant_offices.id | - | - | - | مفتاح خارجي |
| `assigned_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `assigned_at` | timestamp | NO | - | - | - | - | - | - | - |
| `status` | enum | NO | assigned | - | - | - | - | assigned, accepted, rejected, in_progress, completed, cancelled | - |
| `price_offer_amount` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `price_offer_currency` | varchar(8) | YES | - | - | - | - | - | - | - |
| `price_offer_status` | enum | YES | - | - | - | - | - | pending, submitted, approved, rejected | - |
| `consultant_notes` | text | YES | - | - | - | - | - | - | - |
| `completed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)
- ← consultant_offices (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consultant_offices`

**الوظيفة:** جدول: consultant offices
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `license_number` | varchar(255) | YES | - | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `specialization` | varchar(255) | YES | - | - | - | - | - | - | - |
| `sectors` | json | YES | - | - | - | - | - | - | - |
| `contact_person` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | active | - | - | - | - | active, inactive, suspended | - |
| `created_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `supervised_by_type` | varchar(50) | NO | consultant_union | - | - | - | - | - | - |
| `updated_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |

**العلاقات:**
- → consultant_assignments (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → consultant_reports (One-to-Many, مؤكدة)
- → users (One-to-Many, مؤكدة)

---

## جدول `consultant_reports`

**الوظيفة:** جدول: consultant reports
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `consultant_office_id` | bigint unsigned | NO | - | - | consultant_offices.id | - | - | - | مفتاح خارجي |
| `consultant_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `risk_level` | enum | YES | - | - | - | - | - | low, medium, high | - |
| `recommended_amount` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `recommendation` | enum | YES | - | - | - | - | - | approve, reject, needs_adjustment | - |
| `report_summary` | text | YES | - | - | - | - | - | - | - |
| `strengths` | text | YES | - | - | - | - | - | - | - |
| `weaknesses` | text | YES | - | - | - | - | - | - | - |
| `conditions` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)
- ← consultant_offices (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_categories`

**الوظيفة:** جدول: consulting categories
**مصدر التعريف:** `2026_06_18_100000_create_consulting_categories_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `code` | varchar(10) | NO | - | - | - | - | - | - | - |
| `name_ar` | varchar(255) | NO | - | - | - | - | - | - | - |
| `name_en` | varchar(255) | YES | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `applicable_sectors` | json | YES | - | - | - | - | - | - | - |
| `requires_isic4` | boolean | NO | false | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

---

## جدول `consulting_contracts`

**الوظيفة:** جدول: consulting contracts
**مصدر التعريف:** `2026_06_18_100006_create_consulting_contracts_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `contract_code` | varchar(30) | YES | - | - | - | - | - | - | - |
| `request_id` | bigint unsigned | NO | - | - | consulting_requests.id | - | - | - | مفتاح خارجي |
| `offer_id` | bigint unsigned | NO | - | - | consulting_offers.id | - | - | - | مفتاح خارجي |
| `office_id` | bigint unsigned | NO | - | - | consulting_offices.id | - | - | - | مفتاح خارجي |
| `client_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `signed_by_client_at` | timestamp | YES | - | - | - | - | - | - | - |
| `signed_by_office_at` | timestamp | YES | - | - | - | - | - | - | - |
| `start_date` | date | YES | - | - | - | - | - | - | - |
| `expected_end_date` | date | YES | - | - | - | - | - | - | - |
| `actual_end_date` | date | YES | - | - | - | - | - | - | - |
| `total_value` | decimal(12,2) | NO | - | - | - | - | - | - | - |
| `payment_status` | enum | NO | unpaid | - | - | - | - | unpaid, partial, paid | - |
| `contract_pdf_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_requests (One-to-Many, مؤكدة)
- ← consulting_offers (One-to-Many, مؤكدة)
- ← consulting_offices (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → consulting_messages (One-to-Many, مؤكدة)
- → consulting_reports (One-to-Many, مؤكدة)
- → consulting_reviews (One-to-Many, مؤكدة)

---

## جدول `consulting_messages`

**الوظيفة:** جدول: consulting messages
**مصدر التعريف:** `2026_06_18_100007_create_consulting_messages_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `contract_id` | bigint unsigned | NO | - | - | consulting_contracts.id | - | - | - | مفتاح خارجي |
| `sender_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `sender_role` | varchar(30) | YES | - | - | - | - | - | - | - |
| `message_text` | text | NO | - | - | - | - | - | - | - |
| `attachment_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_read` | boolean | NO | false | - | - | - | - | - | - |
| `read_at` | timestamp | YES | - | - | - | - | - | - | - |
| `sent_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_contracts (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_offers`

**الوظيفة:** جدول: consulting offers
**مصدر التعريف:** `2026_06_18_100005_create_consulting_offers_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_id` | bigint unsigned | NO | - | - | consulting_requests.id | - | - | - | مفتاح خارجي |
| `office_id` | bigint unsigned | NO | - | - | consulting_offices.id | - | - | - | مفتاح خارجي |
| `methodology_text` | text | NO | - | - | - | - | - | - | - |
| `price` | decimal(12,2) | NO | - | - | - | - | - | - | - |
| `sample_attachments` | varchar(255) | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, accepted, rejected | - |
| `submitted_at` | timestamp | YES | - | - | - | - | - | - | - |
| `seen_by_client_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → consulting_contracts (One-to-Many, مؤكدة)
- ← consulting_requests (One-to-Many, مؤكدة)
- ← consulting_offices (One-to-Many, مؤكدة)

---

## جدول `consulting_office_specializations`

**الوظيفة:** جدول: consulting office specializations
**مصدر التعريف:** `2026_06_18_100002_create_consulting_office_specializations_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `office_id` | bigint unsigned | NO | - | - | consulting_offices.id | - | - | - | مفتاح خارجي |
| `category_code` | varchar(10) | NO | - | - | - | - | - | - | - |
| `sector` | varchar(50) | YES | - | - | - | - | - | - | - |
| `sample_work_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_verified` | boolean | NO | false | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_offices (One-to-Many, مؤكدة)

---

## جدول `consulting_office_violations`

**الوظيفة:** جدول: consulting office violations
**مصدر التعريف:** `2026_06_18_100010_create_consulting_office_violations_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `office_id` | bigint unsigned | NO | - | - | consulting_offices.id | - | - | - | مفتاح خارجي |
| `reported_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `violation_type` | varchar(100) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | open | - | - | - | - | open, resolved | - |
| `action_taken` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_offices (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_offices`

**الوظيفة:** مكاتب الاستشارات
**مصدر التعريف:** `2026_06_18_100001_create_consulting_offices_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(30) | YES | - | - | - | - | - | - | - |
| `license_number` | varchar(100) | YES | - | - | - | - | - | - | - |
| `license_date` | date | YES | - | - | - | - | - | - | - |
| `license_expiry` | date | YES | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `website` | varchar(255) | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, active, suspended, revoked | - |
| `accreditation_date` | date | YES | - | - | - | - | - | - | - |
| `overall_rating` | decimal(3,2) | NO | 0 | - | - | - | - | - | - |
| `on_time_rate` | decimal(5,2) | NO | 0 | - | - | - | - | - | - |
| `report_accept_rate` | decimal(5,2) | NO | 0 | - | - | - | - | - | - |
| `bio` | text | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |

**العلاقات:**
- → consulting_contracts (One-to-Many, مؤكدة)
- → consulting_offers (One-to-Many, مؤكدة)
- → consulting_office_specializations (One-to-Many, مؤكدة)
- → consulting_office_violations (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- → consulting_reviews (One-to-Many, مؤكدة)

---

## جدول `consulting_reports`

**الوظيفة:** جدول: consulting reports
**مصدر التعريف:** `2026_06_18_100008_create_consulting_reports_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `contract_id` | bigint unsigned | NO | - | - | consulting_contracts.id | - | - | - | مفتاح خارجي |
| `request_id` | bigint unsigned | NO | - | - | consulting_requests.id | - | - | - | مفتاح خارجي |
| `report_pdf_path` | varchar(255) | NO | - | - | - | - | - | - | - |
| `submission_date` | timestamp | YES | - | - | - | - | - | - | - |
| `review_status` | enum | NO | pending | - | - | - | - | pending, approved, returned | - |
| `reviewer_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `review_date` | timestamp | YES | - | - | - | - | - | - | - |
| `reviewer_notes` | text | YES | - | - | - | - | - | - | - |
| `recommendation_details` | text | YES | - | - | - | - | - | - | - |
| `isic4_recommendation` | varchar(10) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_contracts (One-to-Many, مؤكدة)
- ← consulting_requests (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_request_attachments`

**الوظيفة:** جدول: consulting request attachments
**مصدر التعريف:** `2026_06_18_100004_create_consulting_request_attachments_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_id` | bigint unsigned | NO | - | - | consulting_requests.id | - | - | - | مفتاح خارجي |
| `uploader_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `file_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `file_path` | varchar(255) | NO | - | - | - | - | - | - | - |
| `file_type` | varchar(50) | YES | - | - | - | - | - | - | - |
| `file_size` | bigint unsigned | YES | - | - | - | - | - | - | - |
| `upload_stage` | enum | NO | request | - | - | - | - | request, execution, report | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_requests (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_requests`

**الوظيفة:** طلبات الاستشارات
**مصدر التعريف:** `2026_06_18_100003_create_consulting_requests_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_code` | varchar(30) | YES | - | - | - | - | - | - | - |
| `user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `branch_manager_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `category_code` | varchar(10) | NO | - | - | - | - | - | - | - |
| `request_type` | enum | NO | existing | - | - | - | - | new_project, existing, financing, classification | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | NO | - | - | - | - | - | - | - |
| `project_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `economic_activity` | varchar(255) | YES | - | - | - | - | - | - | - |
| `isic4_code` | varchar(10) | YES | - | - | - | - | - | - | - |
| `budget_min` | decimal(12,2) | YES | - | - | - | - | - | - | - |
| `budget_max` | decimal(12,2) | YES | - | - | - | - | - | - | - |
| `branch_notes` | text | YES | - | - | - | - | - | - | - |
| `submitted_at` | timestamp | YES | - | - | - | - | - | - | - |
| `completed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `offers_deadline` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |

**العلاقات:**
- → consulting_contracts (One-to-Many, مؤكدة)
- → consulting_offers (One-to-Many, مؤكدة)
- → consulting_reports (One-to-Many, مؤكدة)
- → consulting_request_attachments (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `consulting_reviews`

**الوظيفة:** جدول: consulting reviews
**مصدر التعريف:** `2026_06_18_100009_create_consulting_reviews_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `contract_id` | bigint unsigned | NO | - | - | consulting_contracts.id | - | - | - | مفتاح خارجي |
| `office_id` | bigint unsigned | NO | - | - | consulting_offices.id | - | - | - | مفتاح خارجي |
| `reviewer_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `comment` | text | YES | - | - | - | - | - | - | - |
| `is_published` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← consulting_contracts (One-to-Many, مؤكدة)
- ← consulting_offices (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `course_registration_request_members`

**الوظيفة:** جدول: course registration request members
**مصدر التعريف:** `2026_04_19_175950_create_course_registration_request_members_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `course_registration_request_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainee_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `full_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `birth_date` | date | YES | - | - | - | - | - | - | - |
| `gender` | enum | YES | - | - | - | - | - | male, female | - |
| `education_level` | varchar(100) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← course_registration_requests (One-to-Many, قوية)
- ← trainees (One-to-Many (inverse), قوية)

---

## جدول `course_registration_requests`

**الوظيفة:** جدول: course registration requests
**مصدر التعريف:** `2026_04_19_175918_create_course_registration_requests_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_number` | varchar(100) | NO | - | - | - | - | - | - | - |
| `training_course_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `submitted_by_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `submitted_by_type` | varchar(50) | YES | - | - | - | - | - | - | - |
| `applicant_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `applicant_phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `applicant_email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `guardian_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `guardian_phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `guardian_national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `guardian_confirmed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `completed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_courses (One-to-Many (inverse), قوية)
- ← users (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- → course_registration_request_members (One-to-Many, قوية)
- ← training_courses (One-to-Many, محتملة)

---

## جدول `document_electronic_signatures`

**الوظيفة:** جدول: document electronic signatures
**مصدر التعريف:** `2026_06_22_200000_create_executive_electronic_signatures.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `signable_type` | varchar(255) | NO | - | - | - | - | - | - | - |
| `signable_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `role_key` | varchar(60) | NO | - | - | - | - | - | - | - |
| `signed_by_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `signer_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `signer_title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `document_hash` | varchar(64) | NO | - | - | - | - | - | - | - |
| `signature_hmac` | varchar(64) | NO | - | - | - | - | - | - | - |
| `verification_code` | varchar(32) | NO | - | - | - | - | - | - | - |
| `signed_at` | timestamp | NO | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `user_electronic_signature_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `signature_image_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `signature_image_hash` | varchar(64) | YES | - | - | - | - | - | - | - |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← certificate_approvals (Polymorphic One-to-One, قوية)
- ← user_electronic_signatures (One-to-Many (inverse), قوية)
- ← (polymorphic) (Polymorphic, قوية)

---

## جدول `entrepreneur_profiles`

**الوظيفة:** ملفات رواد الأعمال
**مصدر التعريف:** `2026_06_21_240000_create_entrepreneur_profiles_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `full_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `governorate` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `education_level` | varchar(255) | YES | - | - | - | - | - | - | - |
| `specialization` | varchar(255) | YES | - | - | - | - | - | - | - |
| `project_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `project_field` | varchar(255) | YES | - | - | - | - | - | - | - |
| `project_field_other` | varchar(255) | YES | - | - | - | - | - | - | - |
| `founding_year` | year | YES | - | - | - | - | - | - | - |
| `executive_summary` | text | YES | - | - | - | - | - | - | - |
| `elevator_pitch` | text | YES | - | - | - | - | - | - | - |
| `readiness_stage` | varchar(255) | YES | - | - | - | - | - | - | - |
| `has_prototype` | boolean | NO | false | - | - | - | - | - | - |
| `tested_with_users` | boolean | NO | false | - | - | - | - | - | - |
| `testing_results` | text | YES | - | - | - | - | - | - | - |
| `problem_description` | text | YES | - | - | - | - | - | - | - |
| `target_customers` | text | YES | - | - | - | - | - | - | - |
| `differentiation` | text | YES | - | - | - | - | - | - | - |
| `competitive_advantages` | json | YES | - | - | - | - | - | - | - |
| `team_size_range` | varchar(255) | YES | - | - | - | - | - | - | - |
| `team_roles` | json | YES | - | - | - | - | - | - | - |
| `technologies` | json | YES | - | - | - | - | - | - | - |
| `market_validation_methods` | json | YES | - | - | - | - | - | - | - |
| `target_market` | varchar(255) | YES | - | - | - | - | - | - | - |
| `current_users_range` | varchar(255) | YES | - | - | - | - | - | - | - |
| `current_customers_range` | varchar(255) | YES | - | - | - | - | - | - | - |
| `has_revenue` | boolean | NO | false | - | - | - | - | - | - |
| `revenue_sources` | json | YES | - | - | - | - | - | - | - |
| `funding_sources` | json | YES | - | - | - | - | - | - | - |
| `seeking_investment` | boolean | NO | false | - | - | - | - | - | - |
| `investment_needed_range` | varchar(255) | YES | - | - | - | - | - | - | - |
| `challenges` | json | YES | - | - | - | - | - | - | - |
| `jobs_3years_range` | varchar(255) | YES | - | - | - | - | - | - | - |
| `scalability_outside_syria` | varchar(255) | YES | - | - | - | - | - | - | - |
| `support_needed` | json | YES | - | - | - | - | - | - | - |
| `previous_participation` | json | YES | - | - | - | - | - | - | - |
| `additional_notes` | text | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | draft | - | - | - | - | draft, submitted, under_review, approved, rejected | - |
| `reviewer_notes` | text | YES | - | - | - | - | - | - | - |
| `reviewed_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `reviewed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `executive_signer_profiles`

**الوظيفة:** جدول: executive signer profiles
**مصدر التعريف:** `2026_06_22_200000_create_executive_electronic_signatures.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `role_key` | varchar(60) | NO | - | - | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `signer_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `signer_title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `signature_image_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)

---

## جدول `failed_jobs`

**الوظيفة:** جدول: failed jobs
**مصدر التعريف:** `0001_01_01_000002_create_jobs_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `uuid` | varchar(255) | NO | - | - | - | - | - | - | - |
| `connection` | text | NO | - | - | - | - | - | - | - |
| `queue` | text | NO | - | - | - | - | - | - | - |
| `payload` | longtext | NO | - | - | - | - | - | - | - |
| `exception` | longtext | NO | - | - | - | - | - | - | - |
| `failed_at` | timestamp | NO | - | - | - | - | - | - | - |

---

## جدول `financial_records`

**الوظيفة:** جدول: financial records
**مصدر التعريف:** `2026_06_19_100000_create_agreements_and_financial_records_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `record_type` | varchar(255) | NO | - | - | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `amount` | decimal(14,2) | NO | 0 | - | - | - | - | - | - |
| `currency` | varchar(8) | NO | SYP | - | - | - | - | - | - |
| `status` | varchar(255) | NO | draft | - | - | - | - | - | - |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← branches (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `funded_loans`

**الوظيفة:** جدول: funded loans
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `funding_partner_id` | bigint unsigned | YES | - | - | funding_partners.id | - | - | - | مفتاح خارجي |
| `loan_number` | varchar(255) | NO | - | - | - | - | - | - | - |
| `approved_amount` | decimal(15,2) | NO | - | - | - | - | - | - | - |
| `currency` | varchar(8) | NO | SYP | - | - | - | - | - | - |
| `interest_type` | enum | NO | interest | - | - | - | - | interest, free, profit_margin | - |
| `interest_rate` | decimal(8,4) | YES | - | - | - | - | - | - | - |
| `profit_margin` | decimal(8,4) | YES | - | - | - | - | - | - | - |
| `installment_amount` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `start_date` | date | YES | - | - | - | - | - | - | - |
| `end_date` | date | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | active | - | - | - | - | active, paid, defaulted, restructured, closed | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)
- ← funding_partners (One-to-Many, مؤكدة)
- → loan_payments (One-to-Many, مؤكدة)

---

## جدول `funding_application_details`

**الوظيفة:** جدول: funding application details
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `owner_experience` | text | YES | - | - | - | - | - | - | - |
| `monthly_revenue` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `monthly_expenses` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `existing_debts` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `assets_description` | text | YES | - | - | - | - | - | - | - |
| `market_description` | text | YES | - | - | - | - | - | - | - |
| `challenges` | text | YES | - | - | - | - | - | - | - |
| `requested_support` | text | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)

---

## جدول `funding_applications`

**الوظيفة:** طلبات التمويل
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `application_number` | varchar(255) | NO | - | - | - | - | - | - | - |
| `applicant_user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `applicant_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `national_id` | varchar(255) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | NO | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | NO | - | - | branches.id | - | - | - | مفتاح خارجي |
| `project_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `project_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `project_sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `project_size` | enum | NO | small | - | - | - | - | micro, small, medium | - |
| `business_stage` | enum | NO | startup | - | - | - | - | idea, startup, existing, expansion | - |
| `requested_amount` | decimal(15,2) | NO | - | - | - | - | - | - | - |
| `currency` | varchar(8) | NO | SYP | - | - | - | - | - | - |
| `financing_type` | enum | NO | capital | - | - | - | - | capital, working_capital, mixed | - |
| `purpose` | text | YES | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `current_stage` | varchar(255) | YES | - | - | - | - | - | - | - |
| `submitted_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `updated_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → consultant_assignments (One-to-Many, مؤكدة)
- → consultant_reports (One-to-Many, مؤكدة)
- → funded_loans (One-to-Many, مؤكدة)
- → funding_application_details (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → funding_documents (One-to-Many, مؤكدة)
- → funding_partner_assignments (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)

---

## جدول `funding_documents`

**الوظيفة:** جدول: funding documents
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `document_type` | varchar(255) | NO | - | - | - | - | - | - | - |
| `file_path` | varchar(255) | NO | - | - | - | - | - | - | - |
| `original_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `mime_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `size` | bigint unsigned | NO | - | - | - | - | - | - | - |
| `uploaded_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `funding_partner_assignments`

**الوظيفة:** جدول: funding partner assignments
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | NO | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `funding_partner_id` | bigint unsigned | NO | - | - | funding_partners.id | - | - | - | مفتاح خارجي |
| `assigned_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `assigned_at` | timestamp | NO | - | - | - | - | - | - | - |
| `status` | enum | NO | sent | - | - | - | - | sent, under_review, approved, rejected, funded | - |
| `approved_amount` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `approved_currency` | varchar(8) | YES | - | - | - | - | - | - | - |
| `decision_notes` | text | YES | - | - | - | - | - | - | - |
| `decision_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funding_applications (One-to-Many, مؤكدة)
- ← funding_partners (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `funding_partners`

**الوظيفة:** جدول: funding partners
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `partner_type` | enum | NO | bank | - | - | - | - | bank, fund, guarantee_company, donor, other | - |
| `contact_person` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | active | - | - | - | - | active, inactive | - |
| `created_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `license_number` | varchar(255) | YES | - | - | - | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `supervised_by_type` | varchar(50) | NO | central_bank | - | - | - | - | - | - |
| `updated_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |

**العلاقات:**
- → funded_loans (One-to-Many, مؤكدة)
- → funding_partner_assignments (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → users (One-to-Many, مؤكدة)

---

## جدول `governorates`

**الوظيفة:** المحافظات السورية
**مصدر التعريف:** `2026_06_17_100000_create_governorates_and_branches_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name_ar` | varchar(100) | NO | - | - | - | - | - | - | - |
| `name_en` | varchar(100) | YES | - | - | - | - | - | - | - |
| `code` | varchar(20) | NO | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → administrative_units (One-to-Many, مؤكدة)
- → agreements (One-to-Many, مؤكدة)
- → branches (One-to-Many, مؤكدة)
- → consultant_offices (One-to-Many, مؤكدة)
- → consulting_offices (One-to-Many, مؤكدة)
- → consulting_requests (One-to-Many, مؤكدة)
- → financial_records (One-to-Many, مؤكدة)
- → funding_applications (One-to-Many, مؤكدة)
- → incubators (One-to-Many, مؤكدة)
- → job_postings (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → training_supervisors (One-to-Many, مؤكدة)
- → users (One-to-Many, مؤكدة)
- → branchs (One-to-Many (inverse), قوية)
- → certificates (One-to-Many (inverse), قوية)
- → course_registration_requests (One-to-Many (inverse), قوية)
- → trainees (One-to-Many (inverse), قوية)
- → trainee_registration_requests (One-to-Many (inverse), قوية)
- → trainers (One-to-Many (inverse), قوية)
- → trainer_registration_requests (One-to-Many (inverse), قوية)
- → training_centers (One-to-Many (inverse), قوية)
- → training_center_registration_requests (One-to-Many (inverse), قوية)
- → training_courses (One-to-Many (inverse), قوية)

---

## جدول `inbox_message_reads`

**الوظيفة:** جدول: inbox message reads
**مصدر التعريف:** `2026_06_19_000002_create_inbox_messages_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `message_id` | bigint unsigned | NO | - | - | inbox_messages.id | - | - | - | مفتاح خارجي |
| `user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `read_at` | timestamp | NO | - | - | - | - | - | - | - |

**العلاقات:**
- ← inbox_messages (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `inbox_messages`

**الوظيفة:** صندوق الوارد الداخلي
**مصدر التعريف:** `2026_06_19_000002_create_inbox_messages_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `sender_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `sender_role` | varchar(40) | NO | admin | - | - | - | - | - | - |
| `recipient_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `is_broadcast` | boolean | NO | false | - | - | - | - | - | - |
| `broadcast_role` | varchar(255) | YES | - | - | - | - | - | - | - |
| `subject` | varchar(255) | NO | - | - | - | - | - | - | - |
| `body` | text | NO | - | - | - | - | - | - | - |
| `requires_reply` | boolean | NO | false | - | - | - | - | - | - |
| `priority` | enum | NO | normal | - | - | - | - | normal, high, urgent | - |
| `attachments` | json | YES | - | - | - | - | - | - | - |
| `parent_id` | bigint unsigned | YES | - | - | inbox_messages.id | - | - | - | مفتاح خارجي |
| `is_read` | boolean | NO | false | - | - | - | - | - | - |
| `read_at` | timestamp | YES | - | - | - | - | - | - | - |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → inbox_message_reads (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → inbox_messages (One-to-Many, مؤكدة)

---

## جدول `incubated_projects`

**الوظيفة:** جدول: incubated projects
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `application_id` | bigint unsigned | NO | - | - | incubation_applications.id | - | - | - | مفتاح خارجي |
| `incubator_id` | bigint unsigned | NO | - | - | incubators.id | - | - | - | مفتاح خارجي |
| `owner_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `mentor_user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `project_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `stage` | enum | NO | seed | - | - | - | - | seed, early, growth, exit | - |
| `start_date` | date | NO | - | - | - | - | - | - | - |
| `expected_end_date` | date | YES | - | - | - | - | - | - | - |
| `actual_end_date` | date | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | active | - | - | - | - | active, graduated, withdrawn, terminated | - |
| `current_revenue` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `current_employees` | int | NO | 0 | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← incubation_applications (One-to-Many, مؤكدة)
- ← incubators (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → incubation_progress_reports (One-to-Many, مؤكدة)
- → mentoring_sessions (One-to-Many, مؤكدة)
- → success_stories (One-to-Many, مؤكدة)
- ← incubation_applications (One-to-Many (inverse), قوية)

---

## جدول `incubation_applications`

**الوظيفة:** طلبات الاحتضان
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `application_number` | varchar(255) | NO | - | - | - | - | - | - | - |
| `incubator_id` | bigint unsigned | NO | - | - | incubators.id | - | - | - | مفتاح خارجي |
| `program_id` | bigint unsigned | YES | - | - | incubation_programs.id | - | - | - | مفتاح خارجي |
| `applicant_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `project_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `project_sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `business_stage` | enum | NO | idea | - | - | - | - | idea, pre_seed, seed, early, growth | - |
| `project_description` | text | NO | - | - | - | - | - | - | - |
| `problem_statement` | text | YES | - | - | - | - | - | - | - |
| `target_market` | text | YES | - | - | - | - | - | - | - |
| `team_size` | int | NO | 1 | - | - | - | - | - | - |
| `has_prototype` | boolean | NO | false | - | - | - | - | - | - |
| `has_revenue` | boolean | NO | false | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, under_review, accepted, rejected, withdrawn | - |
| `reviewer_notes` | text | YES | - | - | - | - | - | - | - |
| `reviewed_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `reviewed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `tech_readiness_level` | tinyint | YES | - | - | - | - | - | - | - |
| `revenue_model` | varchar(255) | YES | - | - | - | - | - | - | - |
| `demo_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `github_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `has_ip` | boolean | NO | false | - | - | - | - | - | - |
| `ip_description` | varchar(255) | YES | - | - | - | - | - | - | - |
| `tech_stack` | json | YES | - | - | - | - | - | - | - |
| `target_platform` | varchar(255) | YES | - | - | - | - | - | - | - |
| `funding_needed` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `funding_stage` | varchar(255) | YES | - | - | - | - | - | - | - |
| `competitive_advantage` | text | YES | - | - | - | - | - | - | - |

**العلاقات:**
- → incubated_projects (One-to-Many, مؤكدة)
- ← incubators (One-to-Many, مؤكدة)
- ← incubation_programs (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → incubated_projects (One-to-Many (inverse), قوية)
- ← incubation_programs (One-to-Many (inverse), قوية)

---

## جدول `incubation_programs`

**الوظيفة:** جدول: incubation programs
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `incubator_id` | bigint unsigned | NO | - | - | incubators.id | - | - | - | مفتاح خارجي |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `duration_months` | int | NO | 12 | - | - | - | - | - | - |
| `seats` | int | NO | 10 | - | - | - | - | - | - |
| `start_date` | date | YES | - | - | - | - | - | - | - |
| `end_date` | date | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | open | - | - | - | - | open, closed, completed | - |
| `requirements` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → incubation_applications (One-to-Many, مؤكدة)
- ← incubators (One-to-Many, مؤكدة)
- → incubation_applications (One-to-Many (inverse), قوية)

---

## جدول `incubation_progress_reports`

**الوظيفة:** جدول: incubation progress reports
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `project_id` | bigint unsigned | NO | - | - | incubated_projects.id | - | - | - | مفتاح خارجي |
| `submitted_by` | bigint unsigned | NO | - | - | users.id | - | - | - | - |
| `period_type` | enum | NO | monthly | - | - | - | - | monthly, quarterly | - |
| `period_label` | varchar(255) | NO | - | - | - | - | - | - | - |
| `revenue` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `employees` | int | YES | - | - | - | - | - | - | - |
| `customers` | int | YES | - | - | - | - | - | - | - |
| `achievements` | text | YES | - | - | - | - | - | - | - |
| `challenges` | text | YES | - | - | - | - | - | - | - |
| `next_steps` | text | YES | - | - | - | - | - | - | - |
| `overall_rating` | int | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← incubated_projects (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `incubators`

**الوظيفة:** حاضنات الأعمال
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `location` | varchar(255) | YES | - | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `manager_user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `capacity` | int | NO | 20 | - | - | - | - | - | - |
| `status` | enum | NO | active | - | - | - | - | active, inactive, suspended | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → incubated_projects (One-to-Many, مؤكدة)
- → incubation_applications (One-to-Many, مؤكدة)
- → incubation_programs (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → success_stories (One-to-Many, مؤكدة)

---

## جدول `job_applications`

**الوظيفة:** جدول: job applications
**مصدر التعريف:** `2026_06_17_140000_create_workforce_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `job_posting_id` | bigint unsigned | YES | - | - | job_postings.id | - | - | - | مفتاح خارجي |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `applicant_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `specialty` | varchar(255) | YES | - | - | - | - | - | - | - |
| `city` | varchar(255) | YES | - | - | - | - | - | - | - |
| `experience_years` | varchar(255) | YES | - | - | - | - | - | - | - |
| `summary` | text | YES | - | - | - | - | - | - | - |
| `cv_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, reviewed, accepted, rejected | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← job_postings (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `job_batches`

**الوظيفة:** جدول: job batches
**مصدر التعريف:** `0001_01_01_000002_create_jobs_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | varchar(255) | NO | - | - | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `total_jobs` | int | NO | - | - | - | - | - | - | - |
| `pending_jobs` | int | NO | - | - | - | - | - | - | - |
| `failed_jobs` | int | NO | - | - | - | - | - | - | - |
| `failed_job_ids` | longtext | NO | - | - | - | - | - | - | - |
| `options` | mediumtext | YES | - | - | - | - | - | - | - |
| `cancelled_at` | int | YES | - | - | - | - | - | - | - |
| `created_at` | int | NO | - | - | - | - | - | - | تاريخ الإنشاء |
| `finished_at` | int | YES | - | - | - | - | - | - | - |

---

## جدول `job_postings`

**الوظيفة:** إعلانات الوظائف
**مصدر التعريف:** `2026_06_17_140000_create_workforce_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `organization_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `city` | varchar(255) | YES | - | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `employment_type` | varchar(255) | NO | full_time | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `skills` | text | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | published | - | - | - | - | draft, published, closed | - |
| `contact_email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `contact_phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → job_applications (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)

---

## جدول `jobs`

**الوظيفة:** جدول: jobs
**مصدر التعريف:** `0001_01_01_000002_create_jobs_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `queue` | varchar(255) | NO | - | - | - | - | - | - | - |
| `payload` | longtext | NO | - | - | - | - | - | - | - |

---

## جدول `legacy_import_id_mappings`

**الوظيفة:** جدول: legacy import id mappings
**مصدر التعريف:** `2026_06_17_120000_create_legacy_import_tracking_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `source` | varchar(50) | NO | - | - | - | - | - | - | - |
| `entity` | varchar(100) | NO | - | - | - | - | - | - | - |
| `old_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `new_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `dedupe_key` | varchar(255) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← news (One-to-Many, محتملة)

---

## جدول `legacy_import_runs`

**الوظيفة:** جدول: legacy import runs
**مصدر التعريف:** `2026_06_17_120000_create_legacy_import_tracking_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `source` | varchar(255) | NO | - | - | - | - | - | - | - |
| `dry_run` | boolean | NO | true | - | - | - | - | - | - |
| `status` | varchar(255) | NO | pending | - | - | - | - | - | - |
| `report` | json | YES | - | - | - | - | - | - | - |
| `started_at` | timestamp | YES | - | - | - | - | - | - | - |
| `finished_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

---

## جدول `loan_payments`

**الوظيفة:** جدول: loan payments
**مصدر التعريف:** `2026_06_21_100000_create_funding_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `funded_loan_id` | bigint unsigned | NO | - | - | funded_loans.id | - | - | - | مفتاح خارجي |
| `due_date` | date | NO | - | - | - | - | - | - | - |
| `paid_date` | date | YES | - | - | - | - | - | - | - |
| `amount_due` | decimal(15,2) | NO | - | - | - | - | - | - | - |
| `amount_paid` | decimal(15,2) | NO | 0 | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, paid, late, partial, defaulted | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← funded_loans (One-to-Many, مؤكدة)

---

## جدول `mentoring_sessions`

**الوظيفة:** جدول: mentoring sessions
**مصدر التعريف:** `2026_06_21_200000_create_incubation_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `project_id` | bigint unsigned | NO | - | - | incubated_projects.id | - | - | - | مفتاح خارجي |
| `mentor_user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `session_date` | date | NO | - | - | - | - | - | - | - |
| `duration_minutes` | int | NO | 60 | - | - | - | - | - | - |
| `topic` | varchar(255) | NO | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `action_items` | text | YES | - | - | - | - | - | - | - |
| `rating` | int | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | scheduled | - | - | - | - | scheduled, completed, cancelled | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← incubated_projects (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `need_action_logs`

**الوظيفة:** جدول: need action logs
**مصدر التعريف:** `2026_06_23_100000_create_needs_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `need_id` | bigint unsigned | NO | - | - | needs.id | - | - | - | مفتاح خارجي |
| `action` | varchar(255) | NO | - | - | - | - | - | - | - |
| `from_status` | varchar(255) | YES | - | - | - | - | - | - | - |
| `to_status` | varchar(255) | YES | - | - | - | - | - | - | - |
| `performed_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `note` | text | YES | - | - | - | - | - | - | - |
| `payload` | json | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← needs (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `need_lookups`

**الوظيفة:** جدول: need lookups
**مصدر التعريف:** `2026_06_23_100000_create_needs_module_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `lookup_type` | varchar(255) | NO | - | - | - | - | - | - | - |
| `value` | varchar(255) | NO | - | - | - | - | - | - | - |
| `label` | varchar(255) | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

---

## جدول `needs`

**الوظيفة:** احتياجات/طلبات الدعم
**مصدر التعريف:** `2026_06_23_100000_create_needs_module_tables.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `need_code` | varchar(255) | NO | - | - | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `summary` | text | YES | - | - | - | - | - | - | - |
| `need_owner_type` | enum | NO | citizen | - | - | - | - | citizen, state | - |
| `need_scope` | enum | NO | individual | - | - | - | - | individual, project, local, governorate, national, sectoral | - |
| `need_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `need_category` | varchar(255) | YES | - | - | - | - | - | - | - |
| `need_complexity` | enum | NO | specific | - | - | - | - | general, specific | - |
| `source_platform` | enum | NO | gis | - | - | - | - | gis, legacy_gis, training, finance, incubator, workforce, manual, other | - |
| `source_module` | varchar(255) | YES | - | - | - | - | - | - | - |
| `source_record_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `district_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `administrative_unit_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `countryside_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `locality_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `village_or_neighborhood` | varchar(255) | YES | - | - | - | - | - | - | - |
| `address_details` | text | YES | - | - | - | - | - | - | - |
| `latitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `longitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `location_source` | varchar(255) | YES | - | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `economic_sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `syrsic_section` | varchar(255) | YES | - | - | - | - | - | - | - |
| `syrsic_division` | varchar(255) | YES | - | - | - | - | - | - | - |
| `syrsic_group` | varchar(255) | YES | - | - | - | - | - | - | - |
| `syrsic_class` | varchar(255) | YES | - | - | - | - | - | - | - |
| `syrsic_activity` | varchar(255) | YES | - | - | - | - | - | - | - |
| `priority` | varchar(255) | NO | medium | - | - | - | - | - | - |
| `status` | varchar(255) | NO | pending_governorate_review | - | - | - | - | - | - |
| `approval_status` | varchar(255) | YES | - | - | - | - | - | - | - |
| `state_need_level` | varchar(255) | YES | - | - | - | - | - | - | - |
| `citizen_need_profile` | varchar(255) | YES | - | - | - | - | - | - | - |
| `responsible_entity` | varchar(255) | YES | - | - | - | - | - | - | - |
| `proposed_intervention` | varchar(255) | YES | - | - | - | - | - | - | - |
| `applicant_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `applicant_phone` | varchar(255) | YES | - | - | - | - | - | - | - |
| `applicant_email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `applicant_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `organization_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `impact_level` | varchar(255) | YES | - | - | - | - | - | - | - |
| `urgency_level` | varchar(255) | YES | - | - | - | - | - | - | - |
| `expected_duration` | varchar(255) | YES | - | - | - | - | - | - | - |
| `available_partners` | text | YES | - | - | - | - | - | - | - |
| `obstacles` | text | YES | - | - | - | - | - | - | - |
| `requirements` | text | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `metadata` | json | YES | - | - | - | - | - | - | - |
| `funding_application_id` | bigint unsigned | YES | - | - | funding_applications.id | - | - | - | مفتاح خارجي |
| `training_course_id` | bigint unsigned | YES | - | - | training_courses.id | - | - | - | مفتاح خارجي |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `updated_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `reviewed_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `rejected_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `classified_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `returned_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `resolved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `reviewer_note` | text | YES | - | - | - | - | - | - | - |
| `approval_note` | text | YES | - | - | - | - | - | - | - |
| `rejection_reason` | text | YES | - | - | - | - | - | - | - |
| `return_reason` | text | YES | - | - | - | - | - | - | - |
| `classification_note` | text | YES | - | - | - | - | - | - | - |
| `reviewed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `rejected_at` | timestamp | YES | - | - | - | - | - | - | - |
| `classified_at` | timestamp | YES | - | - | - | - | - | - | - |
| `returned_at` | timestamp | YES | - | - | - | - | - | - | - |
| `resolved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `is_public` | boolean | NO | false | - | - | - | - | - | - |
| `is_mapped` | boolean | NO | false | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |

**العلاقات:**
- → need_action_logs (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← funding_applications (One-to-Many, مؤكدة)
- ← training_courses (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← training_courses (One-to-Many (inverse), قوية)

---

## جدول `news`

**الوظيفة:** الأخبار
**مصدر التعريف:** `2026_06_21_230000_create_news_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `slug` | varchar(255) | NO | - | - | - | - | - | - | - |
| `summary` | text | NO | - | - | - | - | - | - | - |
| `body` | longtext | NO | - | - | - | - | - | - | - |
| `image_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `category` | varchar(255) | YES | - | - | - | - | - | - | - |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `status` | enum | NO | draft | - | - | - | - | draft, published, archived | - |
| `is_pinned` | boolean | NO | false | - | - | - | - | - | - |
| `published_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → legacy_import_id_mappings (One-to-Many, محتملة)

---

## جدول `notifications`

**الوظيفة:** إشعارات المستخدمين
**مصدر التعريف:** `2026_06_19_000001_create_notifications_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `type` | varchar(60) | NO | - | - | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `body` | text | YES | - | - | - | - | - | - | - |
| `action_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `action_label` | varchar(60) | YES | - | - | - | - | - | - | - |
| `icon` | varchar(60) | NO | bi-bell-fill | - | - | - | - | - | - |
| `color` | varchar(20) | NO | primary | - | - | - | - | - | - |
| `reference_type` | varchar(60) | YES | - | - | - | - | - | - | - |
| `reference_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `is_read` | boolean | NO | false | - | - | - | - | - | - |
| `read_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | NO | - | - | - | - | - | - | تاريخ الإنشاء |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← (polymorphic) (Polymorphic, قوية)

---

## جدول `password_reset_tokens`

**الوظيفة:** جدول: password reset tokens
**مصدر التعريف:** `0001_01_01_000000_create_users_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `email` | varchar(255) | NO | - | - | - | - | - | - | - |
| `token` | varchar(255) | NO | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |

---

## جدول `permissions`

**الوظيفة:** صلاحيات Spatie Permission
**مصدر التعريف:** `2026_04_03_140628_create_permission_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `guard_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

---

## جدول `personal_access_tokens`

**الوظيفة:** رموز Sanctum API
**مصدر التعريف:** `2026_04_03_140620_create_personal_access_tokens_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `tokenable_type` | varchar(255) | NO | - | - | - | - | - | - | - |
| `tokenable_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `name` | text | NO | - | - | - | - | - | - | - |
| `token` | varchar(64) | NO | - | - | - | - | - | - | - |
| `abilities` | text | YES | - | - | - | - | - | - | - |
| `last_used_at` | timestamp | YES | - | - | - | - | - | - | - |
| `expires_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← (polymorphic) (Polymorphic, قوية)

---

## جدول `sessions`

**الوظيفة:** جلسات Laravel
**مصدر التعريف:** `0001_01_01_000000_create_users_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | varchar(255) | NO | - | - | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `ip_address` | varchar(45) | YES | - | - | - | - | - | - | - |
| `user_agent` | text | YES | - | - | - | - | - | - | - |
| `payload` | longtext | NO | - | - | - | - | - | - | - |

**العلاقات:**
- ← users (One-to-Many, محتملة)

---

## جدول `staff_training_requests`

**الوظيفة:** جدول: staff training requests
**مصدر التعريف:** `2026_06_17_140000_create_workforce_platform_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `organization_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `training_field` | varchar(255) | YES | - | - | - | - | - | - | - |
| `city` | varchar(255) | YES | - | - | - | - | - | - | - |
| `details` | text | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, reviewed, scheduled, closed | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)

---

## جدول `status_histories`

**الوظيفة:** جدول: status histories
**مصدر التعريف:** `2026_07_08_210000_create_status_histories_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `model_type` | varchar(191) | NO | - | - | - | - | - | - | - |
| `model_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `from_status` | varchar(100) | YES | - | - | - | - | - | - | - |
| `to_status` | varchar(100) | NO | - | - | - | - | - | - | - |
| `changed_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `reason` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | NO | - | - | - | - | - | - | تاريخ الإنشاء |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← (polymorphic) (Polymorphic, قوية)

---

## جدول `success_stories`

**الوظيفة:** جدول: success stories
**مصدر التعريف:** `2026_06_21_220000_create_success_stories_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `slug` | varchar(255) | NO | - | - | - | - | - | - | - |
| `summary` | text | NO | - | - | - | - | - | - | - |
| `body` | longtext | NO | - | - | - | - | - | - | - |
| `incubated_project_id` | bigint unsigned | YES | - | - | incubated_projects.id | - | - | - | مفتاح خارجي |
| `incubator_id` | bigint unsigned | YES | - | - | incubators.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `hero_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `hero_title` | varchar(255) | YES | - | - | - | - | - | - | - |
| `hero_photo_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `project_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `revenue_achieved` | decimal(15,2) | YES | - | - | - | - | - | - | - |
| `jobs_created` | int | YES | - | - | - | - | - | - | - |
| `years_in_incubator` | int | YES | - | - | - | - | - | - | - |
| `current_stage` | varchar(255) | YES | - | - | - | - | - | - | - |
| `featured_quote` | text | YES | - | - | - | - | - | - | - |
| `cover_image_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `video_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `gallery` | json | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | draft | - | - | - | - | draft, published, archived | - |
| `is_featured` | boolean | NO | false | - | - | - | - | - | - |
| `published_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← incubated_projects (One-to-Many, مؤكدة)
- ← incubators (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `syria_locations`

**الوظيفة:** جدول: syria locations
**مصدر التعريف:** `2026_06_27_212105_create_syria_locations_table.php`
**Soft Delete:** لا
**Timestamps:** لا

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `gov_pcode` | varchar(10) | NO | - | - | - | - | - | - | - |
| `gov_name_en` | varchar(60) | NO | - | - | - | - | - | - | - |
| `gov_name_ar` | varchar(60) | NO | - | - | - | - | - | - | - |
| `district_pcode` | varchar(10) | NO | - | - | - | - | - | - | - |
| `district_name_en` | varchar(80) | NO | - | - | - | - | - | - | - |
| `district_name_ar` | varchar(80) | NO | - | - | - | - | - | - | - |
| `subdistrict_pcode` | varchar(10) | NO | - | - | - | - | - | - | - |
| `subdistrict_name_en` | varchar(80) | NO | - | - | - | - | - | - | - |
| `subdistrict_name_ar` | varchar(80) | NO | - | - | - | - | - | - | - |
| `community_pcode` | varchar(10) | NO | - | - | - | - | - | - | - |
| `community_name_en` | varchar(120) | NO | - | - | - | - | - | - | - |
| `community_name_ar` | varchar(120) | NO | - | - | - | - | - | - | - |
| `latitude` | decimal(9,6) | NO | - | - | - | - | - | - | - |
| `longitude` | decimal(9,6) | NO | - | - | - | - | - | - | - |

---

## جدول `trainee_registration_requests`

**الوظيفة:** جدول: trainee registration requests
**مصدر التعريف:** `2026_04_19_175843_create_trainee_registration_requests_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_number` | varchar(100) | NO | - | - | - | - | - | - | - |
| `full_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `city` | varchar(100) | YES | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `birth_date` | date | YES | - | - | - | - | - | - | - |
| `gender` | enum | YES | - | - | - | - | - | male, female | - |
| `education_level` | varchar(100) | YES | - | - | - | - | - | - | - |
| `guardian_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `guardian_phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `guardian_national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `group_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `submitted_by_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `reviewed_by_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `review_notes` | text | YES | - | - | - | - | - | - | - |
| `approved_trainee_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `rejected_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many (inverse), قوية)
- ← users (One-to-Many (inverse), قوية)
- ← trainees (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)

---

## جدول `trainees`

**الوظيفة:** المتدربون
**مصدر التعريف:** `2026_04_09_131329_create_trainees_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `trainee_code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `city` | varchar(100) | YES | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `birth_date` | date | YES | - | - | - | - | - | - | - |
| `gender` | enum | YES | - | - | - | - | - | male, female | - |
| `education_level` | varchar(100) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `governorate` | varchar(100) | YES | - | - | - | - | - | - | - |
| `district` | varchar(100) | YES | - | - | - | - | - | - | - |
| `location_visibility` | enum | NO | - | - | - | - | - | public, internal, private | - |

**العلاقات:**
- → certificates (One-to-Many (inverse), قوية)
- → course_registration_request_members (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- → workforces (One-to-One, قوية)
- → trainee_registration_requests (One-to-Many (inverse), قوية)
- → users (One-to-Many (inverse), قوية)
- → training_course_trainee (One-to-Many, محتملة)

---

## جدول `trainer_profiles`

**الوظيفة:** جدول: trainer profiles
**مصدر التعريف:** `2026_04_18_085542_create_trainer_profiles_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `headline` | varchar(255) | YES | - | - | - | - | - | - | - |
| `bio` | text | YES | - | - | - | - | - | - | - |
| `skills` | text | YES | - | - | - | - | - | - | - |
| `special_interests` | text | YES | - | - | - | - | - | - | - |
| `linkedin_summary` | text | YES | - | - | - | - | - | - | - |
| `cv_file` | varchar(255) | YES | - | - | - | - | - | - | - |
| `profile_image` | varchar(255) | YES | - | - | - | - | - | - | - |
| `visibility` | enum | NO | internal | - | - | - | - | internal, public | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← trainers (One-to-One, قوية)

---

## جدول `trainer_registration_requests`

**الوظيفة:** جدول: trainer registration requests
**مصدر التعريف:** `2026_04_19_175813_create_trainer_registration_requests_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_number` | varchar(100) | NO | - | - | - | - | - | - | - |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `full_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `national_id` | varchar(100) | YES | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `specialization` | varchar(150) | YES | - | - | - | - | - | - | - |
| `classification_requested` | varchar(100) | YES | - | - | - | - | - | - | - |
| `has_tot` | boolean | NO | false | - | - | - | - | - | - |
| `tot_certificate_number` | varchar(100) | YES | - | - | - | - | - | - | - |
| `tot_certificate_source` | varchar(255) | YES | - | - | - | - | - | - | - |
| `tot_issue_date` | date | YES | - | - | - | - | - | - | - |
| `tot_expiry_date` | date | YES | - | - | - | - | - | - | - |
| `cv_file` | varchar(255) | YES | - | - | - | - | - | - | - |
| `certificate_file` | varchar(255) | YES | - | - | - | - | - | - | - |
| `submitted_by_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `reviewed_by_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `review_notes` | text | YES | - | - | - | - | - | - | - |
| `approved_trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `rejected_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_centers (One-to-Many (inverse), قوية)
- ← users (One-to-Many (inverse), قوية)
- ← users (One-to-Many (inverse), قوية)
- ← trainers (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)

---

## جدول `trainer_training_kit`

**الوظيفة:** جدول: trainer training kit
**مصدر التعريف:** `2026_04_09_131439_create_trainer_training_kit_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_kit_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `is_authorized` | boolean | NO | true | - | - | - | - | - | - |
| `authorized_from` | date | YES | - | - | - | - | - | - | - |
| `authorized_to` | date | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_kits (Many-to-Many, قوية)
- ← trainers (One-to-Many, محتملة)

---

## جدول `trainers`

**الوظيفة:** المدربون
**مصدر التعريف:** `2026_04_09_131255_create_trainers_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `trainer_code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `specialization` | varchar(150) | YES | - | - | - | - | - | - | - |
| `classification` | varchar(100) | YES | - | - | - | - | - | - | - |
| `has_tot` | boolean | NO | false | - | - | - | - | - | - |
| `tot_certificate_number` | varchar(100) | YES | - | - | - | - | - | - | - |
| `tot_certificate_source` | varchar(255) | YES | - | - | - | - | - | - | - |
| `tot_issue_date` | date | YES | - | - | - | - | - | - | - |
| `tot_expiry_date` | date | YES | - | - | - | - | - | - | - |
| `can_train` | boolean | NO | false | - | - | - | - | - | - |
| `can_evaluate` | boolean | NO | false | - | - | - | - | - | - |
| `accreditation_start_date` | date | YES | - | - | - | - | - | - | - |
| `accreditation_end_date` | date | YES | - | - | - | - | - | - | - |
| `bio` | text | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `governorate` | varchar(100) | YES | - | - | - | - | - | - | - |
| `city` | varchar(100) | YES | - | - | - | - | - | - | - |
| `district` | varchar(100) | YES | - | - | - | - | - | - | - |
| `service_areas` | json | YES | - | - | - | - | - | - | - |
| `location_visibility` | enum | NO | - | - | - | - | - | public, internal, private | - |

**العلاقات:**
- → certificates (One-to-Many (inverse), قوية)
- ← training_centers (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- → training_courses (One-to-Many, قوية)
- → training_kit_nominations (One-to-Many, قوية)
- → trainer_profiles (One-to-One, قوية)
- → trainer_registration_requests (One-to-Many (inverse), قوية)
- → users (One-to-Many (inverse), قوية)
- → trainer_training_kit (One-to-Many, محتملة)

---

## جدول `training_center_platforms`

**الوظيفة:** جدول: training center platforms
**مصدر التعريف:** `2026_04_09_131216_create_training_center_platforms_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `platform_name` | varchar(100) | NO | - | - | - | - | - | - | - |
| `platform_url` | varchar(255) | YES | - | - | - | - | - | - | - |
| `approved_at` | date | YES | - | - | - | - | - | - | - |
| `expires_at` | date | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_centers (One-to-Many, قوية)

---

## جدول `training_center_registration_requests`

**الوظيفة:** جدول: training center registration requests
**مصدر التعريف:** `2026_04_19_175352_create_training_center_registration_requests_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `request_number` | varchar(100) | NO | - | - | - | - | - | - | - |
| `center_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `city` | varchar(100) | NO | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `classification_requested` | varchar(100) | YES | - | - | - | - | - | - | - |
| `supports_offline_training` | boolean | NO | true | - | - | - | - | - | - |
| `supports_online_training` | boolean | NO | false | - | - | - | - | - | - |
| `latitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `longitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `license_number` | varchar(255) | YES | - | - | - | - | - | - | - |
| `license_issue_date` | date | YES | - | - | - | - | - | - | - |
| `license_issued_by` | varchar(255) | YES | - | - | - | - | - | - | - |
| `license_image_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `license_file` | varchar(255) | YES | - | - | - | - | - | - | - |
| `accreditation_file` | varchar(255) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `submitted_by_user_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `reviewed_by_user_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `review_notes` | text | YES | - | - | - | - | - | - | - |
| `decision_notes` | text | YES | - | - | - | - | - | - | - |
| `approved_training_center_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `reviewed_at` | timestamp | YES | - | - | - | - | - | - | - |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `rejected_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many (inverse), قوية)
- ← users (One-to-Many (inverse), قوية)
- ← training_centers (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)

---

## جدول `training_centers`

**الوظيفة:** مراكز التدريب المعتمدة
**مصدر التعريف:** `2026_04_09_131124_create_training_centers_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `city` | varchar(100) | NO | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `email` | varchar(255) | YES | - | - | - | - | - | - | - |
| `classification` | varchar(100) | YES | - | - | - | - | - | - | - |
| `supports_offline_training` | boolean | NO | true | - | - | - | - | - | - |
| `supports_online_training` | boolean | NO | false | - | - | - | - | - | - |
| `accreditation_start_date` | date | YES | - | - | - | - | - | - | - |
| `accreditation_end_date` | date | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `governorate` | varchar(100) | YES | - | - | - | - | - | - | - |
| `district` | varchar(100) | YES | - | - | - | - | - | - | - |
| `latitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `longitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `location_visibility` | enum | NO | - | - | - | - | - | public, internal, private | - |
| `license_number` | varchar(255) | YES | - | - | - | - | - | - | - |
| `license_issue_date` | date | YES | - | - | - | - | - | - | - |
| `license_expiry_date` | date | YES | - | - | - | - | - | - | - |
| `license_issued_by` | varchar(255) | YES | - | - | - | - | - | - | - |
| `license_image_path` | varchar(255) | YES | - | - | - | - | - | - | - |
| `supervisor_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |

**العلاقات:**
- → certificates (One-to-Many (inverse), قوية)
- → trainers (One-to-Many (inverse), قوية)
- → trainer_registration_requests (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- ← training_supervisors (One-to-Many (inverse), قوية)
- → training_center_platforms (One-to-Many, قوية)
- → training_courses (One-to-Many, قوية)
- → training_center_registration_requests (One-to-Many (inverse), قوية)
- → users (One-to-Many (inverse), قوية)

---

## جدول `training_course_trainee`

**الوظيفة:** جدول: training course trainee
**مصدر التعريف:** `2026_04_09_131657_create_training_course_trainee_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `training_course_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainee_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `score` | decimal(5,2) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_courses (One-to-Many, محتملة)
- ← trainees (One-to-Many, محتملة)

---

## جدول `training_courses`

**الوظيفة:** الدورات التدريبية
**مصدر التعريف:** `2026_04_09_131622_create_training_courses_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_kit_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_program_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `course_code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `delivery_mode` | enum | NO | offline | - | - | - | - | online, offline | - |
| `approved_platform` | varchar(150) | YES | - | - | - | - | - | - | - |
| `start_date` | date | YES | - | - | - | - | - | - | - |
| `end_date` | date | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `venue_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `governorate` | varchar(100) | YES | - | - | - | - | - | - | - |
| `city` | varchar(100) | YES | - | - | - | - | - | - | - |
| `district` | varchar(100) | YES | - | - | - | - | - | - | - |
| `address` | varchar(255) | YES | - | - | - | - | - | - | - |
| `latitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `longitude` | decimal(10,7) | YES | - | - | - | - | - | - | - |
| `location_visibility` | enum | NO | - | - | - | - | - | public, internal, private | - |
| `online_platform` | varchar(150) | YES | - | - | - | - | - | - | - |
| `online_url` | varchar(255) | YES | - | - | - | - | - | - | - |

**العلاقات:**
- → needs (One-to-Many, مؤكدة)
- → training_program_executions (One-to-Many, مؤكدة)
- → certificates (One-to-Many (inverse), قوية)
- → course_registration_requests (One-to-Many (inverse), قوية)
- → needs (One-to-Many (inverse), قوية)
- ← trainers (One-to-Many, قوية)
- ← training_centers (One-to-Many, قوية)
- ← training_kits (One-to-Many (inverse), قوية)
- ← training_programs (One-to-Many (inverse), قوية)
- ← governorates (One-to-Many (inverse), قوية)
- → certificates (One-to-Many, محتملة)
- → course_registration_requests (One-to-Many, محتملة)
- → training_course_trainee (One-to-Many, محتملة)

---

## جدول `training_kit_nominations`

**الوظيفة:** جدول: training kit nominations
**مصدر التعريف:** `2026_04_18_101403_create_training_kit_nominations_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_kit_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `proposed_name` | varchar(255) | YES | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `category` | varchar(255) | YES | - | - | - | - | - | - | - |
| `hours` | int | YES | - | - | - | - | - | - | - |
| `decision_notes` | text | YES | - | - | - | - | - | - | - |
| `decided_at` | timestamp | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← trainers (One-to-Many, قوية)
- ← training_kits (One-to-Many (inverse), قوية)

---

## جدول `training_kit_public_requests`

**الوظيفة:** جدول: training kit public requests
**مصدر التعريف:** `2026_06_17_140001_create_training_kit_public_requests_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `applicant_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `applicant_email` | varchar(255) | NO | - | - | - | - | - | - | - |
| `proposed_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `city` | varchar(255) | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `status` | enum | NO | pending | - | - | - | - | pending, reviewed, closed | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

---

## جدول `training_kits`

**الوظيفة:** حقائب التدريب (Training Kits)
**مصدر التعريف:** `2026_04_09_131402_create_training_kits_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `sector` | varchar(150) | YES | - | - | - | - | - | - | - |
| `category` | varchar(150) | YES | - | - | - | - | - | - | - |
| `type` | varchar(150) | YES | - | - | - | - | - | - | - |
| `material_code` | varchar(100) | YES | - | - | - | - | - | - | - |
| `level` | varchar(100) | YES | - | - | - | - | - | - | - |
| `objective` | text | YES | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |

**العلاقات:**
- → certificates (One-to-Many (inverse), قوية)
- → trainer_training_kit (Many-to-Many, قوية)
- → training_courses (One-to-Many (inverse), قوية)
- → training_kit_nominations (One-to-Many (inverse), قوية)
- → training_program_training_kit (Many-to-Many, قوية)

---

## جدول `training_program_approval_logs`

**الوظيفة:** جدول: training program approval logs
**مصدر التعريف:** `2026_06_24_100001_create_program_bank_supporting_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `program_id` | bigint unsigned | NO | - | - | training_programs.id | - | - | - | مفتاح خارجي |
| `from_status` | varchar(255) | YES | - | - | - | - | - | - | - |
| `to_status` | varchar(255) | NO | - | - | - | - | - | - | - |
| `action` | varchar(255) | NO | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `user_id` | bigint unsigned | YES | - | - | users.id | - | - | - | مفتاح خارجي |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_programs (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `training_program_executions`

**الوظيفة:** جدول: training program executions
**مصدر التعريف:** `2026_06_24_100001_create_program_bank_supporting_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `program_id` | bigint unsigned | NO | - | - | training_programs.id | - | - | - | مفتاح خارجي |
| `course_id` | bigint unsigned | NO | - | - | training_courses.id | - | - | - | مفتاح خارجي |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_programs (One-to-Many, مؤكدة)
- ← training_courses (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)

---

## جدول `training_program_modules`

**الوظيفة:** جدول: training program modules
**مصدر التعريف:** `2026_06_24_100001_create_program_bank_supporting_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `program_id` | bigint unsigned | NO | - | - | training_programs.id | - | - | - | مفتاح خارجي |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `objectives` | text | YES | - | - | - | - | - | - | - |
| `activities` | text | YES | - | - | - | - | - | - | - |
| `required_tools` | text | YES | - | - | - | - | - | - | - |
| `evaluation_method` | varchar(255) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_programs (One-to-Many, مؤكدة)

---

## جدول `training_program_outcomes`

**الوظيفة:** جدول: training program outcomes
**مصدر التعريف:** `2026_06_24_100001_create_program_bank_supporting_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `program_id` | bigint unsigned | NO | - | - | training_programs.id | - | - | - | مفتاح خارجي |
| `title` | varchar(255) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_programs (One-to-Many, مؤكدة)

---

## جدول `training_program_service_links`

**الوظيفة:** جدول: training program service links
**مصدر التعريف:** `2026_06_24_100001_create_program_bank_supporting_tables.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `program_id` | bigint unsigned | NO | - | - | training_programs.id | - | - | - | مفتاح خارجي |
| `service_reference_id` | bigint unsigned | YES | - | - | - | - | - | - | مفتاح خارجي |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_programs (One-to-Many, مؤكدة)

---

## جدول `training_program_training_kit`

**الوظيفة:** جدول: training program training kit
**مصدر التعريف:** `2026_04_09_131547_create_training_program_training_kit_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `training_program_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `training_kit_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `is_required` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← training_kits (Many-to-Many, قوية)
- ← training_programs (One-to-Many, محتملة)

---

## جدول `training_programs`

**الوظيفة:** البرامج التدريبية
**مصدر التعريف:** `2026_04_09_131516_create_training_programs_table.php`
**Soft Delete:** نعم (`deleted_at`)
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `description` | text | YES | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `deleted_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الحذف الناعم |
| `title` | varchar(255) | YES | - | - | - | - | - | - | - |
| `slug` | varchar(255) | YES | - | - | - | - | - | - | - |
| `sector` | varchar(255) | YES | - | - | - | - | - | - | - |
| `target_audience` | varchar(255) | YES | - | - | - | - | - | - | - |
| `level` | enum | NO | - | - | - | - | - | beginner, intermediate, advanced | - |
| `delivery_mode` | enum | NO | - | - | - | - | - | in_person, online, blended | - |
| `prerequisites` | text | YES | - | - | - | - | - | - | - |
| `outcomes_summary` | text | YES | - | - | - | - | - | - | - |
| `grants_certificate` | boolean | NO | false | - | - | - | - | - | - |
| `requires_final_exam` | boolean | NO | false | - | - | - | - | - | - |
| `requires_project` | boolean | NO | false | - | - | - | - | - | - |
| `created_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `approved_at` | timestamp | YES | - | - | - | - | - | - | - |
| `suspended_at` | timestamp | YES | - | - | - | - | - | - | - |
| `archived_at` | timestamp | YES | - | - | - | - | - | - | - |

**العلاقات:**
- → training_program_approval_logs (One-to-Many, مؤكدة)
- → training_program_executions (One-to-Many, مؤكدة)
- → training_program_modules (One-to-Many, مؤكدة)
- → training_program_outcomes (One-to-Many, مؤكدة)
- → training_program_service_links (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → certificates (One-to-Many (inverse), قوية)
- → training_courses (One-to-Many (inverse), قوية)
- → training_program_training_kit (One-to-Many, محتملة)

---

## جدول `training_supervisors`

**الوظيفة:** جدول: training supervisors
**مصدر التعريف:** `2026_07_11_180000_create_training_supervisors_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `code` | varchar(80) | NO | - | - | - | - | - | - | - |
| `type` | enum | NO | directorate | - | - | - | - | ministry, directorate, internal_entity | - |
| `parent_id` | bigint unsigned | YES | - | - | training_supervisors.id | - | - | - | مفتاح خارجي |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- → training_supervisors (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- → training_centers (One-to-Many (inverse), قوية)
- → users (One-to-Many, قوية)

---

## جدول `user_electronic_signatures`

**الوظيفة:** جدول: user electronic signatures
**مصدر التعريف:** `2026_06_23_100000_create_user_electronic_signatures_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `user_id` | bigint unsigned | NO | - | - | users.id | - | - | - | مفتاح خارجي |
| `signature_path` | varchar(255) | NO | - | - | - | - | - | - | - |
| `original_name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `mime_type` | varchar(100) | NO | - | - | - | - | - | - | - |
| `file_hash` | varchar(64) | NO | - | - | - | - | - | - | - |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `uploaded_by` | bigint unsigned | YES | - | - | users.id | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← users (One-to-Many, مؤكدة)
- ← users (One-to-Many, مؤكدة)
- → document_electronic_signatures (One-to-Many (inverse), قوية)

---

## جدول `users`

**الوظيفة:** مستخدمي النظام (موظفون، مدربون، متدربون، مراكز تدريب)
**مصدر التعريف:** `0001_01_01_000000_create_users_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `name` | varchar(255) | NO | - | - | - | - | - | - | - |
| `email` | varchar(255) | NO | - | - | - | - | - | - | - |
| `email_verified_at` | timestamp | YES | - | - | - | - | - | - | - |
| `password` | varchar(255) | NO | - | - | - | - | - | - | - |
| `entity_type` | varchar(255) | YES | - | - | - | - | - | - | - |
| `parent_user_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `is_active` | boolean | NO | true | - | - | - | - | - | - |
| `remember_token` | varchar(100) | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |
| `training_center_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainer_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `trainee_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `phone` | varchar(30) | YES | - | - | - | - | - | - | - |
| `last_login_at` | timestamp | YES | - | - | - | - | - | - | - |
| `governorate_id` | bigint unsigned | YES | - | - | governorates.id | - | - | - | مفتاح خارجي |
| `branch_id` | bigint unsigned | YES | - | - | branches.id | - | - | - | مفتاح خارجي |
| `consultant_office_id` | bigint unsigned | YES | - | - | consultant_offices.id | - | - | - | مفتاح خارجي |
| `funding_partner_id` | bigint unsigned | YES | - | - | funding_partners.id | - | - | - | مفتاح خارجي |
| `training_supervisor_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |

**العلاقات:**
- → agreements (One-to-Many, مؤكدة)
- → agreements (One-to-Many, مؤكدة)
- → audit_logs (One-to-Many, مؤكدة)
- → branches (One-to-Many, مؤكدة)
- → consultant_assignments (One-to-Many, مؤكدة)
- → consultant_offices (One-to-Many, مؤكدة)
- → consultant_offices (One-to-Many, مؤكدة)
- → consultant_offices (One-to-Many, مؤكدة)
- → consultant_reports (One-to-Many, مؤكدة)
- → consulting_contracts (One-to-Many, مؤكدة)
- → consulting_messages (One-to-Many, مؤكدة)
- → consulting_office_violations (One-to-Many, مؤكدة)
- → consulting_offices (One-to-Many, مؤكدة)
- → consulting_reports (One-to-Many, مؤكدة)
- → consulting_request_attachments (One-to-Many, مؤكدة)
- → consulting_requests (One-to-Many, مؤكدة)
- → consulting_requests (One-to-Many, مؤكدة)
- → consulting_reviews (One-to-Many, مؤكدة)
- → document_electronic_signatures (One-to-Many, مؤكدة)
- → entrepreneur_profiles (One-to-Many, مؤكدة)
- → entrepreneur_profiles (One-to-Many, مؤكدة)
- → executive_signer_profiles (One-to-Many, مؤكدة)
- → financial_records (One-to-Many, مؤكدة)
- → financial_records (One-to-Many, مؤكدة)
- → funding_applications (One-to-Many, مؤكدة)
- → funding_applications (One-to-Many, مؤكدة)
- → funding_applications (One-to-Many, مؤكدة)
- → funding_documents (One-to-Many, مؤكدة)
- → funding_partner_assignments (One-to-Many, مؤكدة)
- → funding_partners (One-to-Many, مؤكدة)
- → funding_partners (One-to-Many, مؤكدة)
- → funding_partners (One-to-Many, مؤكدة)
- → inbox_message_reads (One-to-Many, مؤكدة)
- → inbox_messages (One-to-Many, مؤكدة)
- → inbox_messages (One-to-Many, مؤكدة)
- → incubated_projects (One-to-Many, مؤكدة)
- → incubated_projects (One-to-Many, مؤكدة)
- → incubation_applications (One-to-Many, مؤكدة)
- → incubation_applications (One-to-Many, مؤكدة)
- → incubation_progress_reports (One-to-Many, مؤكدة)
- → incubators (One-to-Many, مؤكدة)
- → incubators (One-to-Many, مؤكدة)
- → job_applications (One-to-Many, مؤكدة)
- → job_postings (One-to-Many, مؤكدة)
- → mentoring_sessions (One-to-Many, مؤكدة)
- → need_action_logs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → needs (One-to-Many, مؤكدة)
- → news (One-to-Many, مؤكدة)
- → notifications (One-to-Many, مؤكدة)
- → staff_training_requests (One-to-Many, مؤكدة)
- → status_histories (One-to-Many, مؤكدة)
- → success_stories (One-to-Many, مؤكدة)
- → success_stories (One-to-Many, مؤكدة)
- → training_program_approval_logs (One-to-Many, مؤكدة)
- → training_program_executions (One-to-Many, مؤكدة)
- → training_programs (One-to-Many, مؤكدة)
- → training_programs (One-to-Many, مؤكدة)
- → user_electronic_signatures (One-to-Many, مؤكدة)
- → user_electronic_signatures (One-to-Many, مؤكدة)
- ← governorates (One-to-Many, مؤكدة)
- ← branches (One-to-Many, مؤكدة)
- ← consultant_offices (One-to-Many, مؤكدة)
- ← funding_partners (One-to-Many, مؤكدة)
- → branchs (One-to-Many (inverse), قوية)
- ← branchs (One-to-Many, قوية)
- → certificate_approvals (One-to-Many (inverse), قوية)
- → course_registration_requests (One-to-Many (inverse), قوية)
- → trainee_registration_requests (One-to-Many (inverse), قوية)
- → trainee_registration_requests (One-to-Many (inverse), قوية)
- → trainer_registration_requests (One-to-Many (inverse), قوية)
- → trainer_registration_requests (One-to-Many (inverse), قوية)
- → training_center_registration_requests (One-to-Many (inverse), قوية)
- → training_center_registration_requests (One-to-Many (inverse), قوية)
- ← training_supervisors (One-to-Many, قوية)
- ← training_centers (One-to-Many (inverse), قوية)
- ← trainers (One-to-Many (inverse), قوية)
- ← trainees (One-to-Many (inverse), قوية)
- → sessions (One-to-Many, محتملة)

---

## جدول `workforces`

**الوظيفة:** القوى العاملة/الكادر
**مصدر التعريف:** `2026_04_15_124127_create_workforces_table.php`
**Soft Delete:** لا
**Timestamps:** نعم

| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |
|-------|------|------|----------|----|----|--------|-------|------|-------|
| `id` | bigint unsigned | NO | - | ✓ | - | - | - | - | - |
| `trainee_id` | bigint unsigned | NO | - | - | - | - | - | - | مفتاح خارجي |
| `workforce_code` | varchar(50) | NO | - | - | - | - | - | - | - |
| `joined_at` | date | YES | - | - | - | - | - | - | - |
| `notes` | text | YES | - | - | - | - | - | - | - |
| `created_at` | timestamp | YES | - | - | - | - | - | - | تاريخ الإنشاء |
| `updated_at` | timestamp | YES | - | - | - | - | - | - | تاريخ آخر تحديث |

**العلاقات:**
- ← trainees (One-to-One, قوية)

---
