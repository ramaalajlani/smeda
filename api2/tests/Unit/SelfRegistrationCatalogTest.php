<?php

namespace Tests\Unit;

use App\Support\SelfRegistrationCatalog;
use PHPUnit\Framework\TestCase;

class SelfRegistrationCatalogTest extends TestCase
{
    public function test_project_owner_maps_to_correct_role_and_entity(): void
    {
        $mapping = SelfRegistrationCatalog::resolveMapping('project_owner');

        $this->assertSame('project_owner', $mapping['role']);
        $this->assertSame('project_owner', $mapping['entity_type']);
    }

    public function test_entrepreneur_alias_normalizes_to_project_owner(): void
    {
        $this->assertSame('project_owner', SelfRegistrationCatalog::normalizeAccountType('entrepreneur'));

        $mapping = SelfRegistrationCatalog::resolveMapping('entrepreneur');
        $this->assertSame('project_owner', $mapping['role']);
    }

    public function test_training_types_map_to_training_roles(): void
    {
        $this->assertSame(['role' => 'trainee_user', 'entity_type' => 'trainee_user'], SelfRegistrationCatalog::resolveMapping('trainee'));
        $this->assertSame(['role' => 'trainer_user', 'entity_type' => 'trainer_user'], SelfRegistrationCatalog::resolveMapping('trainer'));
        $this->assertSame(['role' => 'center_user', 'entity_type' => 'center_user'], SelfRegistrationCatalog::resolveMapping('center'));
    }

    public function test_consultant_and_jobseeker_mappings(): void
    {
        $this->assertSame(['role' => 'consultant_office', 'entity_type' => 'consultant_office'], SelfRegistrationCatalog::resolveMapping('consultant'));
        $this->assertSame(['role' => 'trainee_user', 'entity_type' => 'job_seeker'], SelfRegistrationCatalog::resolveMapping('jobseeker'));
    }

    public function test_incubation_and_workforce_extended_mappings(): void
    {
        $this->assertSame('project_owner', SelfRegistrationCatalog::resolveMapping('incubation_applicant')['role']);
        $this->assertSame('project_owner', SelfRegistrationCatalog::resolveMapping('entrepreneur_tech')['role']);
        $this->assertSame('project_owner', SelfRegistrationCatalog::resolveMapping('employer')['role']);
        $this->assertSame('trainee_user', SelfRegistrationCatalog::resolveMapping('consulting_client')['role']);
        $this->assertSame('consulting_client', SelfRegistrationCatalog::resolveMapping('consulting_client')['entity_type']);
    }

    public function test_catalog_covers_all_project_module_groups(): void
    {
        $groups = SelfRegistrationCatalog::groups();

        $this->assertArrayHasKey('training', $groups);
        $this->assertArrayHasKey('finance', $groups);
        $this->assertArrayHasKey('incubation', $groups);
        $this->assertArrayHasKey('consulting', $groups);
        $this->assertArrayHasKey('workforce', $groups);
        $this->assertCount(10, SelfRegistrationCatalog::accountTypeKeys());
    }
}
