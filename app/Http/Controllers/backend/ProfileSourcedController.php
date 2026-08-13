<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\InterviewLevel;
use App\Models\ProfileSourced;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProfileSourcedController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ProfileSourced::class);

        if ($request->ajax()) {
            return DataTables::of($this->visibleProfiles()->with('recruiter')->latest())
                ->addIndexColumn()
                ->addColumn('recruiter_name', fn ($row) => e($row->recruiter?->recruiter_name ?? '-'))
                ->addColumn('cv', fn ($row) => '<a class="btn btn-sm btn-outline-primary" target="_blank" href="'.asset($row->cv_path).'">View CV</a>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                        $buttons .= '<button type="button" data-route="'.route('admin.profile-sourced.move', $row).'" class="btn btn-sm btn-success me-1 move-to-candidate">Move to Candidate</button>';
                    if (auth()->user()->can('edit', ProfileSourced::class)) {
                        $buttons .= '<a href="'.route('admin.profile-sourced.edit', $row).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', ProfileSourced::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.profile-sourced.delete', $row).'" class="btn btn-link text-danger fs-4 p-0 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['cv', 'action'])
                ->make(true);
        }

        return view('backend.profile-sourced.index');
    }

    public function create()
    {
        $this->authorize('create', ProfileSourced::class);

        return view('backend.profile-sourced.create', ['recruiter' => $this->currentRecruiter()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ProfileSourced::class);
        $recruiter = $this->currentRecruiter();
        $data = $this->validatedData($request);
        $data['recruiter_id'] = $recruiter->id;
        $data['created_by_user_id'] = auth()->id();
        $data['cv_path'] = $this->storeCv($request->file('cv'));
        unset($data['cv']);
        ProfileSourced::create($data);

        return redirect()->route('admin.profile-sourced.index')->with('success', 'Profile sourced successfully.');
    }

    public function edit(ProfileSourced $profileSourced)
    {
        $this->authorize('edit', ProfileSourced::class);
        $profileSourced = $this->visibleProfiles()->findOrFail($profileSourced->id);

        return view('backend.profile-sourced.edit', compact('profileSourced'));
    }

    public function update(Request $request, ProfileSourced $profileSourced)
    {
        $this->authorize('edit', ProfileSourced::class);
        $profileSourced = $this->visibleProfiles()->findOrFail($profileSourced->id);
        $data = $this->validatedData($request, $profileSourced);

        if ($request->hasFile('cv')) {
            $oldCv = $profileSourced->cv_path;
            $data['cv_path'] = $this->storeCv($request->file('cv'));
            File::delete(public_path($oldCv));
        }
        unset($data['cv']);
        $profileSourced->update($data);

        return redirect()->route('admin.profile-sourced.index')->with('success', 'Sourced profile updated successfully.');
    }

    public function destroy(ProfileSourced $profileSourced)
    {
        $this->authorize('delete', ProfileSourced::class);
        $profileSourced = $this->visibleProfiles()->findOrFail($profileSourced->id);
        File::delete(public_path($profileSourced->cv_path));
        $profileSourced->delete();

        return response()->json(['status' => true, 'message' => 'Sourced profile deleted successfully.']);
    }

    public function moveToCandidate(ProfileSourced $profileSourced)
    {
        $this->authorize('delete', ProfileSourced::class);
        $this->authorize('create', Candidate::class);
        $profileSourced = $this->visibleProfiles()->findOrFail($profileSourced->id);
        $levelId = InterviewLevel::whereRaw('LOWER(level) = ?', ['profile feedback pending'])->value('id');

        if (! $levelId) {
            throw ValidationException::withMessages(['profile' => 'The Profile Feedback Pending interview level is not configured.']);
        }
        if (Candidate::where('mobile_no', $profileSourced->mobile_number)->exists()) {
            throw ValidationException::withMessages(['profile' => 'A candidate with this mobile number already exists.']);
        }
        if (Candidate::whereRaw('LOWER(email) = ?', [mb_strtolower($profileSourced->email)])->exists()) {
            throw ValidationException::withMessages(['profile' => 'A candidate with this email address already exists.']);
        }

        $candidate = DB::transaction(function () use ($profileSourced, $levelId) {
            $candidate = Candidate::create([
                'recruiter_id' => $profileSourced->recruiter_id,
                'candidate_name' => $profileSourced->candidate_name,
                'mobile_no' => $profileSourced->mobile_number,
                'email' => mb_strtolower($profileSourced->email),
                'upload_cv' => $profileSourced->cv_path,
                'level_of_interview_id' => $levelId,
                'status' => true,
            ]);
            $profileSourced->delete();

            return $candidate;
        });

        return response()->json([
            'status' => true,
            'message' => 'Profile moved to Candidates successfully.',
            'redirect' => route('admin.candidates.edit', $candidate->id),
        ]);
    }

    private function validatedData(Request $request, ?ProfileSourced $profile = null): array
    {
        return $request->validate([
            'candidate_name' => 'required|string|max:255',
            'cv' => [$profile ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            'mobile_number' => ['required', 'string', 'max:30', Rule::unique('profile_sourced', 'mobile_number')->ignore($profile?->id)->whereNull('deleted_at')],
            'email' => ['required', 'email', 'max:255', Rule::unique('profile_sourced', 'email')->ignore($profile?->id)->whereNull('deleted_at')],
        ]);
    }

    private function currentRecruiter(): Recruiter
    {
        $recruiter = Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower(trim((string) auth()->user()->email))])->first();

        if (! $recruiter) {
            throw ValidationException::withMessages([
                'recruiter' => 'Your login email is not mapped to a recruiter. Add the same email in the Recruiters master first.',
            ]);
        }

        return $recruiter;
    }

    private function storeCv($file): string
    {
        File::ensureDirectoryExists(public_path('uploads/candidates'));
        $name = Str::uuid().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/candidates'), $name);

        return 'uploads/candidates/'.$name;
    }

    private function visibleProfiles()
    {
        return ProfileSourced::visibleTo(auth()->user()->loadMissing('role'));
    }
}
