<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Recruiter;
use App\Support\MasterDataSpreadsheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Candidate::class);

        if ($request->ajax()) {
            $query = $this->candidateQuery($request)->latest();

            return DataTables::of($query)
                ->filterColumn('candidate_name', function ($query, $keyword) {
                    $query->where('candidate_name', 'LIKE', "%{$keyword}%");
                })
                ->addIndexColumn()
                ->addColumn('recruiter_name', fn ($row) => $row->recruiter->recruiter_name ?? '-')
                ->addColumn('client_name', fn ($row) => $row->client->client ?? '-')
                ->addColumn('job_role_name', fn ($row) => $row->jobRole->job_role ?? '-')
                ->addColumn('interview_level', fn ($row) => $row->interviewLevel->level ?? '-')
                ->editColumn('onboarding_date', fn($row) => $row->onboarding_date?->format('d-m-Y') ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('Y-m-d H:i:s') ?? '-')

                ->editColumn('take_home', fn ($row) => $row->take_home !== null ? number_format((float) $row->take_home, 2) : '-')
                ->editColumn('variable', fn ($row) => $row->variable !== null ? number_format((float) $row->variable, 2) : '-')
                ->editColumn('current_ctc', fn ($row) => $row->current_ctc !== null ? number_format((float) $row->current_ctc, 2) : '-')
                ->editColumn('expected_ctc', fn ($row) => $row->expected_ctc !== null ? number_format((float) $row->expected_ctc, 2) : '-')
                ->addColumn('cv_preview', function ($row) {
                    if ($row->upload_cv) {
                        return '<a href="'.asset($row->upload_cv).'" target="_blank" class="btn btn-sm btn-outline-primary">View CV</a>';
                    }
                    return '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Candidate::class)) {
                        $buttons .= '<a href="'.route('admin.candidates.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                        $buttons .= '<a href="'.route('admin.interview-schedules.create', ['candidate_id' => $row->id]).'" class="text-success fs-4 me-1" title="Schedule Interview"><i class="ri-calendar-schedule-line"></i></a>';
                    }
                    $buttons .= '<a href="'.route('admin.interview-schedules.show', $row->id).'" class="text-primary fs-4 me-1" title="Interview History"><i class="ri-history-line"></i></a>';
                    if (auth()->user()->can('delete', Candidate::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.candidates.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action', 'cv_preview'])
                ->make(true);
        }

        return view('backend.candidates.index', $this->formData());
    }

    public function create()
    {
        $this->authorize('create', Candidate::class);

        return view('backend.candidates.create', $this->formData());
    }

    public function store(Request $request)
    {
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '25M');
        $this->authorize('create', Candidate::class);

        $data = $this->validatedData($request);

        if ($request->hasFile('upload_cv')) {

            $file = $request->file('upload_cv');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;

            $file->move(public_path('uploads/candidates'), $filename);

            $data['upload_cv'] = 'uploads/candidates/' . $filename;
        }

        Candidate::create($data);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', 'Candidate created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Candidate::class);

        return view('backend.candidates.edit', array_merge(
            ['candidate' => Candidate::findOrFail($id)],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Candidate::class);

        $candidate = Candidate::findOrFail($id);

        $data = $this->validatedData($request, $candidate);

        if ($request->hasFile('upload_cv')) {

            if ($candidate->upload_cv && file_exists(public_path($candidate->upload_cv))) {
                unlink(public_path($candidate->upload_cv));
            }

            $file = $request->file('upload_cv');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;
            $file->move(public_path('uploads/candidates'), $filename);

            $data['upload_cv'] = 'uploads/candidates/' . $filename;
        }

        $candidate->update($data);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', 'Candidate updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Candidate::class);
        Candidate::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Candidate deleted successfully.']);
    }

    public function export(Request $request)
    {
        $this->authorize('read', Candidate::class);

        $rows = $this->candidateQuery($request)
            ->orderBy('id', 'asc')->get()->map(fn ($candidate,$index) => [
                $index + 1,
                $candidate->created_at ? date('d-m-Y', strtotime($candidate->created_at)) : '',
                $candidate->recruiter?->recruiter_name,
                $candidate->client?->client,
                $candidate->jobRole?->job_role,
                $candidate->candidate_name,
                $candidate->mobile_no,
                $candidate->email,
                $candidate->qualification,
                $candidate->total_experience,
                $candidate->relevant_experience,
                $candidate->take_home,
                $candidate->variable,
                $candidate->current_ctc,
                $candidate->expected_ctc,
                $candidate->notice_period,
                $candidate->current_company,
                $candidate->current_location,
                $candidate->preferred_location,
                $candidate->reason_for_change,
                $candidate->interviewLevel?->level,
                $candidate->status ? 'Active' : 'Inactive',
            ])->all();

        return Excel::download(new MasterDataExport($this->importHeadings(), $rows), 'candidates-'.now()->format('Y-m-d').'.xlsx');
    }

    public function importTemplate()
    {
        $this->authorize('create', Candidate::class);

        $dropdowns = [
            'Recruiter' => Recruiter::where('status', true)->orderBy('recruiter_name')->pluck('recruiter_name')->all(),
            'Client' => Client::where('status', true)->orderBy('client')->pluck('client')->all(),
            'Job Role' => JobRole::where('status', true)->orderBy('job_role')->pluck('job_role')->all(),
            'Level Of Interview' => InterviewLevel::where('status', true)->orderBy('sort_order')->pluck('level')->all(),
        ];

        return Excel::download(new MasterDataExport($this->importHeadings(), [[
            null, 'Existing Recruiter', 'Existing Client', 'Existing Job Role', 'Example Candidate',
            '9876543210', 'candidate@example.com', 'B.Tech', 5, 3, 60000, 5000, 900000,
            1100000, '30 days', 'Example Company', 'Chennai', 'Bengaluru', 'Career growth',
            'Screening', 'Active',
        ]], $dropdowns), 'candidates-import-template.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorize('create', Candidate::class);
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        try {
            $rows = MasterDataSpreadsheet::rows($request->file('import_file'));
        } catch (\Throwable) {
            return back()->with('error', 'The spreadsheet could not be read. Please use the provided template.');
        }

        if ($rows->isEmpty()) {
            return back()->with('error', 'The spreadsheet has no data rows.');
        }

        $validRows = [];
        $errors = [];
        $seenMobiles = [];
        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $number = $index + 2;
            if (empty(trim((string) ($row['candidate_name'] ?? '')))) {
                continue;
            }

            $recruiter = $row['recruiter'] ?? null;
            $client = $row['client'] ?? null;
            $jobRole = $row['job_role'] ?? null;
            $interviewLevel = $row['level_of_interview'] ?? null;

            $data = [
                'id' => $row['record_id'] ?? null,
                'recruiter_id' => MasterDataSpreadsheet::lookup(Recruiter::class, 'recruiter_name', $recruiter),
                'client_id' => MasterDataSpreadsheet::lookup(Client::class, 'client', $client),
                'job_role_id' => MasterDataSpreadsheet::lookup(JobRole::class, 'job_role', $jobRole),
                'candidate_name' => trim((string) ($row['candidate_name'] ?? '')),
                'mobile_no' => ($mobile = MasterDataSpreadsheet::text($row['mobile_no'] ?? null)) !== null
                    ? trim($mobile)
                    : null,
                'email' => ($email = MasterDataSpreadsheet::text($row['email'] ?? null)) !== null
                    ? strtolower(trim($email))
                    : null,
                'qualification' => MasterDataSpreadsheet::text($row['qualification'] ?? null),
                'total_experience' => $row['total_experience'] ?? null,
                'relevant_experience' => $row['relevant_experience'] ?? null,
                'take_home' => $row['take_home'] ?? null,
                'variable' => $row['variable'] ?? null,
                'current_ctc' => $row['current_ctc'] ?? null,
                'expected_ctc' => $row['expected_ctc'] ?? null,
                'notice_period' => MasterDataSpreadsheet::text($row['notice_period'] ?? null),
                'current_company' => MasterDataSpreadsheet::text($row['current_company'] ?? null),
                'current_location' => MasterDataSpreadsheet::text($row['current_location'] ?? null),
                'preferred_location' => MasterDataSpreadsheet::text($row['preferred_location'] ?? null),
                'reason_for_change' => MasterDataSpreadsheet::text($row['reason_for_change'] ?? null),
                'level_of_interview_id' => MasterDataSpreadsheet::lookup(InterviewLevel::class, 'level', $interviewLevel),
                'status' => MasterDataSpreadsheet::status($row['status'] ?? null),
            ];

            $validator = Validator::make($data, [
                'id' => 'nullable|integer|exists:candidates,id',
                'recruiter_id' => 'nullable|exists:recruiters,id',
                'client_id' => 'nullable|exists:clients,id',
                'job_role_id' => 'nullable|exists:job_roles,id',
                'candidate_name' => 'required|string|max:255',
                'mobile_no' => [
                    'nullable',
                    'string',
                    'max:30',
                    Rule::unique('candidates', 'mobile_no')->ignore((int) $data['id'])->whereNull('deleted_at'),
                ],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('candidates', 'email')->ignore((int) $data['id'])->whereNull('deleted_at'),
                ],
                'qualification' => 'nullable|string|max:255',
                'total_experience' => 'nullable|numeric|min:0',
                'relevant_experience' => 'nullable|numeric|min:0',
                'take_home' => 'nullable|numeric|min:0',
                'variable' => 'nullable|numeric|min:0',
                'current_ctc' => 'nullable|numeric|min:0',
                'expected_ctc' => 'nullable|numeric|min:0',
                'notice_period' => 'nullable|string|max:255',
                'current_company' => 'nullable|string|max:255',
                'current_location' => 'nullable|string|max:255',
                'preferred_location' => 'nullable|string|max:255',
                'reason_for_change' => 'nullable|string',
                'level_of_interview_id' => 'nullable|exists:level_of_interviews,id',
                'status' => 'required|in:0,1',
            ]);

            $validator->after(function ($validator) use (
                $recruiter,
                $client,
                $jobRole,
                $interviewLevel,
                $data,
                &$seenMobiles,
                &$seenEmails
            ) {
                $lookups = [
                    ['value' => $recruiter, 'id' => $data['recruiter_id'], 'label' => 'Recruiter'],
                    ['value' => $client, 'id' => $data['client_id'], 'label' => 'Client'],
                    ['value' => $jobRole, 'id' => $data['job_role_id'], 'label' => 'Job role'],
                    ['value' => $interviewLevel, 'id' => $data['level_of_interview_id'], 'label' => 'Level of interview'],
                ];

                foreach ($lookups as $lookup) {
                    if ($lookup['value'] !== null && trim((string) $lookup['value']) !== '' && ! $lookup['id']) {
                        $validator->errors()->add('lookup', $lookup['label'].' "'.$lookup['value'].'" was not found.');
                    }
                }

                if ($data['mobile_no'] && isset($seenMobiles[$data['mobile_no']])) {
                    $validator->errors()->add('mobile_no', 'The mobile number is duplicated in the import file.');
                }

                if ($data['email'] && isset($seenEmails[$data['email']])) {
                    $validator->errors()->add('email', 'The email address is duplicated in the import file.');
                }
            });

            if ($validator->fails()) {
                $errors[] = 'Row '.$number.': '.implode(', ', $validator->errors()->all());
            } else {
                $validRows[] = $data;

                if ($data['mobile_no']) {
                    $seenMobiles[$data['mobile_no']] = true;
                }

                if ($data['email']) {
                    $seenEmails[$data['email']] = true;
                }
            }
        }

        if ($errors) {
            return back()->with('error', 'Nothing was imported. '.MasterDataSpreadsheet::errors($errors));
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $data) {
                $id = $data['id'];
                unset($data['id']);
                $id ? Candidate::findOrFail($id)->update($data) : Candidate::create($data);
            }
        });

        return back()->with('success', count($validRows).' candidate row(s) imported successfully.');
    }

    private function formData(): array
    {
        return [
            'recruiters' => Recruiter::where('status', true)->orderBy('recruiter_name')->get(),
            'clients' => Client::where('status', true)->orderBy('client')->get(),
            'jobRoles' => JobRole::where('status', true)->orderBy('job_role')->get(),
            'interviewLevels' => InterviewLevel::where('status', true)->orderBy('sort_order')->get(),
        ];
    }

    private function candidateQuery(Request $request)
    {
        return Candidate::with(['recruiter', 'client', 'jobRole', 'interviewLevel'])
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->to_date))
            ->when($request->filled('recruiter_id'), fn ($query) => $query->where('recruiter_id', $request->recruiter_id))
            ->when($request->filled('job_role_id'), fn ($query) => $query->where('job_role_id', $request->job_role_id))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('level_of_interview_id'), function ($query) use ($request) {
                $levelIds = array_filter((array) $request->input('level_of_interview_id'));

                return $query->whereIn('level_of_interview_id', $levelIds);
            });
    }

    private function validatedData(Request $request, ?Candidate $candidate = null): array
    {
        $request->merge([
            'mobile_no' => $request->filled('mobile_no') ? trim((string) $request->mobile_no) : null,
            'email' => $request->filled('email') ? strtolower(trim((string) $request->email)) : null,
        ]);

        return $request->validate([
            'recruiter_id' => 'required|exists:recruiters,id',
            'client_id' => 'required|exists:clients,id',
            'job_role_id' => 'required|exists:job_roles,id',
            'candidate_name' => 'required|string|max:255',
            'mobile_no' => [
                'required',
                'string',
                'max:30',
                Rule::unique('candidates', 'mobile_no')->ignore($candidate?->id)->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('candidates', 'email')->ignore($candidate?->id)->whereNull('deleted_at'),
            ],
            'qualification' => 'nullable|string|max:255',
            'total_experience' => 'required|numeric|min:0',
            'relevant_experience' => 'required|numeric|min:0',
            'take_home' => 'nullable|numeric|min:0',
            'variable' => 'nullable|numeric|min:0',
            'current_ctc' => 'required|integer|min:0',
            'expected_ctc' => 'required|integer|min:0',
            'notice_period' => 'required|string|max:255',
            'current_company' => 'required|string|max:255',
            'current_location' => 'required|string|max:255',
            'preferred_location' => 'required|string|max:255',
            'reason_for_change' => 'nullable|string',
            'level_of_interview_id' => 'required|exists:level_of_interviews,id',
            'upload_cv' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'status' => 'required|in:0,1',
        ], [
            'mobile_no.unique' => 'This mobile number is already registered for another candidate.',
            'email.unique' => 'This email address is already registered for another candidate.',
            'current_ctc.integer' => 'Current CTC must be entered as a whole amount (e.g. 650000).',
            'expected_ctc.integer' => 'Expected CTC must be entered as a whole amount (e.g. 800000).',
        ]);
    }

    private function importHeadings(): array
    {
        return ['Record ID','Created Date', 'Recruiter', 'Client', 'Job Role', 'Candidate Name', 'Mobile No', 'Email', 'Qualification', 'Total Experience', 'Relevant Experience', 'Take Home', 'Variable', 'Current CTC', 'Expected CTC', 'Notice Period', 'Current Company', 'Current Location', 'Preferred Location', 'Reason For Change', 'Level Of Interview', 'Status',];
    }
}
