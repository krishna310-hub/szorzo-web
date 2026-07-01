<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
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
            $requirements = ClientRequirement::with(['client', 'mode', 'jobRole', 'location', 'projectOwner'])->latest();

            return DataTables::of($requirements)
                ->addIndexColumn()
                ->addColumn('client_name', fn ($row) => $row->client->name ?? '-')
                ->addColumn('mode_name', fn ($row) => $row->mode->name ?? '-')
                ->addColumn('job_role_name', fn ($row) => $row->jobRole->name ?? '-')
                ->addColumn('location_name', fn ($row) => $row->location->name ?? '-')
                ->addColumn('project_owner_name', fn ($row) => $row->projectOwner->name ?? '-')
                ->editColumn('billing_percentage', fn ($row) => $row->billing_percentage !== null ? $row->billing_percentage . '%' : '-')
                ->editColumn('open_date', fn ($row) => $row->open_date ? $row->open_date->format('Y-m-d') : '-')
                ->editColumn('closure_target_date', fn ($row) => $row->closure_target_date ? $row->closure_target_date->format('Y-m-d') : '-')
                ->editColumn('ctc', fn ($row) => $row->ctc !== null ? number_format((float) $row->ctc, 2) : '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
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
        $clientRequirement = ClientRequirement::findOrFail($id);

        return view('backend.client-requirements.edit', array_merge(
            ['clientRequirement' => $clientRequirement],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', ClientRequirement::class);
        $clientRequirement = ClientRequirement::findOrFail($id);
        $clientRequirement->update($this->validatedData($request));

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
            'clients' => Client::where('status', true)->orderBy('name')->get(),
            'modes' => Mode::where('status', true)->orderBy('name')->get(),
            'jobRoles' => JobRole::where('status', true)->orderBy('name')->get(),
            'locations' => Location::where('status', true)->orderBy('name')->get(),
            'recruiters' => Recruiter::where('status', true)->orderBy('name')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'billing_percentage' => 'nullable|numeric|min:0|max:100',
            'job_description_id' => 'nullable|string|max:255',
            'mode_id' => 'nullable|exists:modes,id',
            'open_date' => 'nullable|date',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'ctc' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'no_of_positions' => 'nullable|integer|min:0',
            'closure_target_date' => 'nullable|date',
            'cvs_required' => 'nullable|integer|min:0',
            'cvs_uploaded' => 'nullable|integer|min:0',
            'project_owner_id' => 'nullable|exists:recruiters,id',
            'status' => 'required|in:0,1',
        ]);
    }
}
