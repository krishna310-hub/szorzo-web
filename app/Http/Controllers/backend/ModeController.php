<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ModeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Mode::class);

        if ($request->ajax()) {
            return DataTables::of(Mode::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Mode::class)) {
                        $buttons .= '<a href="' . route('admin.modes.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Mode::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.modes.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.modes.index');
    }

    public function create()
    {
        $this->authorize('create', Mode::class);
        return view('backend.modes.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Mode::class);
        Mode::create($this->validatedData($request));

        return redirect()->route('admin.modes.index')->with('success', 'Mode created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Mode::class);
        $mode = Mode::findOrFail($id);

        return view('backend.modes.edit', compact('mode'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Mode::class);
        $mode = Mode::findOrFail($id);
        $mode->update($this->validatedData($request, $mode->id));

        return redirect()->route('admin.modes.index')->with('success', 'Mode updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Mode::class);
        Mode::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Mode deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'mode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('modes', 'mode')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'status' => 'required|in:0,1',
        ]);
    }
}
