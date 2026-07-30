<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\InterviewLevel;
use App\Models\InterviewMode;
use App\Models\InterviewSchedule;
use App\Models\JobRole;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InterviewScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Candidate::class);

        if ($request->ajax()) {
            return DataTables::of(
                $this->scheduleQuery($request)->latest('schedule_date')
            )
                ->filter(function ($query) use ($request) {
                    $search = trim((string) $request->input('search.value'));

                    if ($search !== '') {
                        $query->where(function ($q) use ($search) {

                            // Candidate name, mobile, recruiter, client and job role
                            $q->whereHas('candidate', function ($candidateQuery) use ($search) {
                                $candidateQuery
                                    ->where('candidate_name', 'like', "%{$search}%")
                                    ->orWhere('mobile_no', 'like', "%{$search}%")
                                    ->orWhereHas('recruiter', function ($recruiterQuery) use ($search) {
                                        $recruiterQuery->where(
                                            'recruiter_name',
                                            'like',
                                            "%{$search}%"
                                        );
                                    })
                                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                                        $clientQuery->where(
                                            'client',
                                            'like',
                                            "%{$search}%"
                                        );
                                    })
                                    ->orWhereHas('jobRole', function ($jobRoleQuery) use ($search) {
                                        $jobRoleQuery->where(
                                            'job_role',
                                            'like',
                                            "%{$search}%"
                                        );
                                    });
                            })

                                // Direct client relation
                                ->orWhereHas('client', function ($clientQuery) use ($search) {
                                    $clientQuery->where(
                                        'client',
                                        'like',
                                        "%{$search}%"
                                    );
                                })

                                // Direct job role relation
                                ->orWhereHas('jobRole', function ($jobRoleQuery) use ($search) {
                                    $jobRoleQuery->where(
                                        'job_role',
                                        'like',
                                        "%{$search}%"
                                    );
                                })

                                // Interview level
                                ->orWhereHas('interviewLevel', function ($levelQuery) use ($search) {
                                    $levelQuery->where(
                                        'level',
                                        'like',
                                        "%{$search}%"
                                    );
                                })

                                // Schedule date
                                ->orWhereRaw(
                                    "DATE_FORMAT(schedule_date, '%d-%m-%Y %H:%i') LIKE ?",
                                    ["%{$search}%"]
                                )

                                // Notes
                                ->orWhere('notes', 'like', "%{$search}%");

                            // Status search
                            $searchStatus = strtolower($search);

                            foreach (array_keys(InterviewSchedule::STATUSES) as $statusName) {
                                if (str_contains($statusName, $searchStatus)) {
                                    $q->orWhere('status', $statusName);
                                }
                            }
                        });
                    }
                })
                ->addIndexColumn()

                ->addColumn(
                    'candidate_name',
                    fn($row) => $row->candidate?->candidate_name ?? '-'
                )

                ->addColumn(
                    'candidate_mobile',
                    fn($row) => $row->candidate?->mobile_no ?? '-'
                )

                ->addColumn(
                    'recruiter_name',
                    fn($row) => $row->candidate?->recruiter?->recruiter_name ?? '-'
                )

                ->addColumn(
                    'client_name',
                    fn($row) => $row->client?->client
                        ?? $row->candidate?->client?->client
                        ?? '-'
                )

                ->addColumn(
                    'job_role_name',
                    fn($row) => $row->jobRole?->job_role
                        ?? $row->candidate?->jobRole?->job_role
                        ?? '-'
                )

                ->addColumn(
                    'interview_level',
                    fn($row) => $row->interviewLevel?->level ?? '-'
                )

                ->editColumn(
                    'schedule_date',
                    fn($row) => $row->schedule_date?->format('d-m-Y H:i') ?? '-'
                )

                ->editColumn(
                    'status',
                    fn($row) => $this->statusBadge($row->status)
                )

                ->editColumn(
                    'notes',
                    fn($row) => $row->notes ?: '-'
                )

                ->editColumn(
                    'created_at',
                    fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-'
                )

                ->addColumn('action', function ($row) {
                    $buttons = '<a href="' .
                        route(
                            'admin.interview-schedules.show',
                            $row->candidate_id
                        ) .
                        '" class="text-primary fs-4 me-1" title="History">
                            <i class="ri-history-line"></i>
                        </a>';

                    if (auth()->user()->can('edit', Candidate::class)) {
                        $buttons .= '<a href="' .
                            route(
                                'admin.interview-schedules.edit',
                                $row->id
                            ) .
                            '" class="text-info fs-4 me-1" title="Edit">
                                <i class="bx bxs-edit"></i>
                            </a>';
                    }

                    if (auth()->user()->can('delete', Candidate::class)) {
                        $buttons .= '<button
                            type="button"
                            data-route="' .
                            route(
                                'admin.interview-schedules.delete',
                                $row->id
                            ) .
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

        $data = $this->validatedData($request);

        $level = InterviewLevel::find($request->level_of_interview_id);
        if ($level && $level->level === 'Offer Released' && !$request->filled('onboarding_date')) {
            return back()
                ->withErrors([
                    'onboarding_date' => 'Onboarding Date is mandatory when the interview level is Offer Released.'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($data) {
            InterviewSchedule::create($data);
            Candidate::whereKey($data['candidate_id'])->update([
                'level_of_interview_id' => $data['level_of_interview_id'],
                'onboarding_date'       => $data['onboarding_date'],
            ]);
        });

        return redirect()->route('admin.interview-schedules.index')->with('success', 'Interview schedule created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Candidate::class);

        $interviewSchedule = InterviewSchedule::with('interviewMode')->findOrFail($id);
        $onboarding_candidate = Candidate::findOrFail($interviewSchedule->candidate_id);

        return view('backend.interview-schedules.edit', array_merge(
            [
                'interviewSchedule' => $interviewSchedule,
                'onboarding_candidate' => $onboarding_candidate,
                'selectedModeId' => $interviewSchedule->interview_mode_id,
            ],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Candidate::class);

        $level = InterviewLevel::find($request->level_of_interview_id);
        if ($level && $level->level === 'Offer Released' && !$request->filled('onboarding_date')) {
            return back()
                ->withErrors([
                    'onboarding_date' => 'Onboarding Date is mandatory when the interview level is Offer Released.'
                ])
                ->withInput();
        }
        $schedule = InterviewSchedule::findOrFail($id);
        $schedule->update($this->validatedData($request));
        $candidate = Candidate::findOrFail($schedule->candidate_id);
        $candidateData = [];
        if ($candidate->level_of_interview_id != $request->level_of_interview_id) {
            $candidate->update(['level_of_interview_id' => $request->level_of_interview_id]);
        }
        if ($request->onboarding_date) {
            $candidateData['onboarding_date'] = $request->onboarding_date;
        }
        if (!empty($candidateData)) {
            $candidate->update($candidateData);
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
        $user = auth()->user();
        $isRecruiter = (int) $user->role_id === 3;
        $linkedRecruiterId = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->value('id')
            : null;

        return InterviewSchedule::with(['candidate.recruiter', 'candidate.client', 'candidate.jobRole', 'client', 'jobRole', 'interviewLevel'])
            ->when($isRecruiter, fn ($query) => $query->whereHas(
                'candidate',
                fn ($candidateQuery) => $candidateQuery->where('recruiter_id', $linkedRecruiterId ?? 0)
            ))
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('schedule_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('schedule_date', '<=', $request->to_date))
            ->when($request->filled('candidate_id'), fn($query) => $query->where('candidate_id', $request->candidate_id))
            ->when($request->filled('client_id'), fn($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('job_role_id'), fn($query) => $query->where('job_role_id', $request->job_role_id))
            ->when($request->filled('level_of_interview_id'), function ($query) use ($request) {
                $levelIds = array_filter((array) $request->input('level_of_interview_id'));

                return $query->whereIn('level_of_interview_id', $levelIds);
            })
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when(! $isRecruiter && $request->filled('recruiter_id'), fn($query) => $query->whereHas('candidate', fn($candidateQuery) => $candidateQuery->where('recruiter_id', $request->recruiter_id)));
    }

    private function formData(): array
    {
        $isRecruiter = (int) auth()->user()->role_id === 3;
        $linkedRecruiter = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower(auth()->user()->email)])->first()
            : null;

        return [
            'candidates' => Candidate::query()
                ->when($isRecruiter, fn ($query) => $query->where('recruiter_id', $linkedRecruiter?->id ?? 0))
                ->orderBy('candidate_name')
                ->get(),
            'isRecruiterScheduleList' => $isRecruiter,
            'linkedRecruiter' => $linkedRecruiter,
            'clients' => Client::orderBy('client')->get(),
            'jobRoles' => JobRole::orderBy('job_role')->get(),
            'clientJobRoleMap' => ClientJobRole::where('status', true)
                ->get(['client_id', 'job_role_id'])
                ->groupBy('client_id')
                ->map(fn ($rows) => $rows->pluck('job_role_id')->unique()->values()),
            'interviewMode' => InterviewMode::orderBy('interview_mode')->get(),
            'interviewLevels' => InterviewLevel::orderBy('sort_order')->get(),
            'recruiters' => Recruiter::orderBy('recruiter_name')->get(),
            'statuses' => InterviewSchedule::STATUSES,
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'client_id' => 'nullable|exists:clients,id',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'interview_mode_id' => 'nullable|exists:interview_modes,id',
            'level_of_interview_id' => 'required|exists:level_of_interviews,id',
            'schedule_date' => 'nullable|date',
            'status' => ['required', Rule::in(array_keys(InterviewSchedule::STATUSES))],
            'notes' => 'nullable|string',
            'onboarding_date' => 'nullable|date',
        ]);

        if ($data['client_id'] && $data['job_role_id'] && ! ClientJobRole::where('client_id', $data['client_id'])
            ->where('job_role_id', $data['job_role_id'])->where('status', true)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'job_role_id' => 'The selected job role is not assigned to this client.',
            ]);
        }

        return $data;
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

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}
