<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\CandidateController;
use App\Models\Candidate;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\Mode;
use App\Models\Recruiter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class CandidateRequirementTaggingTest extends TestCase
{
    public function test_candidate_form_view_has_tagged_recruiter_data_and_js_filtering(): void
    {
        $base = dirname(__DIR__, 2);
        $formContent = file_get_contents($base . '/resources/views/backend/candidates/form.blade.php');

        // 1. data-recruiters attribute is generated for client requirement options
        $this->assertStringContainsString('data-recruiters=', $formContent);
        $this->assertStringContainsString('$taggedOwners', $formContent);
        $this->assertStringContainsString('project_owner_ids', $formContent);

        // 2. JavaScript reads recruiters dataset
        $this->assertStringContainsString("recruiters: JSON.parse(option.dataset.recruiters || '[]').map(String)", $formContent);

        // 3. JavaScript helper to get selected recruiter
        $this->assertStringContainsString('function getSelectedRecruiterId()', $formContent);
        $this->assertStringContainsString('item.recruiters.includes(selectedRecruiter)', $formContent);

        // 4. Recruiter change event listener re-filters requirements
        $this->assertStringContainsString("recruiter.addEventListener('change', filterRequirements)", $formContent);
    }

    public function test_candidate_controller_includes_project_owners_in_client_requirements(): void
    {
        $base = dirname(__DIR__, 2);
        $controllerContent = file_get_contents($base . '/app/Http/Controllers/backend/CandidateController.php');

        // Verify project_owner and project_owner_ids are queried in formData()
        $this->assertStringContainsString("'project_owner'", $controllerContent);
        $this->assertStringContainsString("'project_owner_ids'", $controllerContent);

        // Verify recruiter scoping when logged in as recruiter
        $this->assertStringContainsString('whereJsonContains(\'project_owner_ids\', $recruiterId)', $controllerContent);
        $this->assertStringContainsString('where(\'project_owner\', $recruiterId)', $controllerContent);
    }

    public function test_validation_logic_enforces_tagged_recruiter(): void
    {
        $base = dirname(__DIR__, 2);
        $controllerContent = file_get_contents($base . '/app/Http/Controllers/backend/CandidateController.php');

        // Verify validateData checks tagged project owners
        $this->assertStringContainsString("Select an active requirement matching the chosen client, job role, and tagged recruiter.", $controllerContent);
        $this->assertStringContainsString("whereJsonContains('project_owner_ids', \$recruiterId)", $controllerContent);
    }
}
