<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Location;
use App\Models\Mode;
use App\Models\Recruiter;
use App\Models\Revenue;
use App\Models\User;
use App\Support\MasterDataSpreadsheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ClientRequirementController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ClientRequirement::class);

        if ($request->ajax()) {
            $modeNames = Mode::pluck('mode', 'id');
            $locationNames = Location::pluck('location', 'id');
            $recruiterNames = Recruiter::pluck('recruiter_name', 'id');
            $query = ClientRequirement::visibleTo($request->user())
                ->with(['client', 'jobDescription', 'mode', 'jobRole', 'location', 'projectOwner', 'billing'])
                ->withCount('onboardedCandidates')
                ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
                ->when($request->filled('project_owner_id'), function ($query) use ($request) {
                    $id = (int) $request->project_owner_id;
                    $query->where(fn ($ownerQuery) => $ownerQuery->whereJsonContains('project_owner_ids', $id)
                        ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('project_owner_ids')->where('project_owner', $id)));
                })
                ->when($request->filled('priority'), fn ($query) => $query->where('is_priority', $request->boolean('priority')))
                ->when($request->filled('mode_id'), function ($query) use ($request) {
                    $id = (int) $request->mode_id;
                    $query->where(fn ($modeQuery) => $modeQuery->whereJsonContains('mode_ids', $id)
                        ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('mode_ids')->where('mode_id', $id)));
                })
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->boolean('status')))
                ->latest();

            $dataTable = DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    $search = trim((string) $request->input('search.value'));

                    if ($search !== '') {
                        $query->where(function ($q) use ($search) {

                            // Job Role
                            $q->orWhereHas('jobRole', function ($jobRoleQuery) use ($search) {
                                $jobRoleQuery->where('job_role', 'like', "%{$search}%");
                            });

                            // Client
                            $q->orWhereHas('client', function ($clientQuery) use ($search) {
                                $clientQuery->where('client', 'like', "%{$search}%");
                            });

                            // Job Description
                            $q->orWhereHas('jobDescription', function ($jobDescriptionQuery) use ($search) {
                                $jobDescriptionQuery->where('job_description', 'like', "%{$search}%");
                            });

                            // Billing
                            $q->orWhereHas('billing', function ($billingQuery) use ($search) {
                                $billingQuery->where('value', 'like', "%{$search}%");
                            });

                            // Priority
                            if (strtolower($search) === 'priority' || $search == '1') {
                                $q->orWhere('is_priority', 1);
                            } elseif (strtolower($search) === 'non priority' || $search == '0') {
                                $q->orWhere('is_priority', 0);
                            }
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('client_name', fn ($row) => $row->client->client ?? '-')
                ->addColumn('job_description_content', fn ($row) => $row->jobDescription?->job_description)
                ->addColumn('job_description_action', fn ($row) => filled($row->jobDescription?->job_description)
                    ? '<button type="button" class="btn btn-link text-primary fs-4 p-0 view-job-description" data-id="'.$row->id.'" data-title="'.e(($row->jobRole?->job_role ?? 'Job Description').($row->client?->client ? ' - '.$row->client->client : '')).'" title="View Job Description" aria-label="View Job Description"><i class="ri-eye-line"></i></button>'
                    : '-')
                ->addColumn('psoition_level', fn ($row) => $row->position_level ?? '-')
                ->addColumn('mode_name', fn ($row) => collect($row->mode_ids ?: array_filter([$row->mode_id]))
                    ->map(fn ($id) => $modeNames->get((int) $id))->filter()->join(', ') ?: '-')
                ->addColumn('job_role_name', fn ($row) => $row->jobRole->job_role ?? '-')
                ->addColumn('location_name', fn ($row) => collect($row->location_ids ?: array_filter([$row->location_id]))
                    ->map(fn ($id) => $locationNames->get((int) $id))->filter()->join(', ') ?: '-')
                ->addColumn('project_owner_name', fn ($row) => collect($row->project_owner_ids ?: array_filter([$row->project_owner]))
                    ->map(fn ($id) => $recruiterNames->get((int) $id))->filter()->join(', ') ?: '-')
                ->addColumn('billing_value', fn ($row) => $row->billing ? $row->billing->value.'%' : '-');

            if ($request->user()->isSuperAdmin()) {
                $dataTable->editColumn('revenue_amount', fn ($row) => $row->revenue_amount !== null
                    ? '₹'.number_format((float) $row->revenue_amount, 2)
                    : '-');
            }

            return $dataTable->editColumn('requirement_open_date', fn ($row) => $row->requirement_open_date?->format('d-m-Y') ?? '-')
                ->addColumn('onboarded_count', function ($row) {
                    $onboarded = (int) ($row->onboarded_candidates_count ?? 0);
                    $positions = (int) ($row->number_of_position ?? 0);
                    $badgeClass = ($positions > 0 && $onboarded >= $positions)
                        ? 'bg-success text-white'
                        : 'bg-info-subtle text-info';

                    return '<span class="badge '.$badgeClass.' fw-semibold">'.$onboarded.' / '.$positions.'</span>';
                })
                ->editColumn('cv_uploaded', function ($row) {
                    $uploaded = (int) ($row->cv_uploaded ?? 0);
                    $required = (int) ($row->cv_required ?? 0);

                    if ($required > 0 && $uploaded > $required) {
                        $extra = $uploaded - $required;
                        return '<span class="badge bg-danger text-white fw-bold">'.$uploaded.'</span> <span class="badge bg-danger-subtle text-danger ms-1">+'.$extra.' extra</span>';
                    } elseif ($required > 0 && $uploaded === $required) {
                        return '<span class="badge bg-success-subtle text-success fw-bold">'.$uploaded.'</span>';
                    }

                    return '<span>'.$uploaded.'</span>';
                })
                ->editColumn('closure_target_date', fn ($row) => $row->closure_target_date?->format('d-m-Y') ?? '-')
                ->editColumn('ctc', fn ($row) => $row->ctc !== null ? number_format((float) $row->ctc, 2) : '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('priority', fn ($row) => $row->is_priority
                    ? '<span class="badge bg-warning-subtle text-warning">Priority</span>'
                    : '-')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', ClientRequirement::class)) {
                        $buttons .= '<a href="'.route('admin.client-requirements.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', ClientRequirement::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.client-requirements.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['job_description_content', 'job_description_action', 'cv_uploaded', 'onboarded_count', 'status', 'priority', 'action'])
                ->make(true);
        }

        return view('backend.client-requirements.index', [
            'clients' => Client::where('status', true)->orderBy('client')->get(),
            'recruiters' => Recruiter::where('status', true)->orderBy('recruiter_name')->get(),
            'modes' => Mode::where('status', true)->orderBy('mode')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', ClientRequirement::class);

        return view('backend.client-requirements.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', ClientRequirement::class);
        ClientRequirement::create($this->validatedData($request));

        return redirect()->route('admin.client-requirements.index')->with('success', 'Client requirement created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', ClientRequirement::class);

        return view('backend.client-requirements.edit', array_merge(
            ['clientRequirement' => ClientRequirement::findOrFail($id)],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', ClientRequirement::class);
        ClientRequirement::findOrFail($id)->update($this->validatedData($request));

        return redirect()->route('admin.client-requirements.index')->with('success', 'Client requirement updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', ClientRequirement::class);
        ClientRequirement::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Client requirement deleted successfully.']);
    }

    public function export(Request $request)
    {
        $this->authorize('read', ClientRequirement::class);
        $modeNames = Mode::pluck('mode', 'id');
        $locationNames = Location::pluck('location', 'id');
        $recruiterNames = Recruiter::pluck('recruiter_name', 'id');

        $rows = ClientRequirement::visibleTo($request->user())
            ->with(['client', 'billing', 'jobDescription', 'mode', 'jobRole', 'projectOwner', 'location'])
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('project_owner_id'), function ($query) use ($request) {
                $id = (int) $request->project_owner_id;
                $query->where(fn ($ownerQuery) => $ownerQuery->whereJsonContains('project_owner_ids', $id)
                    ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('project_owner_ids')->where('project_owner', $id)));
            })
            ->when($request->filled('priority'), fn ($query) => $query->where('is_priority', $request->boolean('priority')))
            ->when($request->filled('mode_id'), function ($query) use ($request) {
                $id = (int) $request->mode_id;
                $query->where(fn ($modeQuery) => $modeQuery->whereJsonContains('mode_ids', $id)
                    ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('mode_ids')->where('mode_id', $id)));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->boolean('status')))
            ->orderBy('id')->get()->map(fn ($item) => [
                $item->id,
                $item->client?->client,
                $item->billing?->value,
                $item->revenue_amount,
                $item->jobDescription?->job_description,
                collect($item->mode_ids ?: array_filter([$item->mode_id]))
                    ->map(fn ($id) => $modeNames->get((int) $id))->filter()->join(', '),
                $item->requirement_open_date?->format('Y-m-d'),
                $item->jobRole?->job_role,
                $item->number_of_position,
                $item->closure_target_date?->format('Y-m-d'),
                $item->cv_required,
                $item->cv_uploaded,
                collect($item->project_owner_ids ?: array_filter([$item->project_owner]))
                    ->map(fn ($id) => $recruiterNames->get((int) $id))->filter()->join(', '),
                $item->is_priority ? 'Yes' : 'No',
                $item->ctc,
                collect($item->location_ids ?: array_filter([$item->location_id]))
                    ->map(fn ($id) => $locationNames->get((int) $id))->filter()->join(', '),
                $item->status ? 'Active' : 'Inactive',
            ])->all();

        return Excel::download(new MasterDataExport($this->importHeadings(), $rows), 'client-requirements-'.now()->format('Y-m-d').'.xlsx');
    }

    public function importTemplate()
    {
        $this->authorize('create', ClientRequirement::class);

        return Excel::download(new MasterDataExport($this->importHeadings(), [[
            null, 'Existing Client Name', '8.5', null, null, 'C2H, Contract', '2026-07-01', 'Existing Job Role',
            2, '2026-07-31', 10, 0, 'Existing Recruiter', 'No', 500000, 'Chennai, Bangalore', 'Active',
        ]]), 'client-requirements-import-template.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorize('create', ClientRequirement::class);
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

        foreach ($rows as $index => $row) {
            $number = $index + 2;
            $clientName = $row['client'] ?? null;
            $billingValue = MasterDataSpreadsheet::cleanPercent($row['billing'] ?? null);
            $jobDescription = $row['job_description'] ?? null;
            $mode = $row['modes'] ?? $row['mode'] ?? null;
            $jobRole = $row['job_role'] ?? null;
            $projectOwner = $row['project_owner'] ?? null;
            $location = $row['locations'] ?? $row['location'] ?? null;
            [$modeIds, $missingModes] = $this->masterIdsFromList(Mode::class, 'mode', $mode);
            [$locationIds, $missingLocations] = $this->masterIdsFromList(Location::class, 'location', $location);
            [$projectOwnerIds, $missingProjectOwners] = $this->masterIdsFromList(Recruiter::class, 'recruiter_name', $projectOwner);
            $clientId = MasterDataSpreadsheet::lookup(Client::class, 'client', $clientName);
            $billingId = MasterDataSpreadsheet::lookupNumeric(Billing::class, 'value', $billingValue);
            $jobDescriptionId = null;

            if ($jobDescription !== null && trim((string) $jobDescription) !== '' && $clientId) {
                $jobDescriptionId = ClientJobRole::where('client_id', $clientId)
                    ->whereRaw('LOWER(job_description) = ?', [mb_strtolower(trim((string) $jobDescription))])
                    ->value('id');
            }

            $ctc = $row['ctc'] ?? null;
            $revenue = $row['revenue_amount'] ?? null;
            if (($revenue === null || $revenue === '') && is_numeric($billingValue) && is_numeric($ctc)) {
                $revenue = ((float) $billingValue * (float) $ctc) / 100;
            }

            $data = [
                'id' => $row['record_id'] ?? null,
                'client_id' => $clientId,
                'billing_id' => $billingId,
                'revenue_amount' => $revenue,
                'job_description_id' => $jobDescriptionId,
                'mode_id' => $modeIds[0] ?? null,
                'mode_ids' => $modeIds,
                'requirement_open_date' => MasterDataSpreadsheet::date($row['requirement_open_date'] ?? null),
                'job_role_id' => MasterDataSpreadsheet::lookup(JobRole::class, 'job_role', $jobRole),
                'number_of_position' => $row['number_of_position'] ?? 0,
                'closure_target_date' => MasterDataSpreadsheet::date($row['closure_target_date'] ?? null),
                'cv_required' => $row['cv_required'] ?? 0,
                'cv_uploaded' => $row['cv_uploaded'] ?? 0,
                'project_owner' => $projectOwnerIds[0] ?? null,
                'project_owner_ids' => $projectOwnerIds,
                'is_priority' => $this->spreadsheetBoolean($row['priority'] ?? null),
                'ctc' => $ctc,
                'location_id' => $locationIds[0] ?? null,
                'location_ids' => $locationIds,
                'status' => MasterDataSpreadsheet::status($row['status'] ?? null),
            ];

            $validator = Validator::make($data, [
                'id' => 'nullable|integer|exists:client_requirements,id',
                'client_id' => 'required|exists:clients,id',
                'billing_id' => 'nullable|exists:billings,id',
                'revenue_amount' => 'nullable|numeric|min:0',
                'job_description_id' => 'nullable|exists:client_job_roles,id',
                'mode_id' => 'nullable|exists:modes,id',
                'mode_ids' => 'nullable|array',
                'mode_ids.*' => 'integer|distinct|exists:modes,id',
                'requirement_open_date' => 'nullable|date',
                'job_role_id' => 'nullable|exists:job_roles,id',
                'number_of_position' => 'nullable|integer|min:0',
                'closure_target_date' => 'nullable|date|after_or_equal:requirement_open_date',
                'cv_required' => 'nullable|integer|min:0',
                'cv_uploaded' => 'nullable|integer|min:0',
                'project_owner' => 'nullable|exists:recruiters,id',
                'project_owner_ids' => 'nullable|array',
                'project_owner_ids.*' => 'integer|distinct|exists:recruiters,id',
                'is_priority' => 'required|boolean',
                'ctc' => 'nullable|numeric|min:0',
                'location_id' => 'nullable|exists:locations,id',
                'location_ids' => 'nullable|array',
                'location_ids.*' => 'integer|distinct|exists:locations,id',
                'status' => 'required|in:0,1',
            ], [
                'client_id.required' => 'Client "'.$clientName.'" was not found.',
            ]);

            $validator->after(function ($validator) use ($billingValue, $jobDescription, $jobRole, $data, $missingModes, $missingLocations, $missingProjectOwners) {
                $lookups = [
                    ['value' => $billingValue, 'id' => $data['billing_id'], 'label' => 'Billing'],
                    ['value' => $jobDescription, 'id' => $data['job_description_id'], 'label' => 'Job description'],
                    ['value' => $jobRole, 'id' => $data['job_role_id'], 'label' => 'Job role'],
                ];
                foreach ($lookups as $lookup) {
                    if ($lookup['value'] !== null && trim((string) $lookup['value']) !== '' && ! $lookup['id']) {
                        $validator->errors()->add('lookup', $lookup['label'].' "'.$lookup['value'].'" was not found.');
                    }
                }
                foreach ($missingModes as $name) {
                    $validator->errors()->add('mode_ids', 'Mode "'.$name.'" was not found.');
                }
                foreach ($missingLocations as $name) {
                    $validator->errors()->add('location_ids', 'Location "'.$name.'" was not found.');
                }
                foreach ($missingProjectOwners as $name) {
                    $validator->errors()->add('project_owner_ids', 'Project owner "'.$name.'" was not found.');
                }
            });

            if ($validator->fails()) {
                $errors[] = 'Row '.$number.': '.implode(', ', $validator->errors()->all());
            } else {
                $validRows[] = $data;
            }
        }

        if ($errors) {
            return back()->with('error', 'Nothing was imported. '.MasterDataSpreadsheet::errors($errors));
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $data) {
                $id = $data['id'];
                unset($data['id']);
                $id ? ClientRequirement::findOrFail($id)->update($data) : ClientRequirement::create($data);
            }
        });

        return back()->with('success', count($validRows).' client requirement row(s) imported successfully.');
    }

    private function formData(): array
    {
        return [
            'clients' => Client::where('status', true)->orderBy('client')->get(),
            'billings' => Billing::where('status', true)->orderBy('value')->get(),
            'modes' => Mode::where('status', true)->orderBy('mode')->get(),
            'jobRoles' => JobRole::where('status', true)->orderBy('job_role')->get(),
            'clientJobRoleMap' => ClientJobRole::where('status', true)
                ->get(['id', 'client_id', 'job_role_id', 'job_description'])
                ->groupBy('client_id')
                ->map(fn ($rows) => $rows->pluck('job_role_id')->unique()->values()),
            'jobDescriptionMap' => ClientJobRole::where('status', true)
                ->get(['id', 'client_id', 'job_role_id', 'job_description'])
                ->mapWithKeys(fn ($item) => [
                    $item->client_id.':'.$item->job_role_id => [
                        'id' => $item->id,
                        'description' => $item->job_description,
                    ],
                ]),
            'recruiters' => Recruiter::where('status', true)->orderBy('recruiter_name')->get(),
            'locations' => Location::where('status', true)->orderBy('location')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'billing_id' => 'nullable|exists:billings,id',
            'position_level' => 'nullable|string',
            'revenue_amount' => 'nullable|numeric|min:0',
            'job_description_id' => 'nullable|exists:client_job_roles,id',
            'mode_ids' => 'nullable|array',
            'mode_ids.*' => 'integer|distinct|exists:modes,id',
            'requirement_open_date' => 'nullable|date',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'number_of_position' => 'nullable|integer|min:0',
            'closure_target_date' => 'nullable|date|after_or_equal:requirement_open_date',
            'cv_required' => 'nullable|integer|min:0',
            'cv_uploaded' => 'nullable|integer|min:0',
            'project_owner_ids' => 'nullable|array',
            'project_owner_ids.*' => 'integer|distinct|exists:recruiters,id',
            'is_priority' => 'required|boolean',
            'ctc' => 'nullable|numeric|min:0',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'integer|distinct|exists:locations,id',
            'status' => 'required|in:0,1',
        ]);

        $data['mode_ids'] = array_values(array_unique(array_map('intval', $data['mode_ids'] ?? [])));
        $data['location_ids'] = array_values(array_unique(array_map('intval', $data['location_ids'] ?? [])));
        $data['project_owner_ids'] = array_values(array_unique(array_map('intval', $data['project_owner_ids'] ?? [])));

        $clientJobRole = null;
        if ($data['job_role_id'] ?? null) {
            $clientJobRole = ClientJobRole::where('client_id', $data['client_id'])
                ->where('job_role_id', $data['job_role_id'])
                ->where('status', true)
                ->first();

            if (! $clientJobRole) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'job_role_id' => 'The selected job role is not assigned to this client.',
                ]);
            }
        }

        // The JD always comes from the selected Client Job Role.
        $data['job_description_id'] = $clientJobRole?->id;

        // Keep the original columns populated for backward compatibility with reports/imports.
        $data['mode_id'] = $data['mode_ids'][0] ?? null;
        $data['location_id'] = $data['location_ids'][0] ?? null;
        $data['project_owner'] = $data['project_owner_ids'][0] ?? null;

        return $data;
    }

    private function importHeadings(): array
    {
        return ['Record ID', 'Client', 'Billing', 'Revenue Amount', 'Job Description', 'Modes', 'Requirement Open Date', 'Job Role', 'Number Of Position', 'Closure Target Date', 'CV Required', 'CV Uploaded', 'Project Owner', 'Priority', 'CTC', 'Locations', 'Status'];
    }

    private function spreadsheetBoolean(mixed $value): bool
    {
        return in_array(mb_strtolower(trim((string) $value)), ['1', 'yes', 'true', 'priority'], true);
    }

    /**
     * Resolve a comma-separated spreadsheet cell into unique master IDs.
     *
     * @return array{0: array<int>, 1: array<string>}
     */
    private function masterIdsFromList(string $model, string $column, mixed $value): array
    {
        $names = collect(preg_split('/[,;|]/', (string) $value))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values();

        if ($names->isEmpty()) {
            return [[], []];
        }

        $records = $model::query()
            ->whereIn(DB::raw('LOWER('.$column.')'), $names->map(fn ($name) => mb_strtolower($name)))
            ->get(['id', $column])
            ->keyBy(fn ($record) => mb_strtolower($record->{$column}));

        $ids = [];
        $missing = [];
        foreach ($names as $name) {
            $record = $records->get(mb_strtolower($name));
            $record ? $ids[] = (int) $record->id : $missing[] = $name;
        }

        return [array_values(array_unique($ids)), $missing];
    }

    public function jobDescription(Request $request, int $id)
    {
        $this->authorize('read', ClientRequirement::class);
        $requirement = ClientRequirement::visibleTo($request->user())
            ->with(['jobDescription', 'jobRole', 'client'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'title' => ($requirement->jobRole?->job_role ?? 'Job Description') . ($requirement->client?->client ? ' - ' . $requirement->client->client : ''),
            'content' => $requirement->jobDescription?->job_description ?? '',
        ]);
    }

    public function clientsReportData(Request $request)
    {
        $this->authorize('read', ClientRequirement::class);
        $user = $request->user();

        $rejectedLevelIds = $this->getRejectedLevelIds();

        // 1. Requirement statistics aggregated by client
        $reqStats = ClientRequirement::query()
            ->visibleTo($user)
            ->selectRaw('client_id, 
                COUNT(*) as total_reqs, 
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_reqs,
                SUM(COALESCE(number_of_position, 0)) as total_positions,
                SUM(COALESCE(revenue_amount, 0)) as total_req_revenue')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        // 2. Candidate statistics aggregated by client
        $candidateStats = Candidate::query()
            ->visibleTo($user)
            ->selectRaw('client_id,
                COUNT(*) as total_candidates,
                SUM(CASE WHEN level_of_interview_id = 20 OR onboarding_date IS NOT NULL THEN 1 ELSE 0 END) as onboarded_count')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        // 3. Rejected candidate statistics aggregated by client
        $rejectedStats = Candidate::query()
            ->visibleTo($user)
            ->where(function ($q) use ($rejectedLevelIds) {
                $q->whereIn('level_of_interview_id', $rejectedLevelIds)
                    ->orWhereHas('interviewSchedules', fn ($s) => $s->where('status', 'rejected'))
                    ->orWhere('status', false);
            })
            ->where(function ($q) {
                $q->where(function ($onboardedCheck) {
                    $onboardedCheck->whereNull('level_of_interview_id')
                        ->orWhere('level_of_interview_id', '!=', 20);
                })
                ->whereNull('onboarding_date');
            })
            ->selectRaw('client_id, COUNT(*) as rejected_count')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        // 4. Invoiced revenues aggregated by client
        $revenueStats = Revenue::query()
            ->selectRaw('client_id, SUM(COALESCE(service_amount, total_amount, 0)) as total_invoiced')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        // 5. Calculate placement revenue from onboarded candidates
        $onboardedCandidates = Candidate::query()
            ->visibleTo($user)
            ->with(['clientRequirement.billing', 'client.billing'])
            ->where(function ($q) {
                $q->where('level_of_interview_id', 20)
                    ->orWhereNotNull('onboarding_date');
            })
            ->get(['id', 'client_id', 'client_requirement_id', 'onboarding_ctc', 'expected_ctc']);

        $placementRevenueByClient = [];
        foreach ($onboardedCandidates as $cand) {
            $cid = $cand->client_id;
            if (! $cid) {
                continue;
            }
            $pct = (float) ($cand->clientRequirement?->billing?->value ?? $cand->client?->billing?->value ?? 0);
            $ctc = (float) ($cand->onboarding_ctc ?: $cand->expected_ctc ?: 0);
            $rev = round(($ctc * $pct) / 100, 2);
            $placementRevenueByClient[$cid] = ($placementRevenueByClient[$cid] ?? 0) + $rev;
        }

        $clientsQuery = Client::query()->with(['location', 'division', 'billing'])->latest();

        return DataTables::of($clientsQuery)
            ->filter(function ($query) use ($request) {
                $search = trim((string) $request->input('search.value'));
                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('client', 'like', "%{$search}%")
                            ->orWhere('poc_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%")
                            ->orWhereHas('location', fn ($l) => $l->where('location', 'like', "%{$search}%"))
                            ->orWhereHas('division', fn ($d) => $d->where('name', 'like', "%{$search}%"));
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('client_name', function ($client) {
                $poc = $client->poc_name ? '<br><small class="text-muted"><i class="ri-user-line me-1"></i>'.e($client->poc_name).'</small>' : '';
                return '<div><span class="fw-semibold text-primary">'.e($client->client).'</span>'.$poc.'</div>';
            })
            ->addColumn('location_division', function ($client) {
                $loc = $client->location->location ?? '-';
                $div = $client->division->name ?? '';
                return '<div><span>'.e($loc).'</span>'.($div ? '<br><small class="text-muted">'.e($div).'</small>' : '').'</div>';
            })
            ->addColumn('total_requirements', function ($client) use ($reqStats) {
                $stats = $reqStats->get($client->id);
                $total = (int) ($stats->total_reqs ?? 0);
                $active = (int) ($stats->active_reqs ?? 0);
                return '<span class="badge bg-primary-subtle text-primary fw-semibold fs-6">'.$total.'</span>' .
                    ($active > 0 ? ' <span class="badge bg-success-subtle text-success ms-1">'.$active.' Active</span>' : '');
            })
            ->addColumn('total_positions', function ($client) use ($reqStats) {
                $stats = $reqStats->get($client->id);
                $positions = (int) ($stats->total_positions ?? 0);
                return '<span class="badge bg-info-subtle text-info fw-semibold fs-6">'.$positions.'</span>';
            })
            ->addColumn('onboarded_count', function ($client) use ($candidateStats, $reqStats) {
                $onboarded = (int) ($candidateStats->get($client->id)->onboarded_count ?? 0);
                $positions = (int) ($reqStats->get($client->id)->total_positions ?? 0);
                $badgeClass = ($positions > 0 && $onboarded >= $positions) ? 'bg-success text-white' : 'bg-success-subtle text-success';
                return '<span class="badge '.$badgeClass.' fw-bold fs-6">'.$onboarded.'</span>';
            })
            ->addColumn('rejected_count', function ($client) use ($rejectedStats) {
                $rejected = (int) ($rejectedStats->get($client->id)->rejected_count ?? 0);
                return '<span class="badge bg-danger-subtle text-danger fw-semibold fs-6">'.$rejected.'</span>';
            })
            ->addColumn('revenue_generated', function ($client) use ($revenueStats, $placementRevenueByClient) {
                $invoiced = (float) ($revenueStats->get($client->id)->total_invoiced ?? 0);
                $placement = (float) ($placementRevenueByClient[$client->id] ?? 0);
                $total = max($invoiced, $placement);
                return '<span class="fw-bold text-success fs-6">₹'.number_format($total, 2).'</span>';
            })
            ->editColumn('status', function ($client) {
                return $client->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
            })
            ->addColumn('action', function ($client) {
                return '<div class="d-flex align-items-center gap-1">' .
                    '<button type="button" class="btn btn-sm btn-info view-client-report" data-id="'.$client->id.'" data-name="'.e($client->client).'" title="View Detailed Report"><i class="ri-eye-line me-1"></i>View</button>' .
                    '<a href="'.route('admin.client-requirements.client-report', $client->id).'" class="btn btn-sm btn-outline-secondary" title="Open Full Page Report" target="_blank"><i class="ri-external-link-line"></i></a>' .
                    '</div>';
            })
            ->rawColumns(['client_name', 'location_division', 'total_requirements', 'total_positions', 'onboarded_count', 'rejected_count', 'revenue_generated', 'status', 'action'])
            ->make(true);
    }

    public function clientReport(Request $request, int $id)
    {
        $this->authorize('read', ClientRequirement::class);
        $data = $this->buildClientReportData($id, $request->user());

        return view('backend.client-requirements.client-report', $data);
    }

    public function clientReportData(Request $request, int $id)
    {
        $this->authorize('read', ClientRequirement::class);
        $data = $this->buildClientReportData($id, $request->user());

        $html = view('backend.client-requirements.partials.client-report-content', $data)->render();

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $data['client']->id,
                'name' => $data['client']->client,
            ],
            'html' => $html,
        ]);
    }

    private function buildClientReportData(int $clientId, User $user): array
    {
        $client = Client::with(['location', 'division', 'billing', 'clientJobRoles.jobRole'])->findOrFail($clientId);

        $modeNames = Mode::pluck('mode', 'id');
        $locationNames = Location::pluck('location', 'id');
        $recruiterNames = Recruiter::pluck('recruiter_name', 'id');

        // Requirements for this client visible to the user
        $requirements = ClientRequirement::query()
            ->where('client_id', $client->id)
            ->visibleTo($user)
            ->with(['jobRole', 'jobDescription', 'mode', 'location', 'billing', 'projectOwner'])
            ->withCount(['candidates', 'onboardedCandidates'])
            ->latest()
            ->get();

        foreach ($requirements as $req) {
            $req->resolved_modes = collect($req->mode_ids ?: array_filter([$req->mode_id]))
                ->map(fn ($id) => $modeNames->get((int) $id))->filter()->join(', ') ?: '-';
            $req->resolved_locations = collect($req->location_ids ?: array_filter([$req->location_id]))
                ->map(fn ($id) => $locationNames->get((int) $id))->filter()->join(', ') ?: '-';
            $req->resolved_project_owners = collect($req->project_owner_ids ?: array_filter([$req->project_owner]))
                ->map(fn ($id) => $recruiterNames->get((int) $id))->filter()->join(', ') ?: '-';
        }

        // Candidates for this client visible to the user
        $candidates = Candidate::query()
            ->where(function ($q) use ($client) {
                $q->where('client_id', $client->id)
                    ->orWhereHas('clientRequirement', fn ($cr) => $cr->where('client_id', $client->id));
            })
            ->visibleTo($user)
            ->with([
                'jobRole',
                'recruiter',
                'interviewLevel',
                'mode',
                'clientRequirement.billing',
                'clientRequirement.jobRole',
                'interviewSchedules',
            ])
            ->latest()
            ->get();

        foreach ($candidates as $cand) {
            $statusInfo = $this->classifyCandidateStatus($cand);
            $cand->status_type = $statusInfo['type'];
            $cand->status_stage = $statusInfo['stage'];
            $cand->status_category = $statusInfo['category'];
            $cand->status_badge = $statusInfo['badge'];
            $cand->status_reason = $statusInfo['reason'];

            $billingPct = (float) (
                $cand->clientRequirement?->billing?->value
                ?? $client->billing?->value
                ?? 0
            );
            $ctc = (float) ($cand->onboarding_ctc ?: $cand->expected_ctc ?: 0);
            $calcRev = round(($ctc * $billingPct) / 100, 2);
            if ($calcRev <= 0 && $cand->clientRequirement?->revenue_amount) {
                $calcRev = (float) $cand->clientRequirement->revenue_amount;
            }
            $cand->calculated_revenue = $calcRev;
            $cand->billing_percentage = $billingPct;
        }

        $onboardedCandidates = $candidates->where('status_type', 'onboarded')->values();
        $rejectedCandidates = $candidates->where('status_type', 'rejected')->values();
        $inProgressCandidates = $candidates->where('status_type', 'in_progress')->values();

        // Invoiced revenues for this client
        $revenues = Revenue::where('client_id', $client->id)
            ->with(['candidate', 'candidates'])
            ->latest()
            ->get();

        $invoicedAmount = (float) $revenues->sum('service_amount');
        if ($invoicedAmount <= 0) {
            $invoicedAmount = (float) $revenues->sum('total_amount');
        }

        $invoicedCandidateIds = $revenues->pluck('candidate_id')->filter()->all();
        foreach ($revenues as $rev) {
            if ($rev->candidates && $rev->candidates->isNotEmpty()) {
                $invoicedCandidateIds = array_merge($invoicedCandidateIds, $rev->candidates->pluck('id')->all());
            }
        }
        $invoicedCandidateIds = array_unique(array_filter($invoicedCandidateIds));

        $uninvoicedPlacementRevenue = $onboardedCandidates
            ->reject(fn ($c) => in_array($c->id, $invoicedCandidateIds, true))
            ->sum('calculated_revenue');

        $totalPlacementRevenue = $onboardedCandidates->sum('calculated_revenue');
        $totalRevenueGenerated = max($invoicedAmount + $uninvoicedPlacementRevenue, $totalPlacementRevenue);

        $rejectionStats = [
            'screening' => $rejectedCandidates->where('status_category', 'Screening')->count(),
            'technical' => $rejectedCandidates->where('status_category', 'Technical Interview')->count(),
            'hr' => $rejectedCandidates->where('status_category', 'HR Round')->count(),
            'declined' => $rejectedCandidates->whereIn('status_category', ['Offer Drop', 'Onboarding Drop', 'Declined'])->count(),
            'dropped' => $rejectedCandidates->whereIn('status_category', ['Candidate Dropped', 'Dropped / Inactive', 'Interview Reject'])->count(),
        ];

        $totalPositions = (int) $requirements->sum('number_of_position');
        $onboardedCount = $onboardedCandidates->count();
        $fillRate = $totalPositions > 0 ? round(($onboardedCount / $totalPositions) * 100, 1) : 0;

        $metrics = [
            'total_requirements' => $requirements->count(),
            'active_requirements' => $requirements->where('status', true)->count(),
            'inactive_requirements' => $requirements->where('status', false)->count(),
            'total_positions' => $totalPositions,
            'onboarded_positions' => $onboardedCount,
            'remaining_positions' => max(0, $totalPositions - $onboardedCount),
            'total_onboarded' => $onboardedCount,
            'total_rejected' => $rejectedCandidates->count(),
            'total_candidates' => $candidates->count(),
            'in_progress_candidates' => $inProgressCandidates->count(),
            'total_revenue' => $totalRevenueGenerated,
            'invoiced_revenue' => $invoicedAmount,
            'placement_revenue' => $totalPlacementRevenue,
            'requirement_revenue' => (float) $requirements->sum('revenue_amount'),
            'fill_rate' => $fillRate,
            'cv_required' => (int) $requirements->sum('cv_required'),
            'cv_uploaded' => (int) $requirements->sum('cv_uploaded'),
        ];

        return [
            'client' => $client,
            'requirements' => $requirements,
            'onboardedCandidates' => $onboardedCandidates,
            'rejectedCandidates' => $rejectedCandidates,
            'inProgressCandidates' => $inProgressCandidates,
            'allCandidates' => $candidates,
            'revenues' => $revenues,
            'rejectionStats' => $rejectionStats,
            'metrics' => $metrics,
        ];
    }

    private function getRejectedLevelIds(): array
    {
        return InterviewLevel::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(level) LIKE ?', ['%reject%'])
                    ->orWhereRaw('LOWER(level) LIKE ?', ['%decline%'])
                    ->orWhereRaw('LOWER(level) LIKE ?', ['%not interested%'])
                    ->orWhereRaw('LOWER(level) LIKE ?', ['%not responding%'])
                    ->orWhereIn('id', [
                        InterviewLevel::CANDIDATE_NOT_INTERESTED_ID,
                        InterviewLevel::CANDIDATE_NOT_RESPONDING_ID,
                        InterviewLevel::CANDIDATE_POSITION_CLOSED_ID,
                        InterviewLevel::CANDIDATE_NOT_POSITION_ID,
                        21, // Joiner Declined
                        22, // Offer Declined
                    ]);
            })
            ->pluck('id')
            ->all();
    }

    private function classifyCandidateStatus($candidate): array
    {
        $rawLevelName = $candidate->relationLoaded('interviewLevel')
            ? ($candidate->interviewLevel?->level ?? '')
            : '';
        $levelName = mb_strtolower((string) $rawLevelName);

        $isOnboarded = (int) $candidate->level_of_interview_id === 20
            || ! empty($candidate->onboarding_date)
            || str_contains($levelName, 'onboard');

        if ($isOnboarded) {
            return [
                'type' => 'onboarded',
                'stage' => 'Onboarded',
                'category' => 'Onboarded',
                'badge' => 'bg-success-subtle text-success',
                'reason' => 'Successfully Onboarded',
            ];
        }

        $levelId = (int) $candidate->level_of_interview_id;
        $hasRejectedSchedule = $candidate->relationLoaded('interviewSchedules')
            ? ($candidate->interviewSchedules?->contains(fn ($s) => strtolower((string) $s->status) === 'rejected') ?? false)
            : false;

        if (str_contains($levelName, 'screen reject') || (str_contains($levelName, 'screen') && str_contains($levelName, 'reject'))) {
            return ['type' => 'rejected', 'stage' => 'Screen Reject', 'category' => 'Screening', 'badge' => 'bg-danger-subtle text-danger', 'reason' => $rawLevelName ?: 'Screen Reject'];
        }
        if (str_contains($levelName, 'l1 reject')) {
            return ['type' => 'rejected', 'stage' => 'L1 Reject', 'category' => 'Technical Interview', 'badge' => 'bg-danger-subtle text-danger', 'reason' => 'L1 Interview Reject'];
        }
        if (str_contains($levelName, 'l2 reject')) {
            return ['type' => 'rejected', 'stage' => 'L2 Reject', 'category' => 'Technical Interview', 'badge' => 'bg-danger-subtle text-danger', 'reason' => 'L2 Interview Reject'];
        }
        if (str_contains($levelName, 'l3 reject')) {
            return ['type' => 'rejected', 'stage' => 'L3 Reject', 'category' => 'Technical Interview', 'badge' => 'bg-danger-subtle text-danger', 'reason' => 'L3 Interview Reject'];
        }
        if (str_contains($levelName, 'l4 reject')) {
            return ['type' => 'rejected', 'stage' => 'L4 Reject', 'category' => 'Technical Interview', 'badge' => 'bg-danger-subtle text-danger', 'reason' => 'L4 Interview Reject'];
        }
        if (str_contains($levelName, 'hr reject')) {
            return ['type' => 'rejected', 'stage' => 'HR Reject', 'category' => 'HR Round', 'badge' => 'bg-danger-subtle text-danger', 'reason' => 'HR Round Reject'];
        }
        if (str_contains($levelName, 'joiner declined') || $levelId === 21) {
            return ['type' => 'rejected', 'stage' => 'Joiner Declined', 'category' => 'Onboarding Drop', 'badge' => 'bg-warning-subtle text-warning', 'reason' => 'Candidate declined joining'];
        }
        if (str_contains($levelName, 'offer declined') || $levelId === 22) {
            return ['type' => 'rejected', 'stage' => 'Offer Declined', 'category' => 'Offer Drop', 'badge' => 'bg-warning-subtle text-warning', 'reason' => 'Candidate declined offer'];
        }
        if (str_contains($levelName, 'not interested') || $levelId === InterviewLevel::CANDIDATE_NOT_INTERESTED_ID) {
            return ['type' => 'rejected', 'stage' => 'Not Interested', 'category' => 'Candidate Dropped', 'badge' => 'bg-secondary-subtle text-secondary', 'reason' => 'Candidate not interested'];
        }
        if (str_contains($levelName, 'not responding') || $levelId === InterviewLevel::CANDIDATE_NOT_RESPONDING_ID) {
            return ['type' => 'rejected', 'stage' => 'Not Responding', 'category' => 'Candidate Dropped', 'badge' => 'bg-secondary-subtle text-secondary', 'reason' => 'Candidate not responding'];
        }
        if (str_contains($levelName, 'reject')) {
            return ['type' => 'rejected', 'stage' => $rawLevelName ?: 'Rejected', 'category' => 'Interview Reject', 'badge' => 'bg-danger-subtle text-danger', 'reason' => $rawLevelName ?: 'Rejected'];
        }
        if (str_contains($levelName, 'declined')) {
            return ['type' => 'rejected', 'stage' => $rawLevelName ?: 'Declined', 'category' => 'Declined', 'badge' => 'bg-warning-subtle text-warning', 'reason' => $rawLevelName ?: 'Declined'];
        }
        if ($hasRejectedSchedule) {
            $schedule = $candidate->interviewSchedules->firstWhere(fn ($s) => strtolower((string) $s->status) === 'rejected');
            return ['type' => 'rejected', 'stage' => 'Schedule Rejected', 'category' => 'Interview Reject', 'badge' => 'bg-danger-subtle text-danger', 'reason' => $schedule->remarks ?: 'Interview schedule marked as rejected'];
        }
        if ($candidate->status === false) {
            return ['type' => 'rejected', 'stage' => 'Inactive', 'category' => 'Dropped / Inactive', 'badge' => 'bg-dark-subtle text-dark', 'reason' => $candidate->reason_for_change ?: ($rawLevelName ?: 'Candidate inactive')];
        }

        return [
            'type' => 'in_progress',
            'stage' => $rawLevelName ?: 'In Progress',
            'category' => 'In Progress',
            'badge' => 'bg-info-subtle text-info',
            'reason' => 'Active in recruitment pipeline',
        ];
    }
}
