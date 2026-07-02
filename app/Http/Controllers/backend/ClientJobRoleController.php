<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\JobRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                        $buttons .= '<a href="' . route('admin.client-job-roles.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', ClientJobRole::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.client-job-roles.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
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
}
