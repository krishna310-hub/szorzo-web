<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\InterviewLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InterviewLevelController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', InterviewLevel::class);

        if ($request->ajax()) {
            $levels = InterviewLevel::orderBy('sort_order')->latest();

            return DataTables::of($levels)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success-subtle text-success">Active</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', InterviewLevel::class)) {
                        $buttons .= '<a href="' . route('admin.interview-levels.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', InterviewLevel::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.interview-levels.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.interview-levels.index');
    }

    public function create()
    {
        $this->authorize('create', InterviewLevel::class);
        return view('backend.interview-levels.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', InterviewLevel::class);

        $data = $request->validate([
            'level' => [
                'required',
                'string',
                'max:255',
                Rule::unique('level_of_interviews', 'level')->whereNull('deleted_at'),
            ],
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        InterviewLevel::create($data);

        return redirect()->route('admin.interview-levels.index')->with('success', 'Interview level created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InterviewLevel::class);
        $interviewLevel = InterviewLevel::findOrFail($id);

        return view('backend.interview-levels.edit', compact('interviewLevel'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', InterviewLevel::class);
        $interviewLevel = InterviewLevel::findOrFail($id);

        $data = $request->validate([
            'level' => [
                'required',
                'string',
                'max:255',
                Rule::unique('level_of_interviews', 'level')->ignore($interviewLevel->id)->whereNull('deleted_at'),
            ],
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $interviewLevel->update($data);

        return redirect()->route('admin.interview-levels.index')->with('success', 'Interview level updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', InterviewLevel::class);
        InterviewLevel::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Interview level deleted successfully.']);
    }
}
