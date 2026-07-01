<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CandidateController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Candidate::class);

        if ($request->ajax()) {
            $query = Candidate::with(['recruiter', 'client', 'jobRole', 'interviewLevel'])->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('recruiter_name', fn ($row) => $row->recruiter->recruiter_name ?? '-')
                ->addColumn('client_name', fn ($row) => $row->client->client ?? '-')
                ->addColumn('job_role_name', fn ($row) => $row->jobRole->job_role ?? '-')
                ->addColumn('interview_level', fn ($row) => $row->interviewLevel->level ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('Y-m-d H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Candidate::class)) {
                        $buttons .= '<a href="' . route('admin.candidates.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Candidate::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.candidates.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.candidates.index');
    }

    public function create()
    {
        $this->authorize('create', Candidate::class);
        return view('backend.candidates.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Candidate::class);
        Candidate::create($this->validatedData($request));
        return redirect()->route('admin.candidates.index')->with('success', 'Candidate created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Candidate::class);
        return view('backend.candidates.edit', array_merge(
            ['candidate' => Candidate::findOrFail($id)],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Candidate::class);
        Candidate::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.candidates.index')->with('success', 'Candidate updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Candidate::class);
        Candidate::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Candidate deleted successfully.']);
    }

    private function formData(): array
    {
        return [
            'recruiters' => Recruiter::where('status', true)->orderBy('recruiter_name')->get(),
            'clients' => Client::where('status', true)->orderBy('client')->get(),
            'jobRoles' => JobRole::where('status', true)->orderBy('job_role')->get(),
            'interviewLevels' => InterviewLevel::where('status', true)->orderBy('sort_order')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'recruiter_id' => 'nullable|exists:recruiters,id',
            'client_id' => 'nullable|exists:clients,id',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'candidate_name' => 'required|string|max:255',
            'mobile_no' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'qualification' => 'nullable|string|max:255',
            'total_experience' => 'nullable|numeric|min:0',
            'relevant_experience' => 'nullable|numeric|min:0',
            'take_home' => 'nullable|numeric|min:0',
            'variable' => 'nullable|numeric|min:0',
            'current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'current_location' => 'nullable|string|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'reason_for_change' => 'nullable|string',
            'level_of_interview_id' => 'nullable|exists:level_of_interviews,id',
            'status' => 'required|in:0,1',
        ]);
    }
}
