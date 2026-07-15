<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterviewMode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InterviewModeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {

        if ($request->ajax()) {
            return DataTables::of(InterviewMode::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('d-m-Y H:i:s') : '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                        $buttons .= '<a href="' . route('admin.interview-modes.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                        $buttons .= '<button type="button" data-route="' . route('admin.interview-modes.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.interview-modes.index');
    }

    public function create()
    {
        return view('backend.interview-modes.create');
    }

    public function store(Request $request)
    {
        InterviewMode::create($this->validatedData($request));

        return redirect()->route('admin.interview-modes.index')->with('success', 'Interview Mode created successfully.');
    }

    public function edit($id)
    {
        $interview_mode = InterviewMode::findOrFail($id);

        return view('backend.interview-modes.edit', compact('interview_mode'));
    }

    public function update(Request $request, $id)
    {
        $interview_mode = InterviewMode::findOrFail($id);
        $interview_mode->update($this->validatedData($request, $interview_mode->id));

        return redirect()->route('admin.interview-modes.index')->with('success', 'Interview Mode updated successfully.');
    }

    public function destroy($id)
    {
        InterviewMode::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Interview Mode deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'interview_mode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('interview_modes', 'interview_mode')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'status' => 'required|in:0,1',
        ]);
    }
}
