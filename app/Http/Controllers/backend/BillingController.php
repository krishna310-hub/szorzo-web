<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BillingController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Billing::class);
        if ($request->ajax()) {
            return DataTables::of(Billing::latest())
                ->addIndexColumn()
                ->editColumn('value', fn ($row) => $row->value !== null ? $row->value . '%' : '-')
                ->editColumn('status', fn($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Billing::class)) {
                        $buttons .= '<a href="' . route('admin.billings.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Billing::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.billings.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('backend.billings.index');
    }

    public function create()
    {
        $this->authorize('create', Billing::class);
        return view('backend.billings.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Billing::class);
        Billing::create($this->validatedData($request));
        return redirect()->route('admin.billings.index')->with('success', 'Billing created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Billing::class);
        return view('backend.billings.edit', ['billing' => Billing::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Billing::class);
        $billing = Billing::findOrFail($id);
        $billing->update($this->validatedData($request, $billing->id));
        return redirect()->route('admin.billings.index')->with('success', 'Billing updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Billing::class);
        Billing::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Billing deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'value'  => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
        ]);
    }
}
