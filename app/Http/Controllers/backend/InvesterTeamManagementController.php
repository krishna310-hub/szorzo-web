<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Invester;
use App\Models\InvesterTeamManagement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterTeamManagementController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', InvesterTeamManagement::class);

        if ($request->ajax()) {
            $teams = InvesterTeamManagement::with('invester')->latest();

            return DataTables::of($teams)
                ->addIndexColumn()
                ->addColumn('invester_name', function ($row) {
                    if (!$row->invester) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    return '<div><strong>' . e($row->invester->name) . '</strong><br><small class="text-muted">' . e($row->invester->invester_id) . '</small></div>';
                })
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', InvesterTeamManagement::class)) {
                        $buttons .= '<a href="' . route('admin.invester-team-managements.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', InvesterTeamManagement::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.invester-team-managements.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['invester_name', 'status', 'action'])
                ->make(true);
        }

        return view('backend.invester-team-managements.index');
    }

    public function create()
    {
        $this->authorize('create', InvesterTeamManagement::class);

        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-team-managements.create', compact('investers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvesterTeamManagement::class);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'team_name' => 'required|string|max:255',
            'managed_employee_count' => 'nullable|string|max:50',
            'operational_contacts' => 'nullable|string',
            'governance_role' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        InvesterTeamManagement::create($validated);

        return redirect()->route('admin.invester-team-managements.index')->with('success', 'Team & Management Scope record created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InvesterTeamManagement::class);

        $teamManagement = InvesterTeamManagement::findOrFail($id);
        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-team-managements.edit', compact('teamManagement', 'investers'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', InvesterTeamManagement::class);

        $teamManagement = InvesterTeamManagement::findOrFail($id);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'team_name' => 'required|string|max:255',
            'managed_employee_count' => 'nullable|string|max:50',
            'operational_contacts' => 'nullable|string',
            'governance_role' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $teamManagement->update($validated);

        return redirect()->route('admin.invester-team-managements.index')->with('success', 'Team & Management Scope record updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', InvesterTeamManagement::class);

        $teamManagement = InvesterTeamManagement::findOrFail($id);
        $teamManagement->delete();

        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', InvesterTeamManagement::class);

        $rows = InvesterTeamManagement::with('invester')->get()->map(function ($row) {
            return [
                $row->invester?->invester_id ?? '-',
                $row->invester?->name ?? 'N/A',
                $row->team_name,
                $row->managed_employee_count,
                $row->operational_contacts,
                $row->governance_role,
                $row->notes,
                $row->status ? 'Active' : 'Inactive',
                $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            ];
        })->toArray();

        $headings = [
            'Invester ID',
            'Invester Name',
            'Team / Organization Name',
            'Managed Employee Count',
            'Key Operational Contacts / Delegates',
            'Management / Governance Role',
            'Notes',
            'Status',
            'Created Date',
        ];

        $dropdowns = [
            'Managed Employee Count' => ['1-10', '11-50', '50+'],
            'Status' => ['Active', 'Inactive'],
        ];

        return Excel::download(new MasterDataExport($headings, $rows, $dropdowns), 'invester-team-management-' . date('Y-m-d') . '.xlsx');
    }
}

