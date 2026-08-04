<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Recruiter;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RecruiterController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Recruiter::class);

        if ($request->ajax()) {
            $recruiters = Recruiter::with('deliveryLead')->latest();

            return DataTables::of($recruiters)
                ->addIndexColumn()
                ->addColumn('delivery_lead', fn ($row) => $row->deliveryLead?->name ?? '-')
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
        return view('backend.recruiters.create', ['deliveryLeads' => $this->deliveryLeads()]);
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
        return view('backend.recruiters.edit', ['recruiter' => $recruiter, 'deliveryLeads' => $this->deliveryLeads()]);
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
            'recruiter_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:30',
            'performance_rating' => 'nullable|numeric|min:0|max:10',
            'delivery_lead_user_id' => ['nullable', Rule::in($this->deliveryLeads()->pluck('id')->all())],
            'status' => 'required|in:0,1',
        ]);
    }

    private function deliveryLeads()
    {
        return User::whereHas('role', function ($query) {
            $query->whereIn('access_level', ['delivery-lead', 'delivery_lead', 'recruiter-dl', 'recruiter_dl']);
        })->where('is_active', 1)->orderBy('name')->get();
    }
}
