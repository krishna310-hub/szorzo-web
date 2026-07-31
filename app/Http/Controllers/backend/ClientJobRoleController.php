<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\JobRole;
use App\Support\MasterDataSpreadsheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ClientJobRoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ClientJobRole::class);

        if ($request->ajax()) {
            $clientJobRoles = ClientJobRole::with(['client', 'jobRole'])->latest();

            return DataTables::of($clientJobRoles)
                ->filter(function ($clientJobRoles) use ($request) {
                    $search = trim((string) $request->input('search.value'));

                    if ($search !== '') {
                        $clientJobRoles->where(function ($q) use ($search) {

                            // Job Role
                            $q->orWhereHas('jobRole', function ($jobRoleQuery) use ($search) {
                                $jobRoleQuery->where('job_role', 'like', "%{$search}%");
                            });

                            // Client
                            $q->orWhereHas('client', function ($clientQuery) use ($search) {
                                $clientQuery->where('client', 'like', "%{$search}%");
                            });
                        });
                    }
                })

                ->addIndexColumn()
                ->addColumn('client_name', fn ($row) => $row->client->client ?? '-')
                ->addColumn('job_role_name', fn ($row) => $row->jobRole->job_role ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', ClientJobRole::class)) {
                        $buttons .= '<a href="'.route('admin.client-job-roles.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', ClientJobRole::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.client-job-roles.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.client-job-roles.index');
    }

    public function create()
    {
        $this->authorize('create', ClientJobRole::class);
        $clients = Client::where('status', true)->orderBy('client')->get();
        $jobRoles = JobRole::where('status', true)->orderBy('job_role')->get();

        return view('backend.client-job-roles.create', compact('clients', 'jobRoles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', ClientJobRole::class);
        ClientJobRole::create($this->validatedData($request));

        return redirect()->route('admin.client-job-roles.index')->with('success', 'Client job role created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', ClientJobRole::class);
        $clientJobRole = ClientJobRole::findOrFail($id);
        $clients = Client::where('status', true)->orderBy('client')->get();
        $jobRoles = JobRole::where('status', true)->orderBy('job_role')->get();

        return view('backend.client-job-roles.edit', compact('clientJobRole', 'clients', 'jobRoles'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', ClientJobRole::class);
        $clientJobRole = ClientJobRole::findOrFail($id);
        $clientJobRole->update($this->validatedData($request, $clientJobRole->id));

        return redirect()->route('admin.client-job-roles.index')->with('success', 'Client job role updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', ClientJobRole::class);
        ClientJobRole::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Client job role deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', ClientJobRole::class);

        $rows = ClientJobRole::with(['client', 'jobRole'])->orderBy('client_id')->get()->map(fn ($item) => [
            $item->id,
            $item->client?->client,
            $item->jobRole?->job_role,
            $item->poc_name,
            $item->contact_number,
            $item->job_description,
            $item->status ? 'Active' : 'Inactive',
        ])->all();

        return Excel::download(new MasterDataExport($this->importHeadings(), $rows), 'client-job-roles-'.now()->format('Y-m-d').'.xlsx');
    }

    public function importTemplate()
    {
        $this->authorize('create', ClientJobRole::class);

        return Excel::download(new MasterDataExport($this->importHeadings(), [[
            null, 'Existing Client Name', 'Existing Job Role', 'Contact Person', '9876543210', 'Role responsibilities', 'Active',
        ]]), 'client-job-roles-import-template.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorize('create', ClientJobRole::class);
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
            $jobRoleName = $row['job_role'] ?? null;
            $data = [
                'id' => $row['record_id'] ?? null,
                'client_id' => MasterDataSpreadsheet::lookup(Client::class, 'client', $clientName),
                'job_role_id' => MasterDataSpreadsheet::lookup(JobRole::class, 'job_role', $jobRoleName),
                'poc_name' => $row['poc_name'] ?? null,
                'contact_number' => $row['contact_number'] ?? null,
                'job_description' => $row['job_description'] ?? null,
                'status' => MasterDataSpreadsheet::status($row['status'] ?? null),
            ];

            $validator = Validator::make($data, [
                'id' => 'nullable|integer|exists:client_job_roles,id',
                'client_id' => 'required|exists:clients,id',
                'job_role_id' => 'required|exists:job_roles,id',
                'poc_name' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:30',
                'job_description' => 'nullable|string',
                'status' => 'required|in:0,1',
            ], [
                'client_id.required' => 'Client "'.$clientName.'" was not found.',
                'job_role_id.required' => 'Job role "'.$jobRoleName.'" was not found.',
            ]);

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
                $item = $id
                    ? ClientJobRole::findOrFail($id)
                    : ClientJobRole::where('client_id', $data['client_id'])->where('job_role_id', $data['job_role_id'])->first();
                $item ? $item->update($data) : ClientJobRole::create($data);
            }
        });

        return back()->with('success', count($validRows).' client job role row(s) imported successfully.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'job_role_id' => [
                'required',
                'exists:job_roles,id',
                Rule::unique('client_job_roles', 'job_role_id')
                    ->where('client_id', $request->client_id)
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'job_description' => 'nullable|string',
            'poc_name' => 'nullable|string',
            'contact_number' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);
    }

    private function importHeadings(): array
    {
        return ['Record ID', 'Client', 'Job Role', 'PoC Name', 'Contact Number', 'Job Description', 'Status'];
    }
}
