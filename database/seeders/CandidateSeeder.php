<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Mode;
use App\Models\Recruiter;
use App\Models\Revenue;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up previously seeded placeholder candidate emails if present
        Candidate::whereIn('email', [
            'varun.prasad@example.com',
            'suresh.subramanian@example.com',
            'ananya.deshmukh@example.com',
            'deepak.chawla@example.com',
            'meera.krishnan@example.com',
            'karthik.venkat@example.com',
            'pooja.hegde@example.com',
            'arvind.swamy@example.com',
            'divya.balaji@example.com',
        ])->forceDelete();

        // 1. Ensure master records exist
        $fullTimeMode = Mode::firstOrCreate(
            ['mode' => 'Full Time'],
            ['status' => true]
        );

        $contractMode = Mode::firstOrCreate(
            ['mode' => 'Contract'],
            ['status' => true]
        );

        $jobRoles = [
            'Software Engineer' => JobRole::firstOrCreate(['job_role' => 'Software Engineer'], ['status' => true]),
            'Senior Full Stack Developer' => JobRole::firstOrCreate(['job_role' => 'Senior Full Stack Developer'], ['status' => true]),
            'DevOps Engineer' => JobRole::firstOrCreate(['job_role' => 'DevOps Engineer'], ['status' => true]),
            'UI/UX Designer' => JobRole::firstOrCreate(['job_role' => 'UI/UX Designer'], ['status' => true]),
            'QA Automation Engineer' => JobRole::firstOrCreate(['job_role' => 'QA Automation Engineer'], ['status' => true]),
        ];

        $recruiters = [
            'Priya Sharma' => Recruiter::firstOrCreate(
                ['email' => 'priya.sharma@szorzo.com'],
                [
                    'recruiter_name' => 'Priya Sharma',
                    'location' => 'Bengaluru',
                    'mobile_number' => '9876543210',
                    'performance_rating' => 4.8,
                    'status' => true,
                ]
            ),
            'Rajesh Kumar' => Recruiter::firstOrCreate(
                ['email' => 'rajesh.kumar@szorzo.com'],
                [
                    'recruiter_name' => 'Rajesh Kumar',
                    'location' => 'Hyderabad',
                    'mobile_number' => '9876543211',
                    'performance_rating' => 4.5,
                    'status' => true,
                ]
            ),
        ];

        $clients = [
            'AFFLUENT' => Client::firstOrCreate(
                ['client' => 'AFFLUENT TECHNOLOGY LIMITED'],
                [
                    'email' => 'business@szorzo.com',
                    'poc_name' => 'Kannan PC',
                    'mobile_number' => '9901419393',
                    'status' => true,
                ]
            ),
            'EDS' => Client::firstOrCreate(
                ['client' => 'EDS TECHNOLOGIES PVT LTD'],
                [
                    'email' => 'business@szorzo.com',
                    'poc_name' => 'Finance Dept',
                    'mobile_number' => '9901419393',
                    'status' => true,
                ]
            ),
        ];

        // Ensure Client to Job Role mappings exist (required by candidate validator)
        ClientJobRole::firstOrCreate(
            ['client_id' => $clients['AFFLUENT']->id, 'job_role_id' => $jobRoles['Software Engineer']->id],
            ['status' => true]
        );
        ClientJobRole::firstOrCreate(
            ['client_id' => $clients['AFFLUENT']->id, 'job_role_id' => $jobRoles['DevOps Engineer']->id],
            ['status' => true]
        );
        ClientJobRole::firstOrCreate(
            ['client_id' => $clients['AFFLUENT']->id, 'job_role_id' => $jobRoles['QA Automation Engineer']->id],
            ['status' => true]
        );

        ClientJobRole::firstOrCreate(
            ['client_id' => $clients['EDS']->id, 'job_role_id' => $jobRoles['Senior Full Stack Developer']->id],
            ['status' => true]
        );
        ClientJobRole::firstOrCreate(
            ['client_id' => $clients['EDS']->id, 'job_role_id' => $jobRoles['UI/UX Designer']->id],
            ['status' => true]
        );

        $levelL1 = InterviewLevel::firstOrCreate(['level' => 'L1 Interview'], ['sort_order' => 1, 'status' => true]);
        $levelL2 = InterviewLevel::firstOrCreate(['level' => 'L2 Interview'], ['sort_order' => 2, 'status' => true]);
        $levelOfferAccepted = InterviewLevel::find(20) ?? InterviewLevel::firstOrCreate(
            ['level' => 'Offer Accepted / Onboarded'],
            ['sort_order' => 20, 'status' => true]
        );

        // 2. Candidates mapped to invoices (Candidate Name: 'BUSINESS @ SZORZO', no email IDs)
        $invoiceCandidates = [
            // Row 1: S.No 1 - AFFLUENT TECHNOLOGY LIMITED | Invoice SZ 01 2026-2027 | APRIL 2026
            [
                'candidate' => [
                    'created_at' => '2026-04-01 09:30:00',
                    'contract_from_date' => '2026-04-01',
                    'contract_to_date' => '2027-03-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.Tech / Professional',
                    'total_experience' => 5.00,
                    'relevant_experience' => 4.50,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => '30 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-04-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SZ 01 2026-2027',
                    'invoice_date' => '2026-04-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: APRIL 2026 | Collection Month: APRIL | Status: Pending',
                ],
            ],

            // Row 2: S.No 2 - EDS TECHNOLOGIES PVT LTD | Invoice SI 02 2026-2027 | APRIL 2026
            [
                'candidate' => [
                    'created_at' => '2026-04-03 10:00:00',
                    'contract_from_date' => '2026-04-03',
                    'contract_to_date' => '2027-03-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Rajesh Kumar']->id,
                    'client_id' => $clients['EDS']->id,
                    'job_role_id' => $jobRoles['Senior Full Stack Developer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'MCA / Professional',
                    'total_experience' => 7.00,
                    'relevant_experience' => 6.00,
                    'take_home' => 56000.00,
                    'variable' => 0.00,
                    'current_ctc' => 600000,
                    'expected_ctc' => 700000,
                    'onboarding_ctc' => 700000,
                    'notice_period' => 'Immediate',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-04-03',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 02 2026-2027',
                    'invoice_date' => '2026-04-03',
                    'universe_number' => '786',
                    'client_name' => 'EDS TECHNOLOGIES PVT LTD',
                    'client_address' => 'Bangalore, Karnataka, India',
                    'client_gst_number' => '29AABCE1234F1Z5',
                    'offered_ctc' => 700000.00,
                    'onboarding_ctc' => 700000.00,
                    'billing_percentage' => 8.00,
                    'service_amount' => 56000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 10080.00,
                    'total_amount' => 66080.00,
                    'notes' => 'Invoice Month: APRIL 2026 | Collection Month: APRIL | Status: Pending',
                ],
            ],

            // Row 3: S.No 5 - AFFLUENT TECHNOLOGY LIMITED | Invoice SZ 05 2026-2027 | MAY 2026
            [
                'candidate' => [
                    'created_at' => '2026-05-01 09:30:00',
                    'contract_from_date' => '2026-05-01',
                    'contract_to_date' => '2027-04-30',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.E. / Professional',
                    'total_experience' => 4.00,
                    'relevant_experience' => 3.50,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => '15 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-05-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SZ 05 2026-2027',
                    'invoice_date' => '2026-05-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: MAY 2026 | Collection Month: MAY | Status: Pending',
                ],
            ],

            // Row 4: S.No 6 - EDS TECHNOLOGIES PVT LTD | Invoice SI 06 2026-2027 | MAY 2026
            [
                'candidate' => [
                    'created_at' => '2026-05-05 10:00:00',
                    'contract_from_date' => '2026-05-05',
                    'contract_to_date' => '2027-04-30',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Rajesh Kumar']->id,
                    'client_id' => $clients['EDS']->id,
                    'job_role_id' => $jobRoles['Senior Full Stack Developer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.Tech Computer Science',
                    'total_experience' => 6.00,
                    'relevant_experience' => 5.00,
                    'take_home' => 43050.00,
                    'variable' => 0.00,
                    'current_ctc' => 400000,
                    'expected_ctc' => 500000,
                    'onboarding_ctc' => 430500,
                    'notice_period' => '30 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-05-05',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 06 2026-2027',
                    'invoice_date' => '2026-05-05',
                    'universe_number' => '786',
                    'client_name' => 'EDS TECHNOLOGIES PVT LTD',
                    'client_address' => 'Bangalore, Karnataka, India',
                    'client_gst_number' => '29AABCE1234F1Z5',
                    'offered_ctc' => 430500.00,
                    'onboarding_ctc' => 430500.00,
                    'billing_percentage' => 10.00,
                    'service_amount' => 43050.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 7749.00,
                    'total_amount' => 50799.00,
                    'notes' => 'Invoice Month: MAY 2026 | Collection Month: MAY | Status: Pending',
                ],
            ],

            // Row 5: S.No 8 - AFFLUENT TECHNOLOGY LIMITED | Invoice SI 08 2026-2027 | JUNE 2026
            [
                'candidate' => [
                    'created_at' => '2026-06-01 09:30:00',
                    'contract_from_date' => '2026-06-01',
                    'contract_to_date' => '2027-05-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.E. Computer Science',
                    'total_experience' => 3.50,
                    'relevant_experience' => 3.00,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => '30 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-06-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 08 2026-2027',
                    'invoice_date' => '2026-06-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: JUNE 2026 | Collection Month: JUNE | Status: Pending',
                ],
            ],

            // Row 6: S.No 12 - EDS TECHNOLOGIES PVT LTD | Invoice SI 12 2026-2027 | JUNE 2026 | Collected 05/06/2026
            [
                'candidate' => [
                    'created_at' => '2026-06-04 10:00:00',
                    'contract_from_date' => '2026-06-04',
                    'contract_to_date' => '2027-05-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Rajesh Kumar']->id,
                    'client_id' => $clients['EDS']->id,
                    'job_role_id' => $jobRoles['Senior Full Stack Developer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.Tech IT',
                    'total_experience' => 4.50,
                    'relevant_experience' => 4.00,
                    'take_home' => 26950.00,
                    'variable' => 0.00,
                    'current_ctc' => 240000,
                    'expected_ctc' => 270000,
                    'onboarding_ctc' => 269500,
                    'notice_period' => 'Immediate',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-06-04',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 12 2026-2027',
                    'invoice_date' => '2026-06-04',
                    'universe_number' => '786',
                    'client_name' => 'EDS TECHNOLOGIES PVT LTD',
                    'client_address' => 'Bangalore, Karnataka, India',
                    'client_gst_number' => '29AABCE1234F1Z5',
                    'offered_ctc' => 269500.00,
                    'onboarding_ctc' => 269500.00,
                    'billing_percentage' => 10.00,
                    'service_amount' => 26950.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 4851.00,
                    'total_amount' => 31801.00,
                    'notes' => 'Invoice Month: JUNE 2026 | Collection Month: JUNE | Collection Date: 2026-06-05 | Status: Collected',
                ],
            ],

            // Row 7: S.No 13 - AFFLUENT TECHNOLOGY LIMITED | Invoice SI 13 2026-2027 | JULY 2026 | Collected 02/07/2026
            [
                'candidate' => [
                    'created_at' => '2026-07-01 09:30:00',
                    'contract_from_date' => '2026-07-01',
                    'contract_to_date' => '2027-06-30',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.E. IT',
                    'total_experience' => 3.00,
                    'relevant_experience' => 2.50,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => 'Immediate',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-07-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 13 2026-2027',
                    'invoice_date' => '2026-07-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: JULY 2026 | Collection Month: JULY | Collection Date: 2026-07-02 | Status: Collected',
                ],
            ],

            // Row 8: S.No 17 - AFFLUENT TECHNOLOGY LIMITED | Invoice SI 17 2026-2027 | AUGUST 2026 | Collected 07/08/2026
            [
                'candidate' => [
                    'created_at' => '2026-08-01 09:30:00',
                    'contract_from_date' => '2026-08-01',
                    'contract_to_date' => '2027-07-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.Tech CS',
                    'total_experience' => 4.00,
                    'relevant_experience' => 3.50,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => '15 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-08-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 17 2026-2027',
                    'invoice_date' => '2026-08-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: AUGUST 2026 | Collection Month: AUGUST | Collection Date: 2026-08-07 | Status: Collected',
                ],
            ],

            // Row 9: S.No 24 - AFFLUENT TECHNOLOGY LIMITED | Invoice SI 24 2026-2027 | SEPTEMBER 2026
            [
                'candidate' => [
                    'created_at' => '2026-09-01 09:30:00',
                    'contract_from_date' => '2026-09-01',
                    'contract_to_date' => '2027-08-31',
                    'is_hourly' => false,
                    'hourly_salary' => null,
                    'candidate_name' => 'BUSINESS @ SZORZO',
                    'email' => null,
                    'mobile_no' => null,
                    'recruiter_id' => $recruiters['Priya Sharma']->id,
                    'client_id' => $clients['AFFLUENT']->id,
                    'job_role_id' => $jobRoles['Software Engineer']->id,
                    'mode_id' => $contractMode->id,
                    'qualification' => 'B.Tech',
                    'total_experience' => 3.50,
                    'relevant_experience' => 3.00,
                    'take_home' => 15000.00,
                    'variable' => 0.00,
                    'current_ctc' => 150000,
                    'expected_ctc' => 180000,
                    'onboarding_ctc' => 180000,
                    'notice_period' => '30 Days',
                    'current_company' => 'Szorzo Network',
                    'current_location' => 'Bengaluru',
                    'preferred_location' => 'Bengaluru',
                    'reason_for_change' => 'Business Engagement',
                    'level_of_interview_id' => $levelOfferAccepted->id,
                    'onboarding_date' => '2026-09-01',
                    'status' => true,
                ],
                'revenue' => [
                    'invoice_number' => 'SI 24 2026-2027',
                    'invoice_date' => '2026-09-01',
                    'universe_number' => '786',
                    'client_name' => 'AFFLUENT TECHNOLOGY LIMITED',
                    'client_address' => '12, Brigade IRV, Nallurhalli Road, Whitefield, Bangalore 560066',
                    'client_gst_number' => '33ABDCA5564C1Z1',
                    'offered_ctc' => 180000.00,
                    'onboarding_ctc' => 180000.00,
                    'billing_percentage' => 8.33,
                    'service_amount' => 15000.00,
                    'gst_percentage' => 18.00,
                    'gst_amount' => 2700.00,
                    'total_amount' => 17700.00,
                    'notes' => 'Invoice Month: SEPTEMBER 2026 | Collection Month: SEPTEMBER | Status: Pending',
                ],
            ],
        ];

        // Seed candidates and their exact invoice records by linking via invoice number
        foreach ($invoiceCandidates as $item) {
            $candData = $item['candidate'];
            $invoiceNumber = $item['revenue']['invoice_number'];

            // Find candidate previously linked to this invoice or create a fresh candidate record
            $existingRevenue = Revenue::where('invoice_number', $invoiceNumber)->first();
            if ($existingRevenue && $existingRevenue->candidate_id) {
                $candidate = Candidate::find($existingRevenue->candidate_id);
                if ($candidate) {
                    $candidate->update($candData);
                } else {
                    $candidate = Candidate::create($candData);
                }
            } else {
                $candidate = Candidate::create($candData);
            }

            // Explicitly persist created_at and contract dates without timestamp overwrite
            $candidate->timestamps = false;
            $candidate->created_at = $candData['created_at'];
            $candidate->updated_at = $candData['created_at'];
            $candidate->contract_from_date = $candData['contract_from_date'];
            $candidate->contract_to_date = $candData['contract_to_date'];
            $candidate->save();
            $candidate->timestamps = true;

            $revData = $item['revenue'];
            $revData['candidate_id'] = $candidate->id;
            $revData['client_id'] = $candidate->client_id;

            Revenue::updateOrCreate(
                ['invoice_number' => $invoiceNumber],
                $revData
            );
        }

        // 3. Additional candidates for Contract Reports and active pipelines
        $additionalCandidates = [
            // Hourly Contract Candidate (for Hourly Contract Reports)
            [
                'created_at' => '2026-04-01 10:00:00',
                'candidate_name' => 'Vikram Sengupta',
                'email' => 'vikram.sengupta@szorzo.com',
                'mobile_no' => '9845034567',
                'recruiter_id' => $recruiters['Rajesh Kumar']->id,
                'client_id' => $clients['AFFLUENT']->id,
                'job_role_id' => $jobRoles['DevOps Engineer']->id,
                'mode_id' => $contractMode->id,
                'qualification' => 'B.E. Information Technology',
                'total_experience' => 4.50,
                'relevant_experience' => 4.00,
                'current_company' => 'TCS',
                'current_location' => 'Hyderabad',
                'preferred_location' => 'Hyderabad',
                'notice_period' => '15 Days',
                'current_ctc' => 750000,
                'expected_ctc' => 900000,
                'onboarding_ctc' => 900000,
                'take_home' => 55000.00,
                'variable' => 0.00,
                'reason_for_change' => 'Contractual engagement',
                'level_of_interview_id' => $levelOfferAccepted->id,
                'onboarding_date' => '2026-04-01',
                'contract_from_date' => '2026-04-01',
                'contract_to_date' => '2027-03-31',
                'is_hourly' => true,
                'hourly_salary' => 750.00,
                'status' => true,
            ],

            // Active Pipeline Candidate (In L1 Stage)
            [
                'created_at' => '2026-05-10 11:00:00',
                'candidate_name' => 'Sneha Nair',
                'email' => 'sneha.nair@szorzo.com',
                'mobile_no' => '9845045678',
                'recruiter_id' => $recruiters['Priya Sharma']->id,
                'client_id' => $clients['EDS']->id,
                'job_role_id' => $jobRoles['UI/UX Designer']->id,
                'mode_id' => $fullTimeMode->id,
                'qualification' => 'B.Des',
                'total_experience' => 3.50,
                'relevant_experience' => 3.00,
                'current_company' => 'Freelance',
                'current_location' => 'Bengaluru',
                'preferred_location' => 'Bengaluru',
                'notice_period' => 'Immediate',
                'current_ctc' => 550000,
                'expected_ctc' => 750000,
                'onboarding_ctc' => null,
                'take_home' => 42000.00,
                'variable' => 0.00,
                'reason_for_change' => 'Looking for full-time role',
                'level_of_interview_id' => $levelL1->id,
                'onboarding_date' => null,
                'contract_from_date' => null,
                'contract_to_date' => null,
                'is_hourly' => false,
                'hourly_salary' => null,
                'status' => true,
            ],

            // Active Pipeline Candidate (In L2 Stage)
            [
                'created_at' => '2026-05-15 14:00:00',
                'candidate_name' => 'Manoj Varma',
                'email' => 'manoj.varma@szorzo.com',
                'mobile_no' => '9845056789',
                'recruiter_id' => $recruiters['Rajesh Kumar']->id,
                'client_id' => $clients['AFFLUENT']->id,
                'job_role_id' => $jobRoles['QA Automation Engineer']->id,
                'mode_id' => $fullTimeMode->id,
                'qualification' => 'B.Tech',
                'total_experience' => 4.00,
                'relevant_experience' => 3.50,
                'current_company' => 'Cognizant',
                'current_location' => 'Coimbatore',
                'preferred_location' => 'Chennai',
                'notice_period' => '60 Days',
                'current_ctc' => 600000,
                'expected_ctc' => 850000,
                'onboarding_ctc' => null,
                'take_home' => 48000.00,
                'variable' => 50000.00,
                'reason_for_change' => 'Relocation and hike',
                'level_of_interview_id' => $levelL2->id,
                'onboarding_date' => null,
                'contract_from_date' => null,
                'contract_to_date' => null,
                'is_hourly' => false,
                'hourly_salary' => null,
                'status' => true,
            ],
        ];

        foreach ($additionalCandidates as $candidateData) {
            $candidate = Candidate::updateOrCreate(
                ['email' => $candidateData['email']],
                $candidateData
            );

            $candidate->timestamps = false;
            $candidate->created_at = $candidateData['created_at'];
            $candidate->updated_at = $candidateData['created_at'];
            $candidate->contract_from_date = $candidateData['contract_from_date'];
            $candidate->contract_to_date = $candidateData['contract_to_date'];
            $candidate->save();
            $candidate->timestamps = true;
        }
    }
}
