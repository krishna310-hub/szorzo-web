<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Invester;
use App\Models\InvesterFinancialDetail;
use App\Models\InvesterLegalCompliance;
use App\Models\InvesterTeamManagement;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Invester::class);

        if ($request->ajax()) {
            $investers = Invester::with('accountManager')->latest();

            return DataTables::of($investers)
                ->addIndexColumn()
                ->addColumn('profile_picture_thumb', function ($row) {
                    if ($row->profile_picture && file_exists(public_path($row->profile_picture))) {
                        return '<img src="' . asset($row->profile_picture) . '" class="rounded-circle avatar-xs" alt="Profile" style="width:36px;height:36px;object-fit:cover;">';
                    }
                    $initial = strtoupper(substr($row->name, 0, 1));
                    return '<div class="avatar-xs d-inline-block"><div class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold" style="width:36px;height:36px;line-height:36px;text-align:center;">' . $initial . '</div></div>';
                })
                ->addColumn('account_manager_name', fn ($row) => $row->accountManager?->name ?? '<span class="text-muted">Unassigned</span>')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    $buttons .= '<a href="' . route('admin.investers.show', $row->id) . '" class="text-primary fs-4 me-1" title="View Profile"><i class="bx bx-show"></i></a>';

                    if (auth()->user()->can('edit', Invester::class)) {
                        $buttons .= '<a href="' . route('admin.investers.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', Invester::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.investers.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['profile_picture_thumb', 'account_manager_name', 'status', 'action'])
                ->make(true);
        }

        return view('backend.investers.index');
    }

    public function create()
    {
        $this->authorize('create', Invester::class);

        $suggestedId = Invester::nextInvesterId();
        $accountManagers = User::orderBy('name')->get();

        return view('backend.investers.create', compact('suggestedId', 'accountManagers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invester::class);

        $validated = $request->validate([
            'invester_id' => 'required|string|max:50|unique:investers,invester_id',
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'investor_type' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:50',
            'primary_email' => 'nullable|email|max:150',
            'alternate_email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'account_manager_id' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
            'status' => 'required|in:0,1',

            // Optional initial Team & Management Scope
            'team_name' => 'nullable|string|max:255',
            'managed_employee_count' => 'nullable|string|max:50',
            'operational_contacts' => 'nullable|string',
            'governance_role' => 'nullable|string|max:100',

            // Optional initial Financial & Investment Details
            'investment_amount' => 'nullable|numeric|min:0',
            'equity_percentage' => 'nullable|numeric|between:0,100',
            'shares_units_held' => 'nullable|string|max:100',
            'investment_date' => 'nullable|date',

            // Optional initial Legal, Compliance & Governance
            'tax_id_number' => 'nullable|string|max:100',
            'kyc_aml_status' => 'nullable|string|in:Verified,Pending,Rejected',
            'nda_signed_doc' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg,jpeg|max:10240',
        ]);

        $investerData = [
            'invester_id' => $validated['invester_id'],
            'name' => $validated['name'],
            'investor_type' => $validated['investor_type'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'primary_email' => $validated['primary_email'] ?? null,
            'alternate_email' => $validated['alternate_email'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_manager_id' => $validated['account_manager_id'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => (bool) $validated['status'],
        ];

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $dir = public_path('uploads/investers/profiles');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $investerData['profile_picture'] = 'uploads/investers/profiles/' . $filename;
        }

        $invester = Invester::create($investerData);

        // Save Team & Management Scope if provided
        if (!empty($validated['team_name'])) {
            InvesterTeamManagement::create([
                'invester_id' => $invester->id,
                'team_name' => $validated['team_name'],
                'managed_employee_count' => $validated['managed_employee_count'] ?? null,
                'operational_contacts' => $validated['operational_contacts'] ?? null,
                'governance_role' => $validated['governance_role'] ?? null,
                'status' => 1,
            ]);
        }

        // Save Financial & Investment Details if provided
        if (!empty($validated['investment_amount']) || !empty($validated['equity_percentage']) || !empty($validated['shares_units_held']) || !empty($validated['investment_date'])) {
            InvesterFinancialDetail::create([
                'invester_id' => $invester->id,
                'investment_amount' => $validated['investment_amount'] ?? null,
                'equity_percentage' => $validated['equity_percentage'] ?? null,
                'shares_units_held' => $validated['shares_units_held'] ?? null,
                'investment_date' => $validated['investment_date'] ?? null,
                'status' => 1,
            ]);
        }

        // Save Legal, Compliance & Governance if provided
        if (!empty($validated['tax_id_number']) || !empty($validated['kyc_aml_status']) || $request->hasFile('nda_signed_doc')) {
            $legalDocPath = null;
            if ($request->hasFile('nda_signed_doc')) {
                $docFile = $request->file('nda_signed_doc');
                $docDir = public_path('uploads/investers/agreements');
                if (!file_exists($docDir)) {
                    mkdir($docDir, 0777, true);
                }
                $docName = time() . '_' . Str::slug(pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $docFile->getClientOriginalExtension();
                $docFile->move($docDir, $docName);
                $legalDocPath = 'uploads/investers/agreements/' . $docName;
            }

            InvesterLegalCompliance::create([
                'invester_id' => $invester->id,
                'tax_id_number' => $validated['tax_id_number'] ?? null,
                'kyc_aml_status' => $validated['kyc_aml_status'] ?? 'Pending',
                'nda_signed_doc' => $legalDocPath,
                'status' => 1,
            ]);
        }

        return redirect()->route('admin.investers.index')->with('success', 'Investor ' . $invester->name . ' (' . $invester->invester_id . ') created successfully.');
    }

    public function show($id)
    {
        $this->authorize('read', Invester::class);

        $invester = Invester::with(['accountManager', 'teamManagements', 'financialDetails', 'legalCompliances'])->findOrFail($id);

        return view('backend.investers.show', compact('invester'));
    }

    public function edit($id)
    {
        $this->authorize('edit', Invester::class);

        $invester = Invester::with(['accountManager', 'teamManagements', 'financialDetails', 'legalCompliances'])->findOrFail($id);
        $accountManagers = User::orderBy('name')->get();

        return view('backend.investers.edit', compact('invester', 'accountManagers'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Invester::class);

        $invester = Invester::findOrFail($id);

        $validated = $request->validate([
            'invester_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('investers', 'invester_id')->ignore($invester->id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'investor_type' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:50',
            'primary_email' => 'nullable|email|max:150',
            'alternate_email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'account_manager_id' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'invester_id' => $validated['invester_id'],
            'name' => $validated['name'],
            'investor_type' => $validated['investor_type'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'primary_email' => $validated['primary_email'] ?? null,
            'alternate_email' => $validated['alternate_email'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_manager_id' => $validated['account_manager_id'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => (bool) $validated['status'],
        ];

        if ($request->hasFile('profile_picture')) {
            if ($invester->profile_picture && file_exists(public_path($invester->profile_picture))) {
                @unlink(public_path($invester->profile_picture));
            }

            $file = $request->file('profile_picture');
            $dir = public_path('uploads/investers/profiles');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['profile_picture'] = 'uploads/investers/profiles/' . $filename;
        }

        $invester->update($data);

        return redirect()->route('admin.investers.index')->with('success', 'Investor ' . $invester->name . ' updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Invester::class);

        $invester = Invester::findOrFail($id);
        $invester->delete();

        return response()->json(['status' => true, 'message' => 'Investor deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', Invester::class);

        $rows = Invester::with('accountManager')->get()->map(function ($row) {
            return [
                $row->invester_id,
                $row->name,
                $row->investor_type,
                $row->contact,
                $row->primary_email,
                $row->alternate_email,
                $row->address,
                $row->accountManager?->name ?? 'Unassigned',
                $row->internal_notes,
                $row->status ? 'Active' : 'Inactive',
                $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            ];
        })->toArray();

        $headings = [
            'Invester ID',
            'Invester Name',
            'Investor Type',
            'Contact',
            'Primary Email',
            'Alternate Email',
            'Residential / Office Address',
            'Account Manager / Internal Point of Contact',
            'Internal Notes',
            'Status',
            'Created Date',
        ];

        $dropdowns = [
            'Investor Type' => ['Angel Investor', 'Venture Capitalist', 'Institutional', 'Strategic Partner', 'Family Office'],
            'Status' => ['Active', 'Inactive'],
        ];

        return Excel::download(new MasterDataExport($headings, $rows, $dropdowns), 'investers-export-' . date('Y-m-d') . '.xlsx');
    }
}

