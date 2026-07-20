<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientRequirement;
use App\Models\General;
use App\Models\InterviewSchedule;
use App\Models\Recruiter;
use App\Models\User;
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
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('project_owner', $selectedRecruiterId))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId));
        $candidates = Candidate::query()
            ->when($selectedRecruiterId !== null, fn ($query) => $query->where('recruiter_id', $selectedRecruiterId))
            ->when($selectedClientId, fn ($query) => $query->where('client_id', $selectedClientId));
        $interviews = InterviewSchedule::query()
            ->when($selectedRecruiterId !== null, fn ($query) => $query->whereHas('candidate',
                fn ($candidate) => $candidate->where('recruiter_id', $selectedRecruiterId)))
            ->when($selectedClientId, fn ($query) => $query->where(function ($clientQuery) use ($selectedClientId) {
                $clientQuery->where('client_id', $selectedClientId)
                    ->orWhereHas('candidate', fn ($candidate) => $candidate->where('client_id', $selectedClientId));
            }));

        $interviewLevels = (clone $interviews)
            ->join('level_of_interviews', 'level_of_interviews.id', '=', 'interview_schedules.level_of_interview_id')
            ->selectRaw('level_of_interviews.level, COUNT(*) as total')
            ->groupBy('level_of_interviews.id', 'level_of_interviews.level', 'level_of_interviews.sort_order')
            ->orderBy('level_of_interviews.sort_order')
            ->get();

        $monthly = (clone $candidates)
            ->where('candidates.created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(candidates.created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')->pluck('total', 'month_key');
        $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonths($offset));

        $targetMultiplier = $selectedRecruiterId
            ? 1
            : max(1, $availableRecruiters->count());
        $monthlyKpis = $this->monthlyTargetAnalytics($candidates, $interviews, $targetMultiplier);
        $deliveryLeadAnalytics = $this->monthlyTargetAnalytics(
            Candidate::query(),
            InterviewSchedule::query(),
            max(1, $availableRecruiters->count())
        );
        $recruiterPerformance = $availableRecruiters->mapWithKeys(function (Recruiter $recruiter) {
            $candidateQuery = Candidate::where('recruiter_id', $recruiter->id);
            $interviewQuery = InterviewSchedule::whereHas('candidate',
                fn ($query) => $query->where('recruiter_id', $recruiter->id));

            return [$recruiter->id => $this->monthlyTargetAnalytics($candidateQuery, $interviewQuery, 1)['overallPercentage']];
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
            'monthlyTargetAnalytics' => $monthlyKpis,
            'deliveryLeadAnalytics' => $deliveryLeadAnalytics,
            'recruiterPerformance' => $recruiterPerformance,
            'activeRequirements' => (clone $requirements)->where('status', true)->sum('number_of_position'),
            'myApplicants' => (clone $candidates)->count(),
            'scheduledInterviews' => (clone $interviews)->where('status', 'scheduled')->get(),
            'yetToOffer' => (clone $interviews)->where('level_of_interview_id', 14)->count(),
            'offered' => (clone $interviews)->where('status', 'selected')->count(),
            'onboarded' => (clone $interviews)->where('status', 'selected')->count(),
            'revenue' => (clone $requirements)->sum('revenue_amount'),
            'interviewLevels' => $interviewLevels,
            'chartMonths' => $months->map->format('M')->values(),
            'chartApplicants' => $months->map(fn ($month) => (int) ($monthly[$month->format('Y-m')] ?? 0))->values(),
            'upcomingInterviews' => (clone $interviews)->with(['candidate', 'client', 'interviewLevel'])
                ->where('schedule_date', '>=', now())->orderBy('schedule_date')->limit(6)->get(),
        ]);
    }

    private function monthlyTargetAnalytics($candidates, $interviews, int $targetMultiplier): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthlyCandidates = (clone $candidates)->whereBetween('candidates.created_at', [$monthStart, $monthEnd]);
        $monthlyInterviews = (clone $interviews)->whereBetween('interview_schedules.schedule_date', [$monthStart, $monthEnd]);

        $definitions = [
            ['label' => 'CV Submission', 'min' => 100, 'max' => 200, 'unit' => 'CVs', 'icon' => 'ri-file-search-line',
                'completed' => (clone $monthlyCandidates)->count()],
            ['label' => 'Candidate Shortlisting', 'min' => 80, 'max' => 120, 'unit' => 'CVs', 'icon' => 'ri-user-search-line',
                'completed' => (clone $monthlyCandidates)->whereNotNull('level_of_interview_id')->count()],
            ['label' => 'Interviews', 'min' => 60, 'max' => 80, 'unit' => 'rounds', 'icon' => 'ri-calendar-check-line',
                'completed' => (clone $monthlyInterviews)->count()],
            ['label' => 'HR Select', 'min' => 45, 'max' => 65, 'unit' => 'screenings', 'icon' => 'ri-survey-line',
                'completed' => (clone $monthlyInterviews)->whereHas('interviewLevel', fn ($query) => $query->where('level', 'like', '%HR%'))->count()],
            ['label' => 'Offers Released', 'min' => 10, 'max' => 15, 'unit' => 'offers', 'icon' => 'ri-draft-line',
                'completed' => (clone $monthlyInterviews)->whereHas('interviewLevel', fn ($query) => $query->where('level', 'like', '%offer%'))->distinct('candidate_id')->count('candidate_id')],
            ['label' => 'Offer Acceptance', 'min' => 8, 'max' => 12, 'unit' => 'acceptances', 'icon' => 'ri-user-follow-line',
                'completed' => (clone $monthlyInterviews)->where('status', 'selected')->distinct('candidate_id')->count('candidate_id')],
            ['label' => 'Onboarding', 'min' => 8, 'max' => 10, 'unit' => 'joiners', 'icon' => 'ri-team-line',
                'completed' => (clone $monthlyInterviews)->whereHas('interviewLevel', fn ($query) => $query->where(function ($level) {
                    $level->where('level', 'like', '%onboard%')->orWhere('level', 'like', '%join%');
                }))->distinct('candidate_id')->count('candidate_id')],
        ];

        $kpis = collect($definitions)->map(function (array $kpi) use ($targetMultiplier) {
            $kpi['min'] *= $targetMultiplier;
            $kpi['max'] *= $targetMultiplier;
            $kpi['target'] = $kpi['min'].'-'.$kpi['max'];
            $kpi['percentage'] = min(100, (int) round(($kpi['completed'] / max(1, $kpi['min'])) * 100));
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

    public function settingStore(Request $request){

    }

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
