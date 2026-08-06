# علاقات غير محسومة — SMEDC

| الجدول | العمود | جدول مُخمّن | السبب | الملفات المراجَعة |
|--------|-------|-----------|-------|-----------------|
| administrative_units | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/AdministrativeUnit.php:22 |
| agreements | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Agreement.php:109 |
| certificates | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Certificate.php:92 |
| consultant_offices | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/ConsultantOffice.php:79 |
| consulting_requests | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/ConsultingRequest.php:41 |
| course_registration_requests | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/CourseRegistrationRequest.php:52 |
| financial_records | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/FinancialRecord.php:30 |
| funding_applications | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/FundingApplication.php:34 |
| governorates | branches_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Governorate.php:24 |
| incubators | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Incubator.php:22 |
| needs | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Need.php:75 |
| news | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/News.php:40 |
| success_stories | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/SuccessStory.php:58 |
| trainees | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Trainee.php:40 |
| trainee_registration_requests | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TraineeRegistrationRequest.php:59 |
| trainers | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/Trainer.php:65 |
| trainer_registration_requests | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainerRegistrationRequest.php:66 |
| training_centers | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingCenter.php:67 |
| training_center_registration_requests | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingCenterRegistrationRequest.php:90 |
| training_courses | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingCourse.php:82 |
| training_programs | program_id | program_modules | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingProgram.php:96 |
| training_programs | program_id | program_outcomes | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingProgram.php:101 |
| training_programs | program_id | program_service_links | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingProgram.php:106 |
| training_programs | program_id | program_approval_logs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingProgram.php:111 |
| training_supervisors | parent_id | selfs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingSupervisor.php:27 |
| training_supervisors | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingSupervisor.php:52 |
| training_supervisors | parent_id | selfs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/TrainingSupervisor.php:32 |
| users | parent_user_id | selfs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/User.php:64 |
| users | branch_id | branchs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/User.php:99 |
| users | parent_user_id | selfs | Model يشير لجدول غير موجود في migrations | C:/Users/LENOVO/Desktop/back_authority/authority2/api2/app/Models/User.php:69 |
| course_registration_request_members | national_id | nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175950_create_course_registration_request_members_table.php |
| course_registration_requests | submitted_by_user_id | submitted_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175918_create_course_registration_requests_table.php |
| course_registration_requests | guardian_national_id | guardian_nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175918_create_course_registration_requests_table.php |
| funding_applications | national_id | nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_06_21_100000_create_funding_platform_tables.php |
| legacy_import_id_mappings | old_id | olds | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_06_17_120000_create_legacy_import_tracking_tables.php |
| needs | source_record_id | source_records | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_06_23_100000_create_needs_module_tables.php |
| trainee_registration_requests | national_id | nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175843_create_trainee_registration_requests_table.php |
| trainee_registration_requests | guardian_national_id | guardian_nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175843_create_trainee_registration_requests_table.php |
| trainee_registration_requests | submitted_by_user_id | submitted_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175843_create_trainee_registration_requests_table.php |
| trainee_registration_requests | reviewed_by_user_id | reviewed_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175843_create_trainee_registration_requests_table.php |
| trainee_registration_requests | approved_trainee_id | approved_trainees | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175843_create_trainee_registration_requests_table.php |
| trainees | national_id | nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_09_131329_create_trainees_table.php |
| trainer_registration_requests | national_id | nationals | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175813_create_trainer_registration_requests_table.php |
| trainer_registration_requests | submitted_by_user_id | submitted_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175813_create_trainer_registration_requests_table.php |
| trainer_registration_requests | reviewed_by_user_id | reviewed_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175813_create_trainer_registration_requests_table.php |
| trainer_registration_requests | approved_trainer_id | approved_trainers | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175813_create_trainer_registration_requests_table.php |
| training_center_registration_requests | submitted_by_user_id | submitted_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175352_create_training_center_registration_requests_table.php |
| training_center_registration_requests | reviewed_by_user_id | reviewed_by_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175352_create_training_center_registration_requests_table.php |
| training_center_registration_requests | approved_training_center_id | approved_training_centers | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_19_175352_create_training_center_registration_requests_table.php |
| training_centers | supervisor_id | supervisors | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_04_09_131124_create_training_centers_table.php |
| training_program_service_links | service_reference_id | service_references | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 2026_06_24_100001_create_program_bank_supporting_tables.php |
| users | parent_user_id | parent_users | عمود *_id بدون FK ولم يُؤكد جدول الهدف | 0001_01_01_000000_create_users_table.php |

## ما يلزم للتأكيد

1. مراجعة migrations alter التي تضيف أعمدة بشروط `Schema::hasColumn`
2. مقارنة مع قاعدة بيانات production فعلية (`SHOW CREATE TABLE`)
3. تتبع استعلامات `DB::raw` و `join` في Controllers/Services