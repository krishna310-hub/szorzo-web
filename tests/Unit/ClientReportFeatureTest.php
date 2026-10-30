<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\ClientRequirementController;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\InterviewLevel;
use App\Models\InterviewSchedule;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ClientReportFeatureTest extends TestCase
{
    public function test_client_model_has_relationships(): void
    {
        $client = new Client();
        $this->assertTrue(method_exists($client, 'clientRequirements'));
        $this->assertTrue(method_exists($client, 'candidates'));
        $this->assertTrue(method_exists($client, 'revenues'));
    }

    public function test_client_requirement_controller_has_report_methods(): void
    {
        $controller = new ClientRequirementController();
        $this->assertTrue(method_exists($controller, 'clientsReportData'));
        $this->assertTrue(method_exists($controller, 'clientReport'));
        $this->assertTrue(method_exists($controller, 'clientReportData'));

        $ref = new ReflectionClass(ClientRequirementController::class);
        $this->assertTrue($ref->hasMethod('buildClientReportData'));
        $this->assertTrue($ref->hasMethod('getRejectedLevelIds'));
        $this->assertTrue($ref->hasMethod('classifyCandidateStatus'));
    }

    public function test_classify_candidate_status_onboarded(): void
    {
        $controller = new ClientRequirementController();
        $refMethod = new ReflectionMethod(ClientRequirementController::class, 'classifyCandidateStatus');
        $refMethod->setAccessible(true);

        // Candidate with level 20
        $candidate1 = new Candidate();
        $candidate1->level_of_interview_id = 20;
        $result1 = $refMethod->invoke($controller, $candidate1);
        $this->assertEquals('onboarded', $result1['type']);
        $this->assertEquals('Onboarded', $result1['stage']);

        // Candidate with onboarding date
        $candidate2 = new Candidate();
        $candidate2->setRawAttributes(['level_of_interview_id' => 10, 'onboarding_date' => '2026-08-01']);
        $result2 = $refMethod->invoke($controller, $candidate2);
        $this->assertEquals('onboarded', $result2['type']);
    }

    public function test_classify_candidate_status_rejections(): void
    {
        $controller = new ClientRequirementController();
        $refMethod = new ReflectionMethod(ClientRequirementController::class, 'classifyCandidateStatus');
        $refMethod->setAccessible(true);

        // Screen Reject
        $candidate = new Candidate();
        $level = new InterviewLevel();
        $level->level = 'Screen Reject';
        $candidate->setRelation('interviewLevel', $level);
        $result = $refMethod->invoke($controller, $candidate);
        $this->assertEquals('rejected', $result['type']);
        $this->assertEquals('Screening', $result['category']);

        // L1 Reject
        $candidate = new Candidate();
        $level = new InterviewLevel();
        $level->level = 'L1 Reject';
        $candidate->setRelation('interviewLevel', $level);
        $result = $refMethod->invoke($controller, $candidate);
        $this->assertEquals('rejected', $result['type']);
        $this->assertEquals('Technical Interview', $result['category']);

        // HR Reject
        $candidate = new Candidate();
        $level = new InterviewLevel();
        $level->level = 'HR Reject';
        $candidate->setRelation('interviewLevel', $level);
        $result = $refMethod->invoke($controller, $candidate);
        $this->assertEquals('rejected', $result['type']);
        $this->assertEquals('HR Round', $result['category']);

        // Joiner Declined
        $candidate = new Candidate();
        $candidate->level_of_interview_id = 21;
        $level = new InterviewLevel();
        $level->level = 'Joiner Declined';
        $candidate->setRelation('interviewLevel', $level);
        $result = $refMethod->invoke($controller, $candidate);
        $this->assertEquals('rejected', $result['type']);
        $this->assertEquals('Onboarding Drop', $result['category']);

        // Not Interested
        $candidate = new Candidate();
        $candidate->level_of_interview_id = 5;
        $level = new InterviewLevel();
        $level->level = 'Candidate Not Interested';
        $candidate->setRelation('interviewLevel', $level);
        $result = $refMethod->invoke($controller, $candidate);
        $this->assertEquals('rejected', $result['type']);
        $this->assertEquals('Candidate Dropped', $result['category']);
    }

    public function test_views_exist_and_contain_required_markup(): void
    {
        $indexPath = dirname(__DIR__, 2) . '/resources/views/backend/client-requirements/index.blade.php';
        $this->assertFileExists($indexPath);
        $indexContent = file_get_contents($indexPath);

        // Contains tab navigation
        $this->assertStringContainsString('id="tab-btn-requirements"', $indexContent);
        $this->assertStringContainsString('id="tab-btn-client-reports"', $indexContent);
        $this->assertStringContainsString('Client Reports', $indexContent);

        // Contains client reports table
        $this->assertStringContainsString('id="client-reports-table"', $indexContent);

        // Contains detailed report modal
        $this->assertStringContainsString('id="client-report-modal"', $indexContent);
        $this->assertStringContainsString('view-client-report', $indexContent);

        // Check partial view
        $partialPath = dirname(__DIR__, 2) . '/resources/views/backend/client-requirements/partials/client-report-content.blade.php';
        $this->assertFileExists($partialPath);
        $partialContent = file_get_contents($partialPath);

        $this->assertStringContainsString('Available Requirements', $partialContent);
        $this->assertStringContainsString('Onboarded Candidates', $partialContent);
        $this->assertStringContainsString('Rejected Details', $partialContent);
        $this->assertStringContainsString('Revenue & Invoices', $partialContent);
        $this->assertStringContainsString('Candidate Pipeline', $partialContent);

        // Check full page view
        $fullPagePath = dirname(__DIR__, 2) . '/resources/views/backend/client-requirements/client-report.blade.php';
        $this->assertFileExists($fullPagePath);
        $fullPageContent = file_get_contents($fullPagePath);
        $this->assertStringContainsString('backend.client-requirements.partials.client-report-content', $fullPageContent);
    }
}
