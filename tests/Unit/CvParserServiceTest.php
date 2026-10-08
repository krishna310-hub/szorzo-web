<?php

namespace Tests\Unit;

use App\Services\CvParserService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class CvParserServiceTest extends TestCase
{
    protected CvParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CvParserService();
    }

    /**
     * Test 1: Extraction of candidate name and experience directly from Naukri filename conventions.
     */
    public function test_naukri_filename_extraction(): void
    {
        $file1 = 'Naukri_ManojKumarMK[16y_0m].docx';
        $res1 = $this->parser->extractFromFilename($file1);
        $this->assertSame('Manoj Kumar Mk', $res1['candidate_name']);
        $this->assertSame('Exp: 16y 0m', $res1['need']);

        $file2 = 'Naukri_MURUGAN[15y_0m].docx';
        $res2 = $this->parser->extractFromFilename($file2);
        $this->assertSame('Murugan', $res2['candidate_name']);
        $this->assertSame('Exp: 15y 0m', $res2['need']);

        $file3 = 'Naukri_TUdayakumar[7y_0m].docx';
        $res3 = $this->parser->extractFromFilename($file3);
        $this->assertSame('T Udayakumar', $res3['candidate_name']);
        $this->assertSame('Exp: 7y 0m', $res3['need']);

        $file4 = 'Naukri_ANMOLDUBEY[4y_0m].pdf';
        $res4 = $this->parser->extractFromFilename($file4);
        $this->assertSame('Anmoldubey', $res4['candidate_name']);
        $this->assertSame('Exp: 4y 0m', $res4['need']);
    }

    /**
     * Test 2: Heuristic extraction of name, email, mobile, and experience from resume text.
     */
    public function test_heuristic_extraction_with_naukri_sample(): void
    {
        $text = "
Naukri.com
Candidate Profile
Name : Manoj Kumar MK
Current Designation: Senior Software Engineer
Total Experience: 16 Year(s) 0 Month(s)
Annual Salary: Rs. 18.0 Lakh(s)
Notice Period: 15 Days
Mobile: +91 98765 43210
Email ID: manoj.kumar@gmail.com
help@naukri.com
        ";

        $data = $this->parser->extractHeuristically($text, 'Naukri_ManojKumarMK[16y_0m].docx');

        $this->assertSame('Manoj Kumar Mk', $data['candidate_name']);
        $this->assertSame('manoj.kumar@gmail.com', $data['email']);
        $this->assertSame('9876543210', $data['mobile_number']);
        $this->assertStringContainsString('16 Year(s) 0 Month(s)', $data['need']);
        $this->assertStringContainsString('15 Days', $data['need']);
    }

    /**
     * Test 3: Heuristic extraction for single-word candidate names (e.g. MURUGAN).
     */
    public function test_single_word_name_extraction(): void
    {
        $text = "
MURUGAN
Phone: +91-9840123456
Email: murugan.tech@yahoo.com
Database Administrator with 15 years experience
        ";

        $data = $this->parser->extractHeuristically($text, 'Naukri_MURUGAN[15y_0m].docx');

        $this->assertSame('Murugan', $data['candidate_name']);
        $this->assertSame('murugan.tech@yahoo.com', $data['email']);
        $this->assertSame('9840123456', $data['mobile_number']);
    }

    /**
     * Test 4: Job portal emails (naukri.com, etc.) are excluded, retaining candidate email.
     */
    public function test_email_filtering_ignores_portal_disclaimers(): void
    {
        $text = "
ANMOL DUBEY
Contact: 8877665544
feedback@naukri.com
support@monster.com
anmol.dubey@gmail.com
info@indeed.com
        ";

        $data = $this->parser->extractHeuristically($text, 'Naukri_ANMOLDUBEY[4y_0m].pdf');

        $this->assertSame('Anmol Dubey', $data['candidate_name']);
        $this->assertSame('anmol.dubey@gmail.com', $data['email']);
        $this->assertSame('8877665544', $data['mobile_number']);
    }

    /**
     * Test 5: Job role matching against company job roles.
     */
    public function test_job_role_matching(): void
    {
        $availableRoles = [
            1 => 'Java Developer',
            2 => 'Full Stack Developer',
            3 => 'Database Administrator',
            4 => 'React Developer',
        ];

        $text = "
T. UDAYAKUMAR
Mobile: 9789012345
Email: udayakumar.t@outlook.com
Senior Full Stack Developer with 7 years experience in web application development.
        ";

        $data = $this->parser->extractHeuristically($text, 'Naukri_TUdayakumar[7y_0m].docx', $availableRoles);

        $this->assertSame('T. Udayakumar', $data['candidate_name']);
        $this->assertSame('udayakumar.t@outlook.com', $data['email']);
        $this->assertSame('9789012345', $data['mobile_number']);
        $this->assertSame(2, $data['job_role_id']);
        $this->assertSame('Full Stack Developer', $data['job_role_name']);
    }

    /**
     * Test 6: Parsing a real in-memory DOCX file with tables and XML text.
     */
    public function test_docx_file_parsing(): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'docx_test_') . '.docx';
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE));

        $xmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                <w:p><w:r><w:t>Name: Manoj Kumar MK</w:t></w:r></w:p>
                <w:p><w:r><w:t>Email ID: manoj.mk@gmail.com</w:t></w:r></w:p>
                <w:p><w:r><w:t>Mobile: +91 9876543210</w:t></w:r></w:p>
                <w:p><w:r><w:t>Total Experience: 16 Years</w:t></w:r></w:p>
            </w:body>
        </w:document>';

        $zip->addFromString('word/document.xml', $xmlContent);
        $zip->close();

        $uploadedFile = new UploadedFile($tempPath, 'Naukri_ManojKumarMK[16y_0m].docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $result = $this->parser->parse($uploadedFile, [10 => 'Software Engineer']);

        $this->assertSame('Manoj Kumar Mk', $result['candidate_name']);
        $this->assertSame('manoj.mk@gmail.com', $result['email']);
        $this->assertSame('9876543210', $result['mobile_number']);
        $this->assertStringContainsString('16 Years', $result['need']);
        $this->assertArrayHasKey('ai_powered', $result);
        $this->assertArrayHasKey('job_role_id', $result);

        @unlink($tempPath);
    }

    /**
     * Test 7: Parsing a binary .doc format containing UTF-16LE text runs.
     */
    public function test_binary_doc_file_parsing(): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'doc_test_') . '.doc';

        $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\x00", 504);
        $utf16Text = mb_convert_encoding("Candidate Name: T Udayakumar\r\nEmail: udayakumar@outlook.com\r\nMobile: 9789012345\r\n", 'UTF-16LE', 'UTF-8');
        file_put_contents($tempPath, $oleHeader . $utf16Text);

        $uploadedFile = new UploadedFile($tempPath, 'Naukri_TUdayakumar[7y_0m].doc', 'application/msword', null, true);

        $result = $this->parser->parse($uploadedFile);

        $this->assertSame('T Udayakumar', $result['candidate_name']);
        $this->assertSame('udayakumar@outlook.com', $result['email']);
        $this->assertSame('9789012345', $result['mobile_number']);

        @unlink($tempPath);
    }
}

