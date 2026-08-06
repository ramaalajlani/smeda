<?php



namespace Tests\Feature;



use App\Models\Branch;

use App\Models\Certificate;

use App\Models\CourseRegistrationRequest;

use App\Models\Trainer;

use App\Models\TrainerRegistrationRequest;

use App\Models\TrainingCenter;

use App\Models\TrainingCourse;

use App\Models\User;

use App\Support\AccessControlGuard;

use Database\Seeders\DatabaseSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Laravel\Sanctum\Sanctum;

use Tests\TestCase;



class BranchIsolationTest extends TestCase

{

    use RefreshDatabase;



    private Branch $aleppo;

    private Branch $damascus;

    private TrainingCenter $center;



    protected function setUp(): void

    {

        parent::setUp();

        $this->seed(DatabaseSeeder::class);



        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();

        $this->damascus = Branch::query()->where('code', 'BR-DAMASCUS')->firstOrFail();

        $this->center = TrainingCenter::query()->firstOrFail();

    }



    public function test_general_director_sees_all_governorates(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());



        $this->getJson('/api/governorates')->assertOk()->assertJsonCount(14, 'data');

        $this->getJson('/api/branches')->assertOk()->assertJsonCount(14, 'data');

    }



    public function test_general_director_has_no_branch_restriction(): void

    {

        $general = User::query()->where('email', 'general@system.com')->firstOrFail();

        Sanctum::actingAs($general);



        $this->assertNull($general->branch_id);

        $this->assertNull($general->governorate_id);

        $this->assertTrue(AccessControlGuard::isNationalAdministrator($general));

    }



    public function test_branch_manager_sees_own_branch_only_in_api(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $this->getJson('/api/branches')->assertOk()->assertJsonCount(1, 'data');

    }



    public function test_branch_manager_aleppo_cannot_see_damascus_trainers(): void

    {

        $this->createTrainer('Damascus Trainer', 'TR-DAM-ISO', $this->damascus);

        $this->createTrainer('Aleppo Trainer', 'TR-ALP-ISO', $this->aleppo);



        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $names = collect($this->getJson('/api/trainers?per_page=100')->assertOk()->json('data'))->pluck('name');



        $this->assertTrue($names->contains('Aleppo Trainer'));

        $this->assertFalse($names->contains('Damascus Trainer'));

    }



    public function test_branch_manager_cannot_see_other_branch_courses(): void

    {

        $this->createCourse('Damascus Course', 'CRS-DAM-ISO', $this->damascus);

        $this->createCourse('Aleppo Course', 'CRS-ALP-ISO', $this->aleppo);



        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $titles = collect($this->getJson('/api/training-courses?per_page=100')->assertOk()->json('data'))->pluck('title');



        $this->assertTrue($titles->contains('Aleppo Course'));

        $this->assertFalse($titles->contains('Damascus Course'));

    }



    public function test_branch_manager_cannot_see_other_branch_certificates(): void

    {

        $damascusCourse = $this->createCourse('Damascus Cert Course', 'CRS-DAM-CERT', $this->damascus);

        $aleppoCourse = $this->createCourse('Aleppo Cert Course', 'CRS-ALP-CERT', $this->aleppo);



        $trainee = \App\Models\Trainee::query()->firstOrFail();
        $trainer = Trainer::query()->firstOrFail();

        Certificate::query()->create([
            'trainee_id' => $trainee->id,
            'training_course_id' => $damascusCourse->id,
            'training_center_id' => $this->center->id,
            'trainer_id' => $trainer->id,
            'training_kit_id' => $damascusCourse->training_kit_id,
            'certificate_number' => 'CERT-DAM-001',
            'certificate_code' => 'CD-DAM-001',
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 8,
            'training_hours' => 8,
            'status' => 'approved',
            'branch_id' => $this->damascus->id,
            'governorate_id' => $this->damascus->governorate_id,
        ]);

        Certificate::query()->create([
            'trainee_id' => $trainee->id,
            'training_course_id' => $aleppoCourse->id,
            'training_center_id' => $this->center->id,
            'trainer_id' => $trainer->id,
            'training_kit_id' => $aleppoCourse->training_kit_id,
            'certificate_number' => 'CERT-ALP-001',
            'certificate_code' => 'CD-ALP-001',
            'certificate_type' => 'attendance',
            'result' => 'passed',
            'hours_awarded' => 8,
            'training_hours' => 8,
            'status' => 'approved',
            'branch_id' => $this->aleppo->id,
            'governorate_id' => $this->aleppo->governorate_id,
        ]);



        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $codes = collect($this->getJson('/api/certificates?per_page=100')->assertOk()->json('data'))->pluck('certificate_code');



        $this->assertTrue($codes->contains('CD-ALP-001'));

        $this->assertFalse($codes->contains('CD-DAM-001'));

    }



    public function test_branch_manager_cannot_see_other_branch_registration_requests(): void

    {

        TrainerRegistrationRequest::query()->create([

            'request_number' => 'TRR-DAM-ISO',

            'training_center_id' => $this->center->id,

            'full_name' => 'Damascus Request Trainer',

            'submitted_by_user_id' => User::query()->where('email', 'branch.damascus@system.com')->value('id'),

            'status' => 'pending',

            'branch_id' => $this->damascus->id,

            'governorate_id' => $this->damascus->governorate_id,

        ]);



        TrainerRegistrationRequest::query()->create([

            'request_number' => 'TRR-ALP-ISO',

            'training_center_id' => $this->center->id,

            'full_name' => 'Aleppo Request Trainer',

            'submitted_by_user_id' => User::query()->where('email', 'branch.aleppo@system.com')->value('id'),

            'status' => 'pending',

            'branch_id' => $this->aleppo->id,

            'governorate_id' => $this->aleppo->governorate_id,

        ]);



        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $names = collect($this->getJson('/api/registration-requests/trainers?per_page=100')->assertOk()->json('data'))->pluck('full_name');



        $this->assertTrue($names->contains('Aleppo Request Trainer'));

        $this->assertFalse($names->contains('Damascus Request Trainer'));

    }



    public function test_branch_manager_cannot_access_other_branch_api(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $this->getJson('/api/branches/' . $this->damascus->id)->assertForbidden();

    }



    public function test_branch_manager_cannot_show_other_branch_trainer(): void

    {

        $trainer = $this->createTrainer('Damascus Only', 'TR-DAM-SHOW', $this->damascus);



        Sanctum::actingAs(User::query()->where('email', 'branch.aleppo@system.com')->firstOrFail());



        $this->getJson('/api/trainers/' . $trainer->id)->assertNotFound();

    }



    public function test_general_director_dashboard_has_national_stats(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());



        $this->getJson('/api/dashboard')->assertOk()

            ->assertJsonStructure([

                'governorates_count',

                'branches_count',

                'governorate_stats',

                'registration_requests_pending',

            ]);

    }



    public function test_branch_manager_dashboard_is_scoped(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'branch.damascus@system.com')->firstOrFail());



        $this->getJson('/api/dashboard')->assertOk()

            ->assertJsonStructure(['branch_id', 'branch_name', 'courses', 'trainers', 'registration_requests_pending']);

    }



    public function test_branch_dashboard_endpoint_auto_scopes_branch_manager(): void

    {

        $manager = User::query()->where('email', 'branch.damascus@system.com')->firstOrFail();

        Sanctum::actingAs($manager);



        $this->getJson('/api/branches/dashboard')->assertOk()

            ->assertJsonPath('scope', 'branch')

            ->assertJsonPath('data.branch.id', $manager->branch_id)

            ->assertJsonStructure(['data' => ['branch', 'kpis', 'incubators', 'pending_applications', 'active_projects']]);



        $otherBranch = Branch::query()->whereKeyNot($manager->branch_id)->firstOrFail();



        $this->getJson('/api/branches/dashboard?branch_id='.$otherBranch->id)->assertForbidden();

    }



    public function test_general_director_can_browse_and_filter_branch_dashboard(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'general@system.com')->firstOrFail());



        $this->getJson('/api/branches/dashboard')->assertOk()

            ->assertJsonPath('scope', 'national')

            ->assertJsonStructure(['branches' => [['id', 'name', 'governorate_name', 'metrics']]]);



        $branch = Branch::query()->firstOrFail();



        $this->getJson('/api/branches/dashboard?branch_id='.$branch->id)->assertOk()

            ->assertJsonPath('scope', 'branch')

            ->assertJsonPath('data.branch.id', $branch->id);

    }



    public function test_incubation_applications_respect_branch_filter_for_manager(): void

    {

        $manager = User::query()->where('email', 'branch.damascus@system.com')->firstOrFail();

        Sanctum::actingAs($manager);



        $response = $this->getJson('/api/incubation/applications?status=pending&per_page=100')->assertOk();

        $apps = $response->json('data') ?? [];



        foreach ($apps as $app) {

            $this->assertSame(

                $manager->branch_id,

                \App\Models\Incubator::query()->whereKey($app['incubator_id'] ?? 0)->value('branch_id')

            );

        }

    }



    public function test_deputy_general_director_has_national_read_not_full_admin(): void

    {

        $deputy = User::query()->where('email', 'deputy@system.com')->firstOrFail();

        Sanctum::actingAs($deputy);



        $this->assertFalse(AccessControlGuard::isNationalAdministrator($deputy));

        $this->getJson('/api/governorates')->assertOk();

        $this->getJson('/api/admin/users')->assertForbidden();

    }



    public function test_auditor_is_read_only_for_training_lists(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'auditor@system.com')->firstOrFail());



        $this->getJson('/api/trainers')->assertOk();

        $this->postJson('/api/admin/users', [

            'name' => 'Blocked',

            'email' => 'blocked@example.com',

            'password' => '12345678',

            'password_confirmation' => '12345678',

            'role' => 'auditor',

        ])->assertForbidden();

    }



    public function test_trainer_user_sees_only_own_courses(): void

    {

        $otherTrainer = $this->createTrainer('Other Branch Trainer', 'TR-OTHER-ISO', $this->damascus);

        $this->createCourse('Other Trainer Course', 'CRS-OTHER-ISO', $this->damascus)->update(['trainer_id' => $otherTrainer->id]);

        $trainerUser = User::query()->where('email', 'trainer@system.com')->firstOrFail();

        Sanctum::actingAs($trainerUser);

        $titles = collect($this->getJson('/api/training-courses?per_page=100')->assertOk()->json('data'))->pluck('title');

        $this->assertFalse($titles->contains('Other Trainer Course'));

    }



    public function test_trainee_user_has_limited_scope(): void

    {

        Sanctum::actingAs(User::query()->where('email', 'trainee@system.com')->firstOrFail());



        $this->getJson('/api/trainees')->assertForbidden();

        $this->getJson('/api/training-courses')->assertForbidden();

        $this->getJson('/api/dashboard')->assertOk()->assertJsonStructure(['certificates']);

    }



    private function createTrainer(string $name, string $code, Branch $branch): Trainer

    {

        return Trainer::query()->create([

            'name' => $name,

            'trainer_code' => $code,

            'training_center_id' => $this->center->id,

            'branch_id' => $branch->id,

            'governorate_id' => $branch->governorate_id,

            'status' => 'active',

        ]);

    }



    private function createCourse(string $title, string $code, Branch $branch): TrainingCourse

    {

        return TrainingCourse::query()->create([

            'title' => $title,

            'course_code' => $code,

            'training_center_id' => $this->center->id,

            'trainer_id' => Trainer::query()->firstOrFail()->id,

            'training_kit_id' => \App\Models\TrainingKit::query()->firstOrFail()->id,

            'delivery_mode' => 'offline',

            'start_date' => now()->toDateString(),

            'end_date' => now()->addMonth()->toDateString(),

            'status' => 'scheduled',

            'branch_id' => $branch->id,

            'governorate_id' => $branch->governorate_id,

        ]);

    }

}

