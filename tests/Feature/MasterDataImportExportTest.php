<?php

namespace Tests\Feature;

use App\Exports\MasterDataExport;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Recruiter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MasterDataImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_be_imported_and_exported(): void
    {
        $this->actingAsSuperAdmin();
        Billing::create(['value' => 8.5, 'status' => 1]);

        $csv = implode("\n", [
            'Record ID,Client,Billing,Location,PoC Name,Signed Date,Renewal Date,Division,Contact Number,Email,Mobile Number,Status',
            ',Acme Ltd,8.5,,Jane Doe,2026-01-01,2026-12-31,,0441234567,jane@acme.test,9876543210,Active',
        ]);

        $this->post(route('admin.clients.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('clients.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'client' => 'Acme Ltd',
            'email' => 'jane@acme.test',
            'status' => 1,
        ]);

        Excel::fake();
        $this->get(route('admin.clients.export'))->assertOk();
        Excel::assertDownloaded('clients-'.now()->format('Y-m-d').'.xlsx', function (MasterDataExport $export) {
            return $export->headings()[1] === 'Client'
                && $export->collection()->first()[1] === 'Acme Ltd';
        });
    }

    public function test_client_requirements_can_be_imported_and_exported(): void
    {
        $this->actingAsSuperAdmin();
        Client::create(['client' => 'Acme Ltd', 'status' => 1]);
        Billing::create(['value' => 8.5, 'status' => 1]);

        $csv = implode("\n", [
            'Record ID,Client,Billing,Revenue Amount,Job Description,Mode,Requirement Open Date,Job Role,Number Of Position,Closure Target Date,CV Required,CV Uploaded,Project Owner,CTC,Location,Status',
            ',Acme Ltd,8.5,85000,,,2026-07-01,,2,2026-07-31,10,1,,1000000,,Active',
        ]);

        $this->post(route('admin.client-requirements.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('client-requirements.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('client_requirements', [
            'client_id' => Client::where('client', 'Acme Ltd')->value('id'),
            'revenue_amount' => 85000,
            'number_of_position' => 2,
            'status' => 1,
        ]);

        Excel::fake();
        $this->get(route('admin.client-requirements.export'))->assertOk();
        Excel::assertDownloaded('client-requirements-'.now()->format('Y-m-d').'.xlsx', function (MasterDataExport $export) {
            return $export->headings()[1] === 'Client'
                && $export->collection()->first()[1] === 'Acme Ltd';
        });
    }

    public function test_candidates_can_be_imported_and_exported_with_all_migration_fields(): void
    {
        $this->actingAsSuperAdmin();

        Recruiter::create(['recruiter_name' => 'Ravi Kumar', 'status' => 1]);
        Client::create(['client' => 'Acme Ltd', 'status' => 1]);
        JobRole::create(['job_role' => 'PHP Developer', 'status' => 1]);
        InterviewLevel::create(['level' => 'Screening', 'sort_order' => 1, 'status' => 1]);

        $csv = implode("\n", [
            'Record ID,Recruiter,Client,Job Role,Candidate Name,Mobile No,Email,Qualification,Total Experience,Relevant Experience,Take Home,Variable,Current CTC,Expected CTC,Notice Period,Current Company,Current Location,Preferred Location,Reason For Change,Level Of Interview,Status',
            ',Ravi Kumar,Acme Ltd,PHP Developer,Anita Rao,9876543210,anita@example.test,B.Tech,5.5,4,60000,5000,900000,1100000,30 days,Example Co,Chennai,Bengaluru,Career growth,Screening,Active',
        ]);

        $this->post(route('admin.candidates.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('candidates.csv', $csv),
        ])->assertRedirect();

        $candidate = Candidate::where('candidate_name', 'Anita Rao')->firstOrFail();
        $this->assertSame('anita@example.test', $candidate->email);
        $this->assertSame('5.50', $candidate->total_experience);
        $this->assertSame('1100000.00', $candidate->expected_ctc);
        $this->assertSame('Screening', $candidate->interviewLevel->level);

        Excel::fake();
        $this->get(route('admin.candidates.export'))->assertOk();
        Excel::assertDownloaded('candidates-'.now()->format('Y-m-d').'.xlsx', function (MasterDataExport $export) {
            return count($export->headings()) === 21
                && $export->headings()[4] === 'Candidate Name'
                && $export->collection()->first()[4] === 'Anita Rao';
        });
    }

    private function actingAsSuperAdmin(): void
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'access_level' => 'super_admin',
            'status' => 1,
        ]);

        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
    }
}
