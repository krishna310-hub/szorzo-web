<?php

namespace Tests\Unit;

use App\Models\Employee;
use PHPUnit\Framework\TestCase;

class EmployeeChecklistTest extends TestCase
{
    public function test_checklist_items_contain_all_required_points(): void
    {
        $items = Employee::CHECKLIST_ITEMS;
        $this->assertCount(10, $items);

        $this->assertArrayHasKey('previous_company_offer_letters', $items);
        $this->assertEquals("Previous company's offer letter", $items['previous_company_offer_letters']['label']);
        $this->assertEquals(1, $items['previous_company_offer_letters']['number']);

        $this->assertArrayHasKey('relieving_letters', $items);
        $this->assertEquals('Relieving Letter', $items['relieving_letters']['label']);
        $this->assertEquals(2, $items['relieving_letters']['number']);

        $this->assertArrayHasKey('pay_slips', $items);
        $this->assertEquals('3 months’ pay slips', $items['pay_slips']['label']);
        $this->assertEquals(3, $items['pay_slips']['number']);

        $this->assertArrayHasKey('bank_statements', $items);
        $this->assertEquals('Bank Statements for the past 3 months', $items['bank_statements']['label']);
        $this->assertEquals(4, $items['bank_statements']['number']);

        $this->assertArrayHasKey('educational_certificates', $items);
        $this->assertEquals('All Educational certificates', $items['educational_certificates']['label']);
        $this->assertEquals(5, $items['educational_certificates']['number']);

        $this->assertArrayHasKey('pan_card', $items);
        $this->assertEquals('Pan Card copy', $items['pan_card']['label']);
        $this->assertEquals(6, $items['pan_card']['number']);

        $this->assertArrayHasKey('aadhaar_card', $items);
        $this->assertEquals('Adhaar Card copy', $items['aadhaar_card']['label']);
        $this->assertEquals(7, $items['aadhaar_card']['number']);

        $this->assertArrayHasKey('photograph', $items);
        $this->assertEquals('Passport size photograph', $items['photograph']['label']);
        $this->assertEquals(8, $items['photograph']['number']);

        $this->assertArrayHasKey('passbook_cheques', $items);
        $this->assertEquals('Passbook FrontPage / Cancelled cheque (Photocopy)', $items['passbook_cheques']['label']);
        $this->assertEquals(9, $items['passbook_cheques']['number']);

        $this->assertArrayHasKey('personal_details', $items);
        $this->assertEquals(10, $items['personal_details']['number']);
    }

    public function test_profile_completion_with_zero_items(): void
    {
        $employee = new Employee();
        $completion = $employee->profile_completion;

        $this->assertEquals(0, $completion['completed_count']);
        $this->assertEquals(10, $completion['total_count']);
        $this->assertEquals(0, $completion['percentage']);
        $this->assertEquals('0/10', $completion['ratio_text']);
        $this->assertFalse($completion['is_fully_completed']);
    }

    public function test_profile_completion_with_seven_verified_items(): void
    {
        $employee = new Employee([
            'employee_name' => 'John Doe',
            'mobile_number' => '9876543210',
            'document_checklist' => [
                'previous_company_offer_letters' => ['verified' => true],
                'relieving_letters' => ['verified' => true],
                'pay_slips' => ['verified' => true],
                'bank_statements' => ['verified' => true],
                'educational_certificates' => ['verified' => true],
                'pan_card' => ['verified' => true],
                'aadhaar_card' => ['verified' => true],
                'photograph' => ['verified' => false],
                'passbook_cheques' => ['verified' => false],
                'personal_details' => ['verified' => false],
            ],
        ]);

        $completion = $employee->profile_completion;

        $this->assertEquals(7, $completion['completed_count']);
        $this->assertEquals(10, $completion['total_count']);
        $this->assertEquals(70, $completion['percentage']);
        $this->assertEquals('7/10', $completion['ratio_text']);
        $this->assertEquals(7, $completion['verified_count']);
        $this->assertFalse($completion['is_fully_completed']);
        $this->assertEquals('#f7b84b', $employee->progress_color); // 70% is warning/amber
    }

    public function test_profile_completion_with_all_ten_verified_items(): void
    {
        $verifiedAll = [];
        foreach (array_keys(Employee::CHECKLIST_ITEMS) as $key) {
            $verifiedAll[$key] = ['verified' => true, 'verified_at' => '2026-09-11 12:00:00'];
        }

        $employee = new Employee([
            'employee_name' => 'Jane Smith',
            'document_checklist' => $verifiedAll,
        ]);

        $completion = $employee->profile_completion;

        $this->assertEquals(10, $completion['completed_count']);
        $this->assertEquals(10, $completion['total_count']);
        $this->assertEquals(100, $completion['percentage']);
        $this->assertEquals('10/10', $completion['ratio_text']);
        $this->assertTrue($completion['is_fully_completed']);
        $this->assertEquals('#0ab39c', $employee->progress_color); // 100% is success/teal
    }

    public function test_progress_color_thresholds(): void
    {
        // < 40% -> danger
        $empDanger = new Employee([
            'document_checklist' => [
                'pan_card' => ['verified' => true],
                'aadhaar_card' => ['verified' => true],
            ],
        ]);
        $this->assertEquals(20, $empDanger->profile_completion['percentage']);
        $this->assertEquals('#f06548', $empDanger->progress_color);

        // 40% - 79% -> warning
        $empWarning = new Employee([
            'document_checklist' => [
                'pan_card' => ['verified' => true],
                'aadhaar_card' => ['verified' => true],
                'pay_slips' => ['verified' => true],
                'bank_statements' => ['verified' => true],
            ],
        ]);
        $this->assertEquals(40, $empWarning->profile_completion['percentage']);
        $this->assertEquals('#f7b84b', $empWarning->progress_color);

        // >= 80% -> success
        $verifiedEight = [];
        $keys = array_keys(Employee::CHECKLIST_ITEMS);
        for ($i = 0; $i < 8; $i++) {
            $verifiedEight[$keys[$i]] = ['verified' => true];
        }
        $empSuccess = new Employee(['document_checklist' => $verifiedEight]);
        $this->assertEquals(80, $empSuccess->profile_completion['percentage']);
        $this->assertEquals('#0ab39c', $empSuccess->progress_color);
    }

    public function test_legacy_employee_uploaded_count_fallback(): void
    {
        // When document_checklist is null/empty, completed_count reflects uploaded documents
        $employee = new Employee([
            'employee_name' => 'Alice Johnson',
            'personal_mail' => 'alice@example.com',
            'employee_image' => 'photo.jpg',
            'pan_card_file' => 'pan.pdf',
            'aadhaar_file' => 'aadhaar.pdf',
            'tenth_marksheet' => '10th.pdf',
            'pay_slips' => ['slip1.pdf', 'slip2.pdf', 'slip3.pdf'],
            'bank_statements' => ['bank1.pdf'],
        ]);

        $completion = $employee->profile_completion;

        // 7 items uploaded: personal_details, photograph, pan_card, aadhaar_card, educational_certificates, pay_slips, bank_statements
        $this->assertEquals(7, $completion['completed_count']);
        $this->assertEquals(7, $completion['uploaded_count']);
        $this->assertEquals(0, $completion['verified_count']); // not yet verified
        $this->assertEquals('7/10', $completion['ratio_text']);
        $this->assertEquals(70, $completion['percentage']);
    }

    public function test_educational_certificates_detection(): void
    {
        // Only 10th marksheet
        $emp10th = new Employee(['tenth_marksheet' => '10th.pdf']);
        $this->assertTrue($emp10th->profile_completion['items']['educational_certificates']['is_uploaded']);

        // Only degree certificate
        $empDegree = new Employee(['degree_certificate' => 'degree.pdf']);
        $this->assertTrue($empDegree->profile_completion['items']['educational_certificates']['is_uploaded']);

        // Only additional educational certificates array
        $empEdu = new Employee(['educational_certificates' => ['diploma.pdf', 'pg.pdf']]);
        $this->assertTrue($empEdu->profile_completion['items']['educational_certificates']['is_uploaded']);
        $this->assertCount(2, $empEdu->profile_completion['items']['educational_certificates']['files']);
    }
}

