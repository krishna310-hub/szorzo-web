<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\JobRole;
use App\Models\Location;
use App\Models\Mode;
use App\Models\Recruiter;
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
                    ? '<button type="button" class="btn btn-link text-primary fs-4 p-0 view-job-description" title="View Job Description" aria-label="View Job Description"><i class="ri-eye-line"></i></button>'
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
                ->rawColumns(['job_description_content', 'job_description_action', 'status', 'priority', 'action'])
                ->make(true);
        }

        return view('backend.client-requirements.index');
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

    public function export()
    {
        $this->authorize('read', ClientRequirement::class);
        $modeNames = Mode::pluck('mode', 'id');
        $locationNames = Location::pluck('location', 'id');
        $recruiterNames = Recruiter::pluck('recruiter_name', 'id');

        $rows = ClientRequirement::visibleTo(request()->user())
            ->with(['client', 'billing', 'jobDescription', 'mode', 'jobRole', 'projectOwner', 'location'])
            ->latest()->get()->map(fn ($item) => [
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
}
