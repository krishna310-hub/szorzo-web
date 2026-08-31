<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientRequirement;
use App\Models\ContractReport;
use App\Models\Employee;
use App\Models\General;
use App\Models\InterviewLevel;
use App\Models\InterviewSchedule;
use App\Models\ProfileSourced;
use App\Models\Recruiter;
use App\Models\Revenue;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('dashboard', General::class);

        $today = Carbon::today();
        $user = Auth::user();
        $employee = Employee::query()
            ->where('status', true)
            ->where(function ($query) use ($user) {
                $query->whereRaw('LOWER(official_mail) = ?', [mb_strtolower($user->email)])
                    ->orWhereRaw('LOWER(personal_mail) = ?', [mb_strtolower($user->email)]);
            })->first();

        $birthdayEmployee = null;
        if ($employee && $employee->dob) {
            $dob = Carbon::parse($employee->dob);
            if (
                $dob->month === $today->month &&
                $dob->day === $today->day
            ) {
                $birthdayEmployee = $employee;
            }
        }

        $birthdayEmployees = collect();
        if ($user->role_id == 1) {
            $birthdayEmployees = Employee::query()
                ->where('status', true)
                ->whereNotNull('dob')
                ->whereMonth('dob', $today->month)
                ->whereDay('dob', $today->day)
                ->get();
        }

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
        $accessLevel = str_replace('_', '-', strtolower((string) $user->role?->access_level));
        $isSuperAdmin = $accessLevel === 'super-admin';
        $isDeliveryLead = in_array($accessLevel, ['delivery-lead', 'recruiter-dl'], true);
        $isRecruiter = $accessLevel === 'recruiter';
        $isPersonalDashboard = $isRecruiter;
        $linkedRecruiter = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->first()
            : null;
        $linkedRecruiterId = $linkedRecruiter?->id;
        $deliveryLeadRecruiterIds = $isDeliveryLead
            ? Recruiter::visibleTo($user)->pluck('id')
            : collect();

        $availableRecruiters = ($isSuperAdmin || $isDeliveryLead)
            ? Recruiter::where('status', true)
                ->when($isDeliveryLead, fn ($query) => $query->visibleTo($user))
                ->orderBy('recruiter_name')->get()
            : collect();
        $availableClients = Client::where('status', true)->orderBy('client')->get();
        $requestedRecruiterId = $request->filled('recruiter_id') ? (int) $request->recruiter_id : null;
        $selectedRecruiterId = $isRecruiter
            ? ($linkedRecruiterId ?? 0)
            : ($availableRecruiters->contains('id', $requestedRecruiterId) ? $requestedRecruiterId : null);
        $requestedClientId = $request->filled('client_id') ? (int) $request->client_id : null;
        $selectedClientId = $availableClients->contains('id', $requestedClientId)
            ? $requestedClientId
            : null;

        $requirements = ClientRequirement::visibleTo($user)
            ->when($isDeliveryLead, function ($query) use ($deliveryLeadRecruiterIds) {
                if ($deliveryLeadRecruiterIds->isEmpty()) {
                    return $query->whereRaw('1 = 0');
                }
                $query->where(function ($teamQuery) use ($deliveryLeadRecruiterIds) {
                    foreach ($deliveryLeadRecruiterIds as $recruiterId) {
                        $teamQuery->orWhereJsonContains('project_owner_ids', $recruiterId)
                            ->orWhere(function ($legacyQuery) use ($recruiterId) {
                                $legacyQuery->whereNull('project_owner_ids')->where('project_owner', $recruiterId);
                            });
                    }
                });
            })
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where(function ($ownerQuery) use ($selectedRecruiterId) {
                $ownerQuery->whereJsonContains('project_owner_ids', $selectedRecruiterId)
                    ->orWhere(function ($legacyQuery) use ($selectedRecruiterId) {
                        $legacyQuery->whereNull('project_owner_ids')
                            ->where('project_owner', $selectedRecruiterId);
                    });
            }))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId))
            ->when($dateFrom, fn ($query) => $query->where('client_requirements.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('client_requirements.created_at', '<=', $dateTo));
        $candidateScope = Candidate::visibleTo($user)
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId));
        $candidates = (clone $candidateScope)
            ->when($dateFrom, fn ($query) => $query->where('candidates.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('candidates.created_at', '<=', $dateTo));
        $onboardingCandidates = (clone $candidateScope)
            ->whereNotNull('onboarding_date')
            ->when($dateFrom, fn ($query) => $query->whereDate('onboarding_date', '>=', $dateFrom->toDateString()))
            ->when($dateTo, fn ($query) => $query->whereDate('onboarding_date', '<=', $dateTo->toDateString()));
        $myProfiles = ProfileSourced::visibleTo($user)
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($dateFrom, fn ($query) => $query->where('profile_sourced.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('profile_sourced.created_at', '<=', $dateTo));
        $chartCandidates = Candidate::visibleTo($user)
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId));
        $interviews = InterviewSchedule::query()
            ->when($isDeliveryLead, fn ($query) => $query->whereHas(
                'candidate', fn ($candidate) => $candidate->whereIn('recruiter_id', $deliveryLeadRecruiterIds)
            ))
            ->when($selectedRecruiterId !== null, fn ($query) => $query->whereHas(
                'candidate',
                fn ($candidate) => $candidate->where('recruiter_id', $selectedRecruiterId)
            ))
            ->when($selectedClientId, fn ($query) => $query->where(function ($clientQuery) use ($selectedClientId) {
                $clientQuery->where('client_id', $selectedClientId)
                    ->orWhereHas('candidate', fn ($candidate) => $candidate->where('client_id', $selectedClientId));
            }))
            ->when($dateFrom, fn ($query) => $query->where('interview_schedules.schedule_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('interview_schedules.schedule_date', '<=', $dateTo));

        $chartYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $yearStart = CarbonImmutable::create($chartYear, 4, 1)->startOfDay();
        $yearEnd = $yearStart->addYear()->subDay()->endOfDay();

        $candidateLevelScope = function ($query) use ($isDeliveryLead, $deliveryLeadRecruiterIds, $selectedRecruiterId, $selectedClientId, $yearStart, $yearEnd) {
            $query
                ->when($isDeliveryLead, fn ($candidate) => $candidate->whereIn('recruiter_id', $deliveryLeadRecruiterIds))
                ->when($selectedRecruiterId !== null, fn ($candidate) => $candidate->where('recruiter_id', $selectedRecruiterId))
                ->when($selectedClientId, fn ($candidate) => $candidate->where('client_id', $selectedClientId))
                ->whereBetween('candidates.created_at', [$yearStart, $yearEnd]);
        };
        $candidateLevels = InterviewLevel::query()
            ->withCount(['candidates' => $candidateLevelScope])
            ->orderBy('sort_order')
            ->get();

        // Onboarding outcomes must follow the actual onboarding date, not the
        // date on which the candidate record was created.
        $onboardingLevelCounts = (clone $candidateScope)
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->where(function ($onboarded) use ($yearStart, $yearEnd) {
                    $onboarded->where('level_of_interview_id', 20)
                        ->whereBetween('onboarding_date', [$yearStart, $yearEnd]);
                })->orWhere(function ($declined) use ($yearStart, $yearEnd) {
                    $declined->where('level_of_interview_id', 21)
                        ->whereBetween('candidates.updated_at', [$yearStart, $yearEnd]);
                });
            })
            ->selectRaw('level_of_interview_id, COUNT(*) as total')
            ->groupBy('level_of_interview_id')
            ->pluck('total', 'level_of_interview_id');
        $candidateLevels->each(function ($level) use ($onboardingLevelCounts) {
            if (in_array((int) $level->id, [20, 21], true)) {
                $level->candidates_count = (int) ($onboardingLevelCounts[$level->id] ?? 0);
            }
        });

        $levelGroups = [
            'Sourcing Stage' => [
                'CV Shared to DL',
                'Internal Duplicate',
                'Profile Feedback Pending',
                'Client Duplicate',
                'Screen Select',
                'Screen Reject',
                'Position Hold',
                'Position Closed',
                'Candidate Not Interested',
                'Candidate Not Responding',
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
            // Rendered as a seven-month onboarding-date bar chart in the
            // interview pipeline, between Offer and Onboarding stages.
            'Monthly Joining Details' => [],
            'Onboarding Stage' => [
                'Onboarded with Client',
                'Joiner Declined',
            ],
            'Monthly Onboarding Details' => [],
        ];

        $groupedLevels = collect($levelGroups)->map(function ($levels, $heading) use ($candidateLevels) {
            return [
                'title' => $heading,
                'levels' => collect($levels)->map(function ($levelName) use ($candidateLevels) {
                    return $candidateLevels->firstWhere('level', $levelName)
                        ?? (object) [
                            'level' => $levelName,
                            'candidates_count' => 0,
                        ];
                }),
            ];
        });

        $maxLevel = max(1, $candidateLevels->max('candidates_count'));

        $months = collect(range(0, 11))->map(fn ($offset) => $yearStart->addMonths($offset));

        $monthly = (clone $chartCandidates)
            ->whereBetween('candidates.created_at', [$yearStart, $yearEnd])
            ->selectRaw("DATE_FORMAT(candidates.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');
        $monthlyProfilesSourced = ProfileSourced::visibleTo($user)
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->whereBetween('profile_sourced.created_at', [$yearStart, $yearEnd])
            ->selectRaw("DATE_FORMAT(profile_sourced.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');
        $joiningMonths = $months;

        $monthlyCandidateLevelCounts = function (array $levelIds, string $dateColumn) use ($chartCandidates, $yearStart, $yearEnd) {
            return (clone $chartCandidates)
                ->whereIn('level_of_interview_id', $levelIds)
                ->whereBetween($dateColumn, [$yearStart, $yearEnd])
                ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month_key, COUNT(*) as total")
                ->groupBy('month_key')
                ->pluck('total', 'month_key');
        };

        // Accepted candidates remain part of the acceptance trend after they
        // advance to onboarding. Declined offers have no onboarding date, so
        // their status update date is the available outcome date.
        $monthlyOfferAccepted = $monthlyCandidateLevelCounts([30, 20], 'onboarding_date');
        $monthlyOfferDeclined = $monthlyCandidateLevelCounts([22], 'candidates.updated_at');
        $monthlyOnboarded = $monthlyCandidateLevelCounts([20], 'onboarding_date');
        $monthlyJoinerDeclined = $monthlyCandidateLevelCounts([21], 'candidates.updated_at');
        $monthlyJoiningDetails = $joiningMonths->map(fn ($month) => [
            'label' => $month->format('M Y'),
            'offer_accepted' => (int) ($monthlyOfferAccepted[$month->format('Y-m')] ?? 0),
            'offer_declined' => (int) ($monthlyOfferDeclined[$month->format('Y-m')] ?? 0),
            'onboarded' => (int) ($monthlyOnboarded[$month->format('Y-m')] ?? 0),
            'joiner_declined' => (int) ($monthlyJoinerDeclined[$month->format('Y-m')] ?? 0),
        ]);

        $revenueMonths = $months;

        // Revenue is based on each candidate's onboarding CTC and client billing percentage.
        $revenueOutcomes = (clone $chartCandidates)
            ->with('client.billing')
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->where(function ($onboarded) use ($yearStart, $yearEnd) {
                    $onboarded->where('level_of_interview_id', 20)
                        ->whereBetween('onboarding_date', [$yearStart, $yearEnd]);
                })->orWhere(function ($declined) use ($yearStart, $yearEnd) {
                    $declined->where('level_of_interview_id', 21)
                        ->whereBetween('candidates.updated_at', [$yearStart, $yearEnd]);
                });
            })
            ->get(['client_id', 'mode_id', 'level_of_interview_id', 'take_home', 'expected_ctc', 'onboarding_ctc', 'onboarding_date', 'candidates.updated_at']);
        $candidateRevenue = fn (Candidate $candidate): float => $this->candidateRevenue($candidate);
        $monthlyOutcomeRevenue = $revenueOutcomes
            ->groupBy(fn ($candidate) => (int) $candidate->level_of_interview_id === 21
                ? $candidate->updated_at->format('Y-m')
                : $candidate->onboarding_date->format('Y-m'));
        $monthlyOnboardedRevenue = $monthlyOutcomeRevenue->map(fn ($rows) => $rows
            ->where('level_of_interview_id', 20)
            ->sum($candidateRevenue));
        $monthlyDeclinedRevenue = $monthlyOutcomeRevenue->map(fn ($rows) => $rows
            ->where('level_of_interview_id', 21)
            ->sum($candidateRevenue));
        $monthlyContractRevenue = ContractReport::query()
            ->whereBetween('salary_month', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->whereIn('candidate_id', (clone $chartCandidates)->select('candidates.id'))
            ->selectRaw("DATE_FORMAT(salary_month, '%Y-%m') as month_key, SUM(payable_salary) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');
        $onboardedRevenue = $revenueOutcomes->where('level_of_interview_id', 20)
            ->sum($candidateRevenue);
        $declinedRevenue = $revenueOutcomes->where('level_of_interview_id', 21)
            ->sum($candidateRevenue);
        $contractRevenue = $monthlyContractRevenue->sum();
        $totalOnboardingRevenue = (clone $onboardingCandidates)
            ->with('client.billing')
            ->where('level_of_interview_id', 20)
            ->get(['client_id', 'mode_id', 'take_home', 'onboarding_ctc'])
            ->sum($candidateRevenue);

        $targetMultiplier = $selectedRecruiterId
            ? 1
            : max(1, $availableRecruiters->count());
        $targetMonthStart = now()->startOfMonth();
        $targetMonthEnd = now()->endOfMonth();
        $monthlyKpis = $this->monthlyTargetAnalytics($candidateScope, $targetMultiplier, $targetMonthStart, $targetMonthEnd);
        $deliveryLeadAnalytics = $this->monthlyTargetAnalytics(
            Candidate::visibleTo($user),
            max(1, $availableRecruiters->count()),
            $dateFrom,
            $dateTo
        );
        $recruiterPerformance = $availableRecruiters->mapWithKeys(function (Recruiter $recruiter) use ($dateFrom, $dateTo) {
            $candidateQuery = Candidate::where('recruiter_id', $recruiter->id);

            return [$recruiter->id => $this->monthlyTargetAnalytics(
                $candidateQuery,
                1,
                $dateFrom,
                $dateTo
            )['overallPercentage']];
        });

        return view('backend.index', [
            'scopeLabel' => $isRecruiter
                ? 'My recruitment pipeline'
                : ($isDeliveryLead ? 'My delivery lead recruitment pipeline' : 'Talent Aquisition overview'),
            'isSuperAdminDashboard' => $isSuperAdmin,
            'isDeliveryLeadDashboard' => $isDeliveryLead,
            'isRecruiterDashboard' => $isPersonalDashboard,
            'showClientFilter' => true,
            'recruiterLinked' => ! $isRecruiter || (bool) $linkedRecruiterId,
            'linkedRecruiter' => $linkedRecruiter,
            'recruiters' => $availableRecruiters,
            'clients' => $availableClients,
            'selectedRecruiterId' => $selectedRecruiterId,
            'selectedClientId' => $selectedClientId,
            'selectedFromDate' => $selectedFromDate,
            'selectedToDate' => $selectedToDate,
            'chartYear' => $chartYear,
            'chartYearLabel' => $chartYear.'-'.($chartYear + 1),
            'fromDateError' => $fromDateError,
            'toDateError' => $toDateError,
            'monthlyTargetAnalytics' => $monthlyKpis,
            'targetMonth' => now()->month,
            'targetYear' => now()->year,
            'deliveryLeadAnalytics' => $deliveryLeadAnalytics,
            'recruiterPerformance' => $recruiterPerformance,
            'activeRequirements' => (clone $requirements)->where('status', true)->count(), // sum('number_of_position')
            'priorityRequirements' => (clone $requirements)->where('is_priority', true)->count(),
            'inActiveRequirements' => (clone $requirements)->where('status', false)->count(), // sum('number_of_position')

            'myApplicants' => (clone $candidates)->count(),
            'myProfiles' => (clone $myProfiles)->count(),
            'candidateInterviewStages' => (clone $candidates)
                ->whereIn('level_of_interview_id', [7, 8, 31, 11, 12, 32, 23, 25, 33, 27, 28, 34])
                ->get(['id', 'level_of_interview_id']),
            'yetToOffer' => (clone $candidates)->where('level_of_interview_id', 15)->count(),
            'offered' => (clone $candidates)->whereIn('level_of_interview_id', [30, 35])->count(),
            'hrSelected' => (clone $candidates)->where('level_of_interview_id', 15)->count(),
            'onboarded' => (clone $onboardingCandidates)->where('level_of_interview_id', 20)->count(),
            'monthlyJoiningDetails' => $monthlyJoiningDetails,
            'joiningChartMonths' => $monthlyJoiningDetails->pluck('label')->values(),
            'offerAcceptedChartTotals' => $monthlyJoiningDetails->pluck('offer_accepted')->values(),
            'offerDeclinedChartTotals' => $monthlyJoiningDetails->pluck('offer_declined')->values(),
            'onboardedChartTotals' => $monthlyJoiningDetails->pluck('onboarded')->values(),
            'joinerDeclinedChartTotals' => $monthlyJoiningDetails->pluck('joiner_declined')->values(),
            'revenueChartMonths' => $revenueMonths->map->format('M Y')->values(),
            'revenueChartTotals' => $revenueMonths
                ->map(fn ($month) => round((float) ($monthlyOnboardedRevenue[$month->format('Y-m')] ?? 0), 2))
                ->values(),
            'declinedRevenueChartTotals' => $revenueMonths
                ->map(fn ($month) => round((float) ($monthlyDeclinedRevenue[$month->format('Y-m')] ?? 0), 2))
                ->values(),
            'contractRevenueChartTotals' => $revenueMonths
                ->map(fn ($month) => round((float) ($monthlyContractRevenue[$month->format('Y-m')] ?? 0), 2))
                ->values(),
            'onboardedRevenue' => round((float) $onboardedRevenue, 2),
            'declinedRevenue' => round((float) $declinedRevenue, 2),
            'contractRevenue' => round((float) $contractRevenue, 2),
            'showRevenueDashboard' => $user->can('read', Revenue::class),

            'revenue' => round((float) $totalOnboardingRevenue, 2),
            'candidateLevels' => $candidateLevels,
            'chartMonths' => $months->map->format('M Y')->values(),
            'chartApplicants' => $months->map(fn ($month) => (int) ($monthly[$month->format('Y-m')] ?? 0))->values(),
            'chartProfilesSourced' => $months->map(fn ($month) => (int) ($monthlyProfilesSourced[$month->format('Y-m')] ?? 0))->values(),
            'upcomingInterviews' => (clone $interviews)->with(['candidate', 'client', 'interviewLevel'])
                ->where('schedule_date', '>=', now())->orderBy('schedule_date')->limit(6)->get(),
            'groupedLevels' => $groupedLevels,
            'maxLevel' => $maxLevel,
            'birthdayEmployee' => $birthdayEmployee,
            'employee' => $employee,
            'birthdayEmployees' => $birthdayEmployees,
        ]);
    }

    public function yearCharts(Request $request)
    {
        $this->authorize('dashboard', General::class);

        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'recruiter_id' => 'nullable|integer',
            'client_id' => 'nullable|integer',
        ]);

        $user = Auth::user()->loadMissing('role.permissions');
        $year = (int) $data['year'];
        $yearStart = CarbonImmutable::create($year, 4, 1)->startOfDay();
        $yearEnd = $yearStart->addYear()->subDay()->endOfDay();
        $months = collect(range(0, 11))->map(fn ($offset) => $yearStart->addMonths($offset));
        $candidates = Candidate::visibleTo($user)
            ->when($data['recruiter_id'] ?? null, fn ($query, $id) => $query->where('recruiter_id', $id))
            ->when($data['client_id'] ?? null, fn ($query, $id) => $query->where('client_id', $id));

        $applicants = (clone $candidates)
            ->whereBetween('candidates.created_at', [$yearStart, $yearEnd])
            ->selectRaw("DATE_FORMAT(candidates.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');
        $profilesSourced = ProfileSourced::visibleTo($user)
            ->when($data['recruiter_id'] ?? null, fn ($query, $id) => $query->where('recruiter_id', $id))
            ->whereBetween('profile_sourced.created_at', [$yearStart, $yearEnd])
            ->selectRaw("DATE_FORMAT(profile_sourced.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');

        $levelCounts = function (array $levelIds, string $dateColumn) use ($candidates, $yearStart, $yearEnd) {
            return (clone $candidates)
                ->whereIn('level_of_interview_id', $levelIds)
                ->whereBetween($dateColumn, [$yearStart, $yearEnd])
                ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month_key, COUNT(*) as total")
                ->groupBy('month_key')->pluck('total', 'month_key');
        };

        $offerAccepted = $levelCounts([30, 20], 'onboarding_date');
        $offerDeclined = $levelCounts([22], 'candidates.updated_at');
        $onboarded = $levelCounts([20], 'onboarding_date');
        $joinerDeclined = $levelCounts([21], 'candidates.updated_at');
        $outcomes = $user->can('read', Revenue::class)
            ? (clone $candidates)->with('client.billing')
                ->where(function ($query) use ($yearStart, $yearEnd) {
                    $query->where(function ($onboarded) use ($yearStart, $yearEnd) {
                        $onboarded->where('level_of_interview_id', 20)
                            ->whereBetween('onboarding_date', [$yearStart, $yearEnd]);
                    })->orWhere(function ($declined) use ($yearStart, $yearEnd) {
                        $declined->where('level_of_interview_id', 21)
                            ->whereBetween('candidates.updated_at', [$yearStart, $yearEnd]);
                    });
                })
                ->get(['client_id', 'mode_id', 'level_of_interview_id', 'take_home', 'expected_ctc', 'onboarding_ctc', 'onboarding_date', 'candidates.updated_at'])
                ->groupBy(fn ($candidate) => (int) $candidate->level_of_interview_id === 21
                    ? $candidate->updated_at->format('Y-m')
                    : $candidate->onboarding_date->format('Y-m'))
            : collect();
        $levelNames = InterviewLevel::pluck('level', 'id');
        $pipelineCounts = (clone $candidates)
            ->whereBetween('candidates.created_at', [$yearStart, $yearEnd])
            ->selectRaw('level_of_interview_id, COUNT(*) as total')
            ->groupBy('level_of_interview_id')
            ->pluck('total', 'level_of_interview_id')
            ->mapWithKeys(fn ($total, $levelId) => [($levelNames[$levelId] ?? (string) $levelId) => (int) $total]);
        $onboardingPipelineCounts = (clone $candidates)
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->where(function ($onboarded) use ($yearStart, $yearEnd) {
                    $onboarded->where('level_of_interview_id', 20)
                        ->whereBetween('onboarding_date', [$yearStart, $yearEnd]);
                })->orWhere(function ($declined) use ($yearStart, $yearEnd) {
                    $declined->where('level_of_interview_id', 21)
                        ->whereBetween('candidates.updated_at', [$yearStart, $yearEnd]);
                });
            })
            ->selectRaw('level_of_interview_id, COUNT(*) as total')
            ->groupBy('level_of_interview_id')
            ->pluck('total', 'level_of_interview_id');
        foreach ([20, 21] as $levelId) {
            if (isset($levelNames[$levelId])) {
                $pipelineCounts[$levelNames[$levelId]] = (int) ($onboardingPipelineCounts[$levelId] ?? 0);
            }
        }
        $candidateRevenue = fn (Candidate $candidate): float => $this->candidateRevenue($candidate);
        $monthlyOnboardedRevenue = $outcomes->map(fn ($rows) => $rows->where('level_of_interview_id', 20)->sum($candidateRevenue));
        $monthlyDeclinedRevenue = $outcomes->map(fn ($rows) => $rows->where('level_of_interview_id', 21)->sum($candidateRevenue));
        $monthlyContractRevenue = $user->can('read', Revenue::class)
            ? ContractReport::query()
                ->whereBetween('salary_month', [$yearStart->toDateString(), $yearEnd->toDateString()])
                ->whereIn('candidate_id', (clone $candidates)->select('candidates.id'))
                ->selectRaw("DATE_FORMAT(salary_month, '%Y-%m') as month_key, SUM(payable_salary) as total")
                ->groupBy('month_key')
                ->pluck('total', 'month_key')
            : collect();

        return response()->json([
            'year' => $year,
            'financial_year' => $year.'-'.($year + 1),
            'months' => $months->map->format('M Y')->values(),
            'applicants' => $months->map(fn ($month) => (int) ($applicants[$month->format('Y-m')] ?? 0))->values(),
            'profiles_sourced' => $months->map(fn ($month) => (int) ($profilesSourced[$month->format('Y-m')] ?? 0))->values(),
            'offer_accepted' => $months->map(fn ($month) => (int) ($offerAccepted[$month->format('Y-m')] ?? 0))->values(),
            'offer_declined' => $months->map(fn ($month) => (int) ($offerDeclined[$month->format('Y-m')] ?? 0))->values(),
            'onboarded' => $months->map(fn ($month) => (int) ($onboarded[$month->format('Y-m')] ?? 0))->values(),
            'joiner_declined' => $months->map(fn ($month) => (int) ($joinerDeclined[$month->format('Y-m')] ?? 0))->values(),
            'onboarded_revenue' => $months->map(fn ($month) => round((float) ($monthlyOnboardedRevenue[$month->format('Y-m')] ?? 0), 2))->values(),
            'declined_revenue' => $months->map(fn ($month) => round((float) ($monthlyDeclinedRevenue[$month->format('Y-m')] ?? 0), 2))->values(),
            'contract_revenue' => $months->map(fn ($month) => round((float) ($monthlyContractRevenue[$month->format('Y-m')] ?? 0), 2))->values(),
            'onboarded_revenue_total' => round((float) $monthlyOnboardedRevenue->sum(), 2),
            'declined_revenue_total' => round((float) $monthlyDeclinedRevenue->sum(), 2),
            'contract_revenue_total' => round((float) $monthlyContractRevenue->sum(), 2),
            'pipeline_counts' => $pipelineCounts,
        ]);
    }

    private function candidateRevenue(Candidate $candidate): float
    {
        $ctc = (int) $candidate->level_of_interview_id === 21
            ? ($candidate->onboarding_ctc ?: $candidate->expected_ctc)
            : $candidate->onboarding_ctc;

        // Contract candidates may only have their monthly take-home recorded.
        // Annualize it so their revenue is not silently calculated as zero.
        if ((int) $candidate->mode_id === 2 && (float) $ctc <= 0) {
            $ctc = (float) $candidate->take_home * 12;
        }

        return round(
            (float) $ctc * (float) ($candidate->client?->billing?->value ?? 0) / 100,
            2
        );
    }

    private function monthlyTargetAnalytics(
        $candidates,
        int $targetMultiplier,
        ?CarbonInterface $dateFrom = null,
        ?CarbonInterface $dateTo = null
    ): array {
        $periodStart = $dateFrom ?? ($dateTo ? null : now()->startOfMonth());
        $periodEnd = $dateTo ?? ($dateFrom ? null : now()->endOfMonth());

        $periodCandidates = (clone $candidates)
            ->when($periodStart, fn ($query) => $query->where('candidates.created_at', '>=', $periodStart))
            ->when($periodEnd, fn ($query) => $query->where('candidates.created_at', '<=', $periodEnd));
        $periodOnboardingCandidates = (clone $candidates)
            ->whereNotNull('onboarding_date')
            ->when($periodStart, fn ($query) => $query->whereDate('onboarding_date', '>=', $periodStart->toDateString()))
            ->when($periodEnd, fn ($query) => $query->whereDate('onboarding_date', '<=', $periodEnd->toDateString()));

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
                'completed' => (clone $periodCandidates)->count(),
            ],
            [
                'label' => 'Candidate Shortlisting',
                'target' => 150,
                'unit' => 'CVs',
                'icon' => 'ri-user-search-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $shortlistedLevelIds)->count(),
            ],
            [
                'label' => 'Interviews',
                'target' => 70,
                'unit' => 'rounds',
                'icon' => 'ri-calendar-check-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $interviewLevelIds)->count(),
            ],
            [
                'label' => 'HR Select',
                'target' => 25,
                'unit' => 'screenings',
                'icon' => 'ri-survey-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $hrSelectId)->count(),
            ],
            [
                'label' => 'Offers Released',
                'target' => 15,
                'unit' => 'offers',
                'icon' => 'ri-draft-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $offerReleasedIds)->count(),
            ],
            [
                'label' => 'Offer Acceptance',
                'target' => 12,
                'unit' => 'acceptances',
                'icon' => 'ri-user-follow-line',
                'completed' => (clone $periodCandidates)->whereIn('level_of_interview_id', $offerAcceptedId)->count(),
            ],
            [
                'label' => 'Onboarding',
                'target' => 10,
                'unit' => 'joiners',
                'icon' => 'ri-team-line',
                'completed' => (clone $periodOnboardingCandidates)->where('level_of_interview_id', $onboardedId)->count(),
            ],
        ];

        $configuredTargets = Target::where('status', true)->pluck('monthly_target', 'target_name');
        $kpis = collect($definitions)->map(function (array $kpi) use ($targetMultiplier, $configuredTargets) {
            $kpi['target'] = (int) ($configuredTargets->get($kpi['label'], $kpi['target'])) * $targetMultiplier;
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

    public function monthlyTargets(Request $request)
    {
        $this->authorize('dashboard', General::class);

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'recruiter_id' => ['nullable', 'integer'],
            'client_id' => ['nullable', 'integer'],
        ]);

        $user = Auth::user()->loadMissing('role.permissions');
        $accessLevel = str_replace('_', '-', strtolower((string) $user->role?->access_level));
        $isSuperAdmin = $accessLevel === 'super-admin';
        $isDeliveryLead = in_array($accessLevel, ['delivery-lead', 'recruiter-dl'], true);
        $isRecruiter = $accessLevel === 'recruiter';
        $availableRecruiters = ($isSuperAdmin || $isDeliveryLead)
            ? Recruiter::where('status', true)
                ->when($isDeliveryLead, fn ($query) => $query->visibleTo($user))
                ->orderBy('recruiter_name')
                ->get()
            : collect();
        $linkedRecruiterId = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->value('id')
            : null;
        $requestedRecruiterId = isset($validated['recruiter_id']) ? (int) $validated['recruiter_id'] : null;
        $selectedRecruiterId = $isRecruiter
            ? ($linkedRecruiterId ?? 0)
            : ($availableRecruiters->contains('id', $requestedRecruiterId) ? $requestedRecruiterId : null);
        $selectedClientId = isset($validated['client_id'])
            && Client::where('status', true)->whereKey($validated['client_id'])->exists()
                ? (int) $validated['client_id']
                : null;

        $candidateScope = Candidate::visibleTo($user)
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId));
        $targetMultiplier = $selectedRecruiterId ? 1 : max(1, $availableRecruiters->count());
        $periodStart = CarbonImmutable::create((int) $validated['year'], (int) $validated['month'], 1)->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();

        return response()->json(array_merge(
            $this->monthlyTargetAnalytics($candidateScope, $targetMultiplier, $periodStart, $periodEnd),
            ['monthLabel' => $periodStart->format('F Y')]
        ));
    }

    public function profile()
    {
        $this->authorize('profileRead', General::class);
        $user = Auth::user();
        $employee = Employee::query()
            ->where(function ($query) use ($user) {
                $query->whereRaw('LOWER(official_mail) = ?', [mb_strtolower($user->email)])
                    ->orWhereRaw('LOWER(personal_mail) = ?', [mb_strtolower($user->email)]);
            })
            ->first();

        return view('backend.common.profile', compact('employee'));
    }

    public function settingStore(Request $request) {}

    public function uploadProfile(Request $request)
    {
        // Every authenticated user may update their own cover image. Keep the
        // existing permission requirement for profile photo changes.
        if ($request->hasFile('profile_image')) {
            $this->authorize('profileEdit', General::class);
        }

        $data = $request->validate([
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|required_without:cover_image',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|required_without:profile_image',
        ]);
        $user = User::findOrFail(Auth::id());

        foreach (['profile_image' => 'profile_picture', 'cover_image' => 'cover_picture'] as $input => $column) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $file = $request->file($input);
            $directory = $input === 'profile_image' ? 'uploads/profile_images' : 'uploads/cover_images';
            $fileName = uniqid($input.'_', true).'.'.$file->getClientOriginalExtension();
            File::ensureDirectoryExists(public_path($directory));
            $file->move(public_path($directory), $fileName);

            if ($user->{$column} && file_exists(public_path($user->{$column}))) {
                unlink(public_path($user->{$column}));
            }
            $user->{$column} = $directory.'/'.$fileName;
        }

        $user->save();

        return back()->with('success', 'Profile images updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        abort_unless($user->isSuperAdmin(), 403, 'Only the super administrator can change a password here.');

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|different:current_password|confirmed',
        ], [
            'password.different' => 'The new password must be different from the current password.',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
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
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            session()->forget('locked');

            return redirect()->intended(route('admin.dashboard'))->with('info', 'Welcome back!');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }
}
