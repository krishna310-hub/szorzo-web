<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\JobRole;
use App\Models\Location;
use App\Models\Mode;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientRequirementController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ClientRequirement::class);

        if ($request->ajax()) {
            $query = ClientRequirement::with(['client', 'jobDescription', 'mode', 'jobRole', 'location', 'projectOwner'])->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('client_name', fn($row) => $row->client->client ?? '-')
                ->addColumn('job_description_name', fn($row) => $row->jobDescription->job_description ?? '-')
                ->addColumn('mode_name', fn($row) => $row->mode->mode ?? '-')
                ->addColumn('job_role_name', fn($row) => $row->jobRole->job_role ?? '-')
                ->addColumn('location_name', fn($row) => $row->location->location ?? '-')
                ->addColumn('project_owner_name', fn($row) => $row->projectOwner->recruiter_name ?? '-')
                ->editColumn('billing', fn($row) => $row->billing !== null ? $row->billing . '%' : '-')
                ->editColumn('requirement_open_date', fn($row) => $row->requirement_open_date?->format('Y-m-d') ?? '-')
                ->editColumn('closure_target_date', fn($row) => $row->closure_target_date?->format('Y-m-d') ?? '-')
                ->editColumn('ctc', fn($row) => $row->ctc !== null ? number_format((float) $row->ctc, 2) : '-')
                ->editColumn('status', fn($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn($row) => $row->created_at?->format('Y-m-d H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', ClientRequirement::class)) {
                        $buttons .= '<a href="' . route('admin.client-requirements.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', ClientRequirement::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.client-requirements.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
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

    private function formData(): array
    {
        return [
            'clients' => Client::where('status', true)->orderBy('client')->get(),
            'billing'=>Billing::where('status', true)->orderBy('value')->get(),
            'jobDescriptions' => ClientJobRole::where('status', true)->orderBy('job_description')->get(),
            'modes' => Mode::where('status', true)->orderBy('mode')->get(),
            'jobRoles' => JobRole::where('status', true)->orderBy('job_role')->get(),
            'recruiters' => Recruiter::where('status', true)->orderBy('recruiter_name')->get(),
            'locations' => Location::where('status', true)->orderBy('location')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'billing_id' => 'required|exists:billings,id',
            'billing' => 'nullable|numeric|min:0|max:100',
            'job_description_id' => 'nullable|exists:client_job_roles,id',
            'mode_id' => 'nullable|exists:modes,id',
            'requirement_open_date' => 'nullable|date',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'number_of_position' => 'nullable|integer|min:0',
            'closure_target_date' => 'nullable|date|after_or_equal:requirement_open_date',
            'cv_required' => 'nullable|integer|min:0',
            'cv_uploaded' => 'nullable|integer|min:0',
            'project_owner' => 'nullable|exists:recruiters,id',
            'ctc' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'status' => 'required|in:0,1',
        ]);
    }
}
