<?php

namespace App\Http\Controllers\backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Location;
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
            $candidates = Candidate::with('location')->latest();

            return DataTables::of($candidates)
                ->addIndexColumn()
                ->editColumn('profile_image', function ($row) {
                    return $row->profile_image
                        ? '<img src="' . asset($row->profile_image) . '" alt="' . e($row->name) . '" class="rounded" width="45" height="45" style="object-fit: cover;">'
                        : '-';
                })
                ->addColumn('location_name', fn ($row) => $row->location->name ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
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
                ->rawColumns(['profile_image', 'status', 'action'])
                ->make(true);
        }

        return view('backend.candidates.index');
    }

    public function create()
    {
        $this->authorize('create', Candidate::class);
        $locations = Location::where('status', true)->orderBy('name')->get();

        return view('backend.candidates.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Candidate::class);
        $data = $this->validatedData($request);

        if ($request->hasFile('profile_image')) {
            $upload = Helper::uploadImage($request->file('profile_image'), 'candidates');
            if ($upload['status']) {
                $data['profile_image'] = $upload['name'];
            }
        }

        Candidate::create($data);

        return redirect()->route('admin.candidates.index')->with('success', 'Candidate created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Candidate::class);
        $candidate = Candidate::findOrFail($id);
        $locations = Location::where('status', true)->orderBy('name')->get();

        return view('backend.candidates.edit', compact('candidate', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Candidate::class);
        $candidate = Candidate::findOrFail($id);
        $data = $this->validatedData($request);

        if ($request->hasFile('profile_image')) {
            $oldImage = $candidate->profile_image;
            $upload = Helper::uploadImage($request->file('profile_image'), 'candidates');
            if ($upload['status']) {
                $data['profile_image'] = $upload['name'];
                if ($oldImage) {
                    Helper::unlinkImage($oldImage);
                }
            }
        }

        $candidate->update($data);

        return redirect()->route('admin.candidates.index')->with('success', 'Candidate updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Candidate::class);
        $candidate = Candidate::findOrFail($id);
        if ($candidate->profile_image) {
            Helper::unlinkImage($candidate->profile_image);
        }
        $candidate->delete();

        return response()->json(['status' => true, 'message' => 'Candidate deleted successfully.']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'location_id' => 'nullable|exists:locations,id',
            'email' => 'nullable|email|max:255',
            'mobile_no' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);
    }
}
