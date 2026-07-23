<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\InterviewLevel;
use App\Models\InterviewMode;
use App\Models\InterviewSchedule;
use App\Models\JobRole;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InterviewScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Candidate::class);

        if ($request->ajax()) {
            $query = $this->scheduleQuery($request)
                ->latest('schedule_date');

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn(
                    'candidate_name',
                    fn ($row) => $row->candidate?->candidate_name ?? '-'
                )
                ->filterColumn('candidate_name', function ($query, $keyword) {
                    $query->whereHas('candidate', function ($candidateQuery) use ($keyword) {
                        $candidateQuery->where(
                            'candidate_name',
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })

                ->addColumn(
                    'candidate_mobile',
                    fn ($row) => $row->candidate?->mobile_no ?? '-'
                )
                ->filterColumn('candidate_mobile', function ($query, $keyword) {
                    $query->whereHas('candidate', function ($candidateQuery) use ($keyword) {
                        $candidateQuery->where(
                            'mobile_no',
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })

                ->addColumn(
                    'recruiter_name',
                    fn ($row) => $row->candidate?->recruiter?->recruiter_name ?? '-'
                )
                ->filterColumn('recruiter_name', function ($query, $keyword) {
                    $query->whereHas('candidate.recruiter', function ($recruiterQuery) use ($keyword) {
                        $recruiterQuery->where(
                            'recruiter_name',
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })

                ->addColumn(
                    'client_name',
                    fn ($row) => $row->client?->client
                        ?? $row->candidate?->client?->client
                        ?? '-'
                )
                ->filterColumn('client_name', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->whereHas('client', function ($clientQuery) use ($keyword) {
                                $clientQuery->where(
                                    'client',
                                    'like',
                                    "%{$keyword}%"
                                );
                            })
                            ->orWhereHas('candidate.client', function ($clientQuery) use ($keyword) {
                                $clientQuery->where(
                                    'client',
                                    'like',
                                    "%{$keyword}%"
                                );
                            });
                    });
                })

                ->addColumn(
                    'job_role_name',
                    fn ($row) => $row->jobRole?->job_role
                        ?? $row->candidate?->jobRole?->job_role
                        ?? '-'
                )
                ->filterColumn('job_role_name', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->whereHas('jobRole', function ($roleQuery) use ($keyword) {
                                $roleQuery->where(
                                    'job_role',
                                    'like',
                                    "%{$keyword}%"
                                );
                            })
                            ->orWhereHas('candidate.jobRole', function ($roleQuery) use ($keyword) {
                                $roleQuery->where(
                                    'job_role',
                                    'like',
                                    "%{$keyword}%"
                                );
                            });
                    });
                })

                ->addColumn(
                    'interview_level',
                    fn ($row) => $row->interviewLevel?->level ?? '-'
                )
                ->filterColumn('interview_level', function ($query, $keyword) {
                    $query->whereHas('interviewLevel', function ($levelQuery) use ($keyword) {
                        $levelQuery->where(
                            'level',
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })

                ->editColumn(
                    'schedule_date',
                    fn ($row) => $row->schedule_date?->format('d-m-Y H:i') ?? '-'
                )
                ->filterColumn('schedule_date', function ($query, $keyword) {
                    $query->whereRaw(
                        "DATE_FORMAT(schedule_date, '%d-%m-%Y %H:%i') LIKE ?",
                        ["%{$keyword}%"]
                    );
                })

                ->editColumn(
                    'status',
                    fn ($row) => $this->statusBadge($row->status)
                )
                ->filterColumn('status', function ($query, $keyword) {
                    $statusMap = [
                        'scheduled'   => 0,
                        'completed'   => 1,
                        'cancelled'   => 2,
                        'rescheduled' => 3,
                    ];

                    $keyword = strtolower(trim($keyword));

                    if (array_key_exists($keyword, $statusMap)) {
                        $query->where('status', $statusMap[$keyword]);
                    } else {
                        $query->where('status', 'like', "%{$keyword}%");
                    }
                })

                ->editColumn(
                    'notes',
                    fn ($row) => $row->notes ?: '-'
                )

                ->addColumn('action', function ($row) {
                    $buttons = '<a href="'.
                        route('admin.interview-schedules.show', $row->candidate_id).
                        '" class="text-primary fs-4 me-1" title="History">
                            <i class="ri-history-line"></i>
                        </a>';

                    if (auth()->user()->can('edit', Candidate::class)) {
                        $buttons .= '<a href="'.
                            route('admin.interview-schedules.edit', $row->id).
                            '" class="text-info fs-4 me-1" title="Edit">
                                <i class="bx bxs-edit"></i>
                            </a>';
                    }

                    if (auth()->user()->can('delete', Candidate::class)) {
                        $buttons .= '<button
                            type="button"
                            data-route="'.
                            route('admin.interview-schedules.delete', $row->id).
                            '"
                            class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record"
                            title="Delete">
                                <i class="bx bxs-trash"></i>
                        </button>';
                    }

                    return $buttons;
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view(
            'backend.interview-schedules.index',
            $this->formData()
        );
    }

    public function create(Request $request)
    {
        $this->authorize('create', Candidate::class);

        $data = $this->formData();
        $candidate = $request->filled('candidate_id')
            ? Candidate::with(['client', 'jobRole', 'interviewLevel'])->find($request->candidate_id)
            : null;
        $interviewMode = InterviewMode::all();

        return view('backend.interview-schedules.create', array_merge($data, [
            'selectedCandidate' => $candidate,
            'interviewMode' => $interviewMode,
        ]));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Candidate::class);

        InterviewSchedule::create($this->validatedData($request));

        return redirect()->route('admin.interview-schedules.index')->with('success', 'Interview schedule created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Candidate::class);

        $interviewSchedule = InterviewSchedule::with('interviewMode')->findOrFail($id);

        return view('backend.interview-schedules.edit', array_merge(
            [
                'interviewSchedule' => $interviewSchedule,
                'selectedModeId' => $interviewSchedule->interview_mode_id,
            ],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Candidate::class);

        $schedule = InterviewSchedule::findOrFail($id);
        $schedule->update($this->validatedData($request));
        $candidate = Candidate::findOrFail($schedule->candidate_id);
        if( $candidate->level_of_interview_id != $request->level_of_interview_id) {
            $candidate->update(['level_of_interview_id' => $request->level_of_interview_id]);
        }
        return redirect()->route('admin.interview-schedules.index')->with('success', 'Interview schedule updated successfully.');
    }

    public function show($candidateId)
    {
        $this->authorize('read', Candidate::class);

        $candidate = Candidate::with(['recruiter', 'client', 'jobRole', 'interviewLevel'])->findOrFail($candidateId);
        $schedules = InterviewSchedule::with(['client', 'jobRole', 'interviewLevel'])
            ->where('candidate_id', $candidate->id)
            ->latest('schedule_date')
            ->get();

        return view('backend.interview-schedules.show', compact('candidate', 'schedules'));
    }

    public function destroy($id)
    {
        $this->authorize('delete', Candidate::class);

        InterviewSchedule::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Interview schedule deleted successfully.']);
    }

    private function scheduleQuery(Request $request)
    {
        return InterviewSchedule::with(['candidate.recruiter', 'candidate.client', 'candidate.jobRole', 'client', 'jobRole', 'interviewLevel'])
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('schedule_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('schedule_date', '<=', $request->to_date))
            ->when($request->filled('candidate_id'), fn ($query) => $query->where('candidate_id', $request->candidate_id))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('job_role_id'), fn ($query) => $query->where('job_role_id', $request->job_role_id))
            ->when($request->filled('level_of_interview_id'), function ($query) use ($request) {
                $levelIds = array_filter((array) $request->input('level_of_interview_id'));

                return $query->whereIn('level_of_interview_id', $levelIds);
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('recruiter_id'), fn ($query) => $query->whereHas('candidate', fn ($candidateQuery) => $candidateQuery->where('recruiter_id', $request->recruiter_id)));
    }

    private function formData(): array
    {
        return [
            'candidates' => Candidate::orderBy('candidate_name')->get(),
            'clients' => Client::orderBy('client')->get(),
            'jobRoles' => JobRole::orderBy('job_role')->get(),
            'interviewMode' => InterviewMode::orderBy('interview_mode')->get(),
            'interviewLevels' => InterviewLevel::orderBy('sort_order')->get(),
            'recruiters' => Recruiter::orderBy('recruiter_name')->get(),
            'statuses' => InterviewSchedule::STATUSES,
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'client_id' => 'nullable|exists:clients,id',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'interview_mode_id' => 'required|exists:interview_modes,id',
            'level_of_interview_id' => 'required|exists:level_of_interviews,id',
            'schedule_date' => 'required|date',
            'status' => ['required', Rule::in(array_keys(InterviewSchedule::STATUSES))],
            'notes' => 'nullable|string',
        ]);
    }

    private function statusBadge(string $status): string
    {
        $classes = [
            'scheduled' => 'bg-primary-subtle text-primary',
            'completed' => 'bg-info-subtle text-info',
            'selected' => 'bg-success-subtle text-success',
            'rejected' => 'bg-danger-subtle text-danger',
            'cancelled' => 'bg-warning-subtle text-warning',
        ];

        $label = InterviewSchedule::STATUSES[$status] ?? ucfirst($status);
        $class = $classes[$status] ?? 'bg-secondary-subtle text-secondary';

        return '<span class="badge '.$class.'">'.$label.'</span>';
    }
}
