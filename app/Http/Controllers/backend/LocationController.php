<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Location::class);

        if ($request->ajax()) {
            $locations = Location::latest();

            return DataTables::of($locations)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success-subtle text-success">Active</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', Location::class)) {
                        $buttons .= '<a href="' . route('admin.locations.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', Location::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.locations.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.locations.index');
    }

    public function create()
    {
        $this->authorize('create', Location::class);
        return view('backend.locations.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Location::class);

        $data = $request->validate([
            'location' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'location')->whereNull('deleted_at'),
            ],
            'status' => 'required|in:0,1',
        ]);

        Location::create($data);

        return redirect()->route('admin.locations.index')->with('success', 'Location created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Location::class);
        $location = Location::findOrFail($id);

        return view('backend.locations.edit', compact('location'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Location::class);
        $location = Location::findOrFail($id);

        $data = $request->validate([
            'location' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'location')->ignore($location->id)->whereNull('deleted_at'),
            ],
            'status' => 'required|in:0,1',
        ]);

        $location->update($data);

        return redirect()->route('admin.locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Location::class);
        Location::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Location deleted successfully.']);
    }
}
