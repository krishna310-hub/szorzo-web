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
        $isPersonalDashboard = !$isSuperAdmin;
        $linkedRecruiterId = $isPersonalDashboard
            ? Recruiter::whereRaw('LOWER(email) = ?', [strtolower($user->email)])->value('id')
            : null;

        $selectedRecruiterId = $isPersonalDashboard
            ? ($linkedRecruiterId ?? 0)
            : ($request->filled('recruiter_id') ? (int) $request->recruiter_id : null);
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

        return view('backend.index', [
            'scopeLabel' => $isRecruiter
                ? 'My recruitment pipeline'
                : ($isDeliveryLead ? 'My delivery lead recruitment pipeline' : 'Management recruitment overview'),
            'isRecruiterDashboard' => $isPersonalDashboard,
            'showClientFilter' => $isSuperAdmin,
            'recruiterLinked' => $isSuperAdmin || (bool) $linkedRecruiterId,
            'recruiters' => $isSuperAdmin ? Recruiter::where('status', true)->orderBy('recruiter_name')->get() : collect(),
            'clients' => $isSuperAdmin ? Client::where('status', true)->orderBy('client')->get() : collect(),
            'selectedRecruiterId' => $selectedRecruiterId,
            'selectedClientId' => $selectedClientId,
            'activeRequirements' => (clone $requirements)->where('status', true)->sum('number_of_position'),
            'myApplicants' => (clone $candidates)->count(),
            'scheduledInterviews' => (clone $interviews)->where('status', 'scheduled')->get(),
            'yetToOffer' => (clone $interviews)->where('level_of_interview_id', 14)->count(),
            'offered' => (clone $interviews)->where('status', 'selected')->count(),
            'revenue' => (clone $requirements)->sum('revenue_amount'),
            'interviewLevels' => $interviewLevels,
            'chartMonths' => $months->map->format('M')->values(),
            'chartApplicants' => $months->map(fn ($month) => (int) ($monthly[$month->format('Y-m')] ?? 0))->values(),
            'upcomingInterviews' => (clone $interviews)->with(['candidate', 'client', 'interviewLevel'])
                ->where('schedule_date', '>=', now())->orderBy('schedule_date')->limit(6)->get(),
        ]);
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
