<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientRequirement;
use App\Models\General;
use App\Models\InterviewLevel;
use App\Models\InterviewSchedule;
use App\Models\Recruiter;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('dashboard', General::class);

        $toDateRules = ['nullable', 'date_format:Y-m-d'];
        if ($request->filled('dashboard_from_date')) {
            $toDateRules[] = 'after_or_equal:dashboard_from_date';
        }
        $dateValidator = validator($request->only(['dashboard_from_date', 'dashboard_to_date']), [
            'dashboard_from_date' => ['nullable', 'date_format:Y-m-d'],
            'dashboard_to_date' => $toDateRules,
        ], [], [
            'dashboard_from_date' => 'from date',
            'dashboard_to_date' => 'to date',
        ]);
        $datesAreValid = $dateValidator->passes();
        $selectedFromDate = $datesAreValid && $request->filled('dashboard_from_date')
            ? $request->string('dashboard_from_date')->toString()
            : null;
        $selectedToDate = $datesAreValid && $request->filled('dashboard_to_date')
            ? $request->string('dashboard_to_date')->toString()
            : null;
        $fromDateError = $dateValidator->errors()->first('dashboard_from_date');
        $toDateError = $dateValidator->errors()->first('dashboard_to_date');
        $dateFrom = $selectedFromDate
            ? CarbonImmutable::createFromFormat('!Y-m-d', $selectedFromDate)->startOfDay()
            : null;
        $dateTo = $selectedToDate
            ? CarbonImmutable::createFromFormat('!Y-m-d', $selectedToDate)->endOfDay()
            : null;

        $user = Auth::user()->loadMissing('role.permissions');
        $roleId = (int) $user->role_id;
        $isSuperAdmin = $roleId === 1;
        $isDeliveryLead = $roleId === 2;
        $isRecruiter = $roleId === 3;
        $isPersonalDashboard = $isRecruiter;
        $linkedRecruiterId = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [strtolower($user->email)])->value('id')
            : null;

        $availableRecruiters = ($isSuperAdmin || $isDeliveryLead)
            ? Recruiter::where('status', true)->orderBy('recruiter_name')->get()
            : collect();
        $requestedRecruiterId = $request->filled('recruiter_id') ? (int) $request->recruiter_id : null;
        $selectedRecruiterId = $isRecruiter
            ? ($linkedRecruiterId ?? 0)
            : ($availableRecruiters->contains('id', $requestedRecruiterId) ? $requestedRecruiterId : null);
        $selectedClientId = $isSuperAdmin && $request->filled('client_id')
            ? (int) $request->client_id
            : null;

        $requirements = ClientRequirement::query()
            ->when($selectedRecruiterId !== null, fn($query) => $query->where('project_owner', $selectedRecruiterId))
            ->when($selectedClientId, fn($query) => $query->where('client_id', $selectedClientId))
            ->when($dateFrom, fn($query) => $query->where('client_requirements.created_at', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->where('client_requirements.created_at', '<=', $dateTo));
        $candidates = Candidate::query()
            ->when($selectedRecruiterId !== null, fn($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($selectedClientId, fn($query) => $query->where('client_id', $selectedClientId))
            ->when($dateFrom, fn($query) => $query->where('candidates.created_at', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->where('candidates.created_at', '<=', $dateTo));
        $interviews = InterviewSchedule::query()
            ->when($selectedRecruiterId !== null, fn($query) => $query->whereHas(
                'candidate',
                fn($candidate) => $candidate->where('recruiter_id', $selectedRecruiterId)
            ))
            ->when($selectedClientId, fn($query) => $query->where(function ($clientQuery) use ($selectedClientId) {
                $clientQuery->where('client_id', $selectedClientId)
                    ->orWhereHas('candidate', fn($candidate) => $candidate->where('client_id', $selectedClientId));
            }))
            ->when($dateFrom, fn($query) => $query->where('interview_schedules.schedule_date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->where('interview_schedules.schedule_date', '<=', $dateTo));

        $candidateLevelScope = function ($query) use ($selectedRecruiterId, $selectedClientId, $dateFrom, $dateTo) {
            $query
                ->when($selectedRecruiterId !== null, fn($candidate) => $candidate->where('recruiter_id', $selectedRecruiterId))
                ->when($selectedClientId, fn($candidate) => $candidate->where('client_id', $selectedClientId))
                ->when($dateFrom, fn($candidate) => $candidate->where('candidates.created_at', '>=', $dateFrom))
                ->when($dateTo, fn($candidate) => $candidate->where('candidates.created_at', '<=', $dateTo));
        };
        $candidateLevels = InterviewLevel::query()
            ->whereHas('candidates', $candidateLevelScope)
            ->withCount(['candidates' => $candidateLevelScope])
            ->orderBy('sort_order')
            ->get();

        $levelGroups = [
            'Sourcing Stage' => [
                'CV Shared to DL',
                'Internal Duplicate',
                'Profile Feedback Pending',
                'Client Duplicate',
                'Screen Select',
                'Screen Reject',
            ],
            'Interview Stage' => [
                'L1 Scheduled',
                'L1 Select',
                'L1 Reject',
                'L1 Re-Schedule',
                'L2 Scheduled',
                'L2 Select',
                'L2 Reject',
                'L2 Re-Schedule',
                'L3 Scheduled',
                'L3 Select',
                'L3 Reject',
                'L3 Re-Schedule',
                'L4 Scheduled',
                'L4 Select',
                'L4 Reject',
                'L4 Re-Schedule',
            ],
            'Offer Stage' => [
                'HR Discussion Pending',
                'HR Select',
                'HR Reject',
                'Offer Released',
                'Offer Accepted',
                'Offer Declined',
            ],
            // Rendered as a five-month onboarding-date bar chart in the
            // interview pipeline, between Offer and Onboarding stages.
            'Monthly Joining Details' => [],
            'Onboarding Stage' => [
                'Onboarded with Client',
                'Joiner Declined',
                'Position Hold',
                'Candidate Not Interested',
            ],
        ];

        $groupedLevels = collect($levelGroups)->map(function ($levels, $heading) use ($candidateLevels) {
            return [
                'title' => $heading,
                'levels' => collect($levels)->map(function ($levelName) use ($candidateLevels) {
                    return $candidateLevels->firstWhere('level', $levelName)
                        ?? (object)[
                            'level' => $levelName,
                            'candidates_count' => 0,
                        ];
                }),
            ];
        });

        $maxLevel = max(1, $candidateLevels->max('candidates_count'));

        $monthly = (clone $candidates)
            ->where('candidates.created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(candidates.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');
        $months = collect(range(5, 0))->map(fn($offset) => now()->subMonths($offset));
        $joiningMonths = collect(range(4, 0))->map(fn ($offset) => now()->subMonths($offset));
        $monthlyJoinings = (clone $candidates)
            ->whereNotNull('onboarding_date')
            ->where('onboarding_date', '>=', now()->subMonths(4)->startOfMonth())
            ->selectRaw("DATE_FORMAT(onboarding_date, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');
        $monthlyJoiningDetails = $joiningMonths->map(fn ($month) => [
            'label' => $month->format('M Y'),
            'total' => (int) ($monthlyJoinings[$month->format('Y-m')] ?? 0),
        ]);

        $targetMultiplier = $selectedRecruiterId
            ? 1
            : max(1, $availableRecruiters->count());
        $monthlyKpis = $this->monthlyTargetAnalytics($candidates, $interviews, $targetMultiplier, $dateFrom, $dateTo);
        $deliveryLeadAnalytics = $this->monthlyTargetAnalytics(
            Candidate::query(),
            InterviewSchedule::query(),
            max(1, $availableRecruiters->count()),
            $dateFrom,
            $dateTo
        );
        $recruiterPerformance = $availableRecruiters->mapWithKeys(function (Recruiter $recruiter) use ($dateFrom, $dateTo) {
            $candidateQuery = Candidate::where('recruiter_id', $recruiter->id);
            $interviewQuery = InterviewSchedule::whereHas(
                'candidate',
                fn($query) => $query->where('recruiter_id', $recruiter->id)
            );

            return [$recruiter->id => $this->monthlyTargetAnalytics(
                $candidateQuery,
                $interviewQuery,
                1,
                $dateFrom,
                $dateTo
            )['overallPercentage']];
        });

        return view('backend.index', [
            'scopeLabel' => $isRecruiter
                ? 'My recruitment pipeline'
                : ($isDeliveryLead ? 'My delivery lead recruitment pipeline' : 'Management recruitment overview'),
            'isRecruiterDashboard' => $isPersonalDashboard,
            'showClientFilter' => $isSuperAdmin,
            'recruiterLinked' => !$isRecruiter || (bool) $linkedRecruiterId,
            'recruiters' => $availableRecruiters,
            'clients' => $isSuperAdmin ? Client::where('status', true)->orderBy('client')->get() : collect(),
            'selectedRecruiterId' => $selectedRecruiterId,
            'selectedClientId' => $selectedClientId,
            'selectedFromDate' => $selectedFromDate,
            'selectedToDate' => $selectedToDate,
            'fromDateError' => $fromDateError,
            'toDateError' => $toDateError,
            'monthlyTargetAnalytics' => $monthlyKpis,
            'deliveryLeadAnalytics' => $deliveryLeadAnalytics,
            'recruiterPerformance' => $recruiterPerformance,
            'activeRequirements' => (clone $requirements)->where('status', true)->count(),//sum('number_of_position')
            'inActiveRequirements' => (clone $requirements)->where('status', false)->count(),//sum('number_of_position')

            'myApplicants' => (clone $candidates)->count(),
            'scheduledInterviews' => (clone $interviews)->whereIn('status', ['scheduled', 'completed', 'selected'])->get(),
            'yetToOffer' => (clone $interviews)->where('level_of_interview_id', 14)->count(),
            'offered' => (clone $interviews)->where('level_of_interview_id', 30)->count(),
            'hrSelected' => (clone $interviews)->where('level_of_interview_id', 15)->count(),
            'onboarded' => (clone $interviews)->where('level_of_interview_id', 20)->count(),
            'monthlyJoiningTotal' => (clone $candidates)
                ->whereNotNull('onboarding_date')
                ->whereBetween('onboarding_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'monthlyJoiningDetails' => $monthlyJoiningDetails,
            'joiningChartMonths' => $monthlyJoiningDetails->pluck('label')->values(),
            'joiningChartTotals' => $monthlyJoiningDetails->pluck('total')->values(),

            'revenue' => (clone $requirements)->sum('revenue_amount'),
            'candidateLevels' => $candidateLevels,
            'chartMonths' => $months->map->format('M')->values(),
            'chartApplicants' => $months->map(fn($month) => (int) ($monthly[$month->format('Y-m')] ?? 0))->values(),
            'upcomingInterviews' => (clone $interviews)->with(['candidate', 'client', 'interviewLevel'])
                ->where('schedule_date', '>=', now())->orderBy('schedule_date')->limit(6)->get(),
            'groupedLevels' => $groupedLevels,
            'maxLevel' => $maxLevel,
        ]);
    }

    private function monthlyTargetAnalytics(
        $candidates,
        $interviews,
        int $targetMultiplier,
        ?CarbonInterface $dateFrom = null,
        ?CarbonInterface $dateTo = null
    ): array {
        $periodStart = $dateFrom ?? ($dateTo ? null : now()->startOfMonth());
        $periodEnd = $dateTo ?? ($dateFrom ? null : now()->endOfMonth());

        $periodCandidates = (clone $candidates)
            ->when($periodStart, fn($query) => $query->where('candidates.created_at', '>=', $periodStart))
            ->when($periodEnd, fn($query) => $query->where('candidates.created_at', '<=', $periodEnd));
        $periodInterviews = (clone $interviews)
            ->when($periodStart, fn($query) => $query->where('interview_schedules.schedule_date', '>=', $periodStart))
            ->when($periodEnd, fn($query) => $query->where('interview_schedules.schedule_date', '<=', $periodEnd))
            ->where('status', '!=', 'cancelled');

        // Fixed IDs from the Level of Interviews master.
        $shortlistedLevelIds = [3, 7, 8, 9, 31, 11, 12, 13, 32, 23, 25, 24, 33, 27, 28, 29, 34, 14, 15, 16, 35, 22, 20, 21, 36, 5];
        $interviewLevelIds = [7, 8, 9, 31, 11, 12, 13, 32, 23, 24, 25, 33, 27, 28, 29, 34, 15, 35, 30, 22, 20, 21];
        $hrSelectId = [15, 35, 30, 22, 20, 21];
        $offerReleasedIds = [35, 30, 22, 20, 21];
        $offerAcceptedId = [30, 20];
        $onboardedId = 20;

        $definitions = [
            [
                'label' => 'CV Submission',
                'target' => 200,
                'unit' => 'CVs',
                'icon' => 'ri-file-search-line',
                'completed' => (clone $periodCandidates)->count()
            ],
            [
                'label' => 'Candidate Shortlisting',
                'target' => 150,
                'unit' => 'CVs',
                'icon' => 'ri-user-search-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $shortlistedLevelIds)->count()
            ],
            [
                'label' => 'Interviews',
                'target' => 70,
                'unit' => 'rounds',
                'icon' => 'ri-calendar-check-line',
                'completed' => (clone $periodInterviews)->whereIn('level_of_interview_id', $interviewLevelIds)->count()
            ],
            [
                'label' => 'HR Select',
                'target' => 25,
                'unit' => 'screenings',
                'icon' => 'ri-survey-line',
                'completed' => (clone $periodInterviews)->where('level_of_interview_id', $hrSelectId)
                    ->distinct()->count('candidate_id')
            ],
            [
                'label' => 'Offers Released',
                'target' => 15,
                'unit' => 'offers',
                'icon' => 'ri-draft-line',
                'completed' => (clone $periodInterviews)->whereIn('level_of_interview_id', $offerReleasedIds)
                    ->distinct()->count('candidate_id')
            ],
            [
                'label' => 'Offer Acceptance',
                'target' => 12,
                'unit' => 'acceptances',
                'icon' => 'ri-user-follow-line',
                'completed' => (clone $periodInterviews)->where('level_of_interview_id', $offerAcceptedId)
                    ->distinct()->count('candidate_id')
            ],
            [
                'label' => 'Onboarding',
                'target' => 10,
                'unit' => 'joiners',
                'icon' => 'ri-team-line',
                'completed' => (clone $periodInterviews)->where('level_of_interview_id', $onboardedId)
                    ->distinct()->count('candidate_id')
            ],
        ];

        $kpis = collect($definitions)->map(function (array $kpi) use ($targetMultiplier) {
            $kpi['target'] *= $targetMultiplier;
            $kpi['percentage'] = min(100, (int) round(($kpi['completed'] / max(1, $kpi['target'])) * 100));
            return $kpi;
        });

        return [
            'kpis' => $kpis,
            'overallPercentage' => (int) round($kpis->avg('percentage')),
            'completedProcesses' => $kpis->where('percentage', '>=', 100)->count(),
            'offers' => $kpis->firstWhere('label', 'Offers Released'),
            'joining' => $kpis->firstWhere('label', 'Onboarding'),
            'targetMultiplier' => $targetMultiplier,
        ];
    }

    public function profile()
    {
        $this->authorize('profileRead', General::class);
        return view('backend.common.profile');
    }

    public function settingStore(Request $request) {}

    public function uploadProfile(Request $request)
    {
        $this->authorize('profileEdit', General::class);
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');

            $user = User::where('id', Auth::user()->id)->first();
            if ($user) {
                if ($user->profile_picture) {
                    $oldImagePath = public_path($user->profile_picture);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $uploadPath = public_path('uploads/profile_images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/profile_images/' . $fileName;

                $file->move($uploadPath, $fileName);

                $user->profile_picture = $filePath;
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Profile uploaded successfully',
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'No image was uploaded',
        ]);
    }

    public function lock()
    {
        session(['locked' => true]);
        $sliders = [];
        return view('errors.lock', compact('sliders'));
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            session()->forget('locked');
            return redirect()->intended(route('admin.dashboard'))->with('info', 'Welcome back!');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }
}
