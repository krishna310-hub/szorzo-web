<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Division::class);

        if ($request->ajax()) {
            return DataTables::of(Division::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('Y-m-d H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Division::class)) {
                        $buttons .= '<a href="' . route('admin.divisions.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Division::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.divisions.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.divisions.index');
    }

    public function create()
    {
        $this->authorize('create', Division::class);
        return view('backend.divisions.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Division::class);
        Division::create($this->validatedData($request));
        return redirect()->route('admin.divisions.index')->with('success', 'Division created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Division::class);
        return view('backend.divisions.edit', ['division' => Division::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Division::class);
        $division = Division::findOrFail($id);
        $division->update($this->validatedData($request, $division->id));
        return redirect()->route('admin.divisions.index')->with('success', 'Division updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Division::class);
        Division::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Division deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('divisions')->ignore($ignoreId)->whereNull('deleted_at')],
            'status' => 'required|in:0,1',
        ]);
    }
}
