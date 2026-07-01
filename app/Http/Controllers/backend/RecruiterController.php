<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Recruiter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RecruiterController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Recruiter::class);

        if ($request->ajax()) {
            $recruiters = Recruiter::with('location')->latest();

            return DataTables::of($recruiters)
                ->addIndexColumn()
                ->addColumn('location_name', fn ($row) => $row->location->name ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Recruiter::class)) {
                        $buttons .= '<a href="' . route('admin.recruiters.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Recruiter::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.recruiters.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.recruiters.index');
    }

    public function create()
    {
        $this->authorize('create', Recruiter::class);
        $locations = Location::where('status', true)->orderBy('name')->get();

        return view('backend.recruiters.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Recruiter::class);
        Recruiter::create($this->validatedData($request));

        return redirect()->route('admin.recruiters.index')->with('success', 'Recruiter created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Recruiter::class);
        $recruiter = Recruiter::findOrFail($id);
        $locations = Location::where('status', true)->orderBy('name')->get();

        return view('backend.recruiters.edit', compact('recruiter', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Recruiter::class);
        $recruiter = Recruiter::findOrFail($id);
        $recruiter->update($this->validatedData($request));

        return redirect()->route('admin.recruiters.index')->with('success', 'Recruiter updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Recruiter::class);
        Recruiter::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Recruiter deleted successfully.']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'email' => 'nullable|email|max:255',
            'mobile_no' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);
    }
}
