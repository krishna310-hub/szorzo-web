<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\JobRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class JobRoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', JobRole::class);

        if ($request->ajax()) {
            return DataTables::of(JobRole::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', JobRole::class)) {
                        $buttons .= '<a href="' . route('admin.job-roles.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', JobRole::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.job-roles.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.job-roles.index');
    }

    public function create()
    {
        $this->authorize('create', JobRole::class);
        return view('backend.job-roles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', JobRole::class);
        JobRole::create($this->validatedData($request));

        return redirect()->route('admin.job-roles.index')->with('success', 'Job role created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', JobRole::class);
        $jobRole = JobRole::findOrFail($id);

        return view('backend.job-roles.edit', compact('jobRole'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', JobRole::class);
        $jobRole = JobRole::findOrFail($id);
        $jobRole->update($this->validatedData($request, $jobRole->id));

        return redirect()->route('admin.job-roles.index')->with('success', 'Job role updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', JobRole::class);
        JobRole::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Job role deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'job_role' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_roles', 'job_role')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'status' => 'required|in:0,1',
        ]);
    }
}
