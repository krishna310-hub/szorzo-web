<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Invester;
use App\Models\InvesterFinancialDetail;
use App\Models\InvesterLegalCompliance;
use App\Models\InvesterTeamManagement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && ($user->isInvestor() || (int) $user->role_id === 6) && !$user->isSuperAdmin()) {
            return redirect()->route('admin.investers.dashboard');
        }

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
                ->addColumn('account_manager_name', function ($row) {
                    $names = $row->account_manager_names;
                    return $names !== 'Unassigned' && !empty($names)
                        ? e($names)
                        : '<span class="text-muted">Unassigned</span>';
                })
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

    /**
     * Get external employees only for Account Manager / Point of Contact.
     */
    protected function getExternalAccountManagers()
    {
        return Employee::with(['mode', 'client'])
            ->orderBy('employee_name')
            ->get()
            ->filter(fn ($emp) => $emp->isExternal())
            ->values();
    }

    public function create()
    {
        $this->authorize('create', Invester::class);

        $suggestedId = Invester::nextInvesterId();
        $accountManagers = $this->getExternalAccountManagers();

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
            'account_manager_name' => 'nullable|string|max:255',
            'account_manager_email' => 'nullable|email|max:150',
            'account_manager_contact' => 'nullable|string|max:50',
            'account_manager_role' => 'nullable|string|max:100',
            'account_manager_id' => 'nullable|exists:employees,id',
            'account_manager_ids' => 'nullable|array',
            'account_manager_ids.*' => 'integer|exists:employees,id',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:150',
            'user_password' => 'nullable|string|max:255',
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

        $managerIds = $request->input('account_manager_ids');
        if (is_array($managerIds)) {
            $managerIds = array_values(array_filter(array_map('intval', $managerIds)));
        } elseif (!empty($validated['account_manager_id'])) {
            $managerIds = [(int) $validated['account_manager_id']];
        } else {
            $managerIds = [];
        }

        $primaryManagerId = !empty($managerIds) ? $managerIds[0] : null;

        $investerData = [
            'invester_id' => $validated['invester_id'],
            'name' => $validated['name'],
            'investor_type' => $validated['investor_type'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'primary_email' => $validated['primary_email'] ?? null,
            'alternate_email' => $validated['alternate_email'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_manager_name' => $request->input('account_manager_name'),
            'account_manager_email' => $request->input('account_manager_email'),
            'account_manager_contact' => $request->input('account_manager_contact'),
            'account_manager_role' => $request->input('account_manager_role'),
            'account_manager_id' => $primaryManagerId,
            'account_manager_ids' => !empty($managerIds) ? $managerIds : null,
            'portal_username' => $request->input('user_name'),
            'portal_password' => $request->input('user_password'),
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

        // Create or link User Account with Role ID 6 (Invester)
        if ($request->boolean('create_user_account') || $request->filled('user_email') || $request->filled('user_password') || $request->filled('user_name')) {
            $userEmail = trim($request->input('user_email') ?: ($invester->primary_email ?? ''));
            $userName = trim($request->input('user_name') ?: $invester->name);
            $userPass = $request->input('user_password');

            if (!empty($userEmail)) {
                Role::firstOrCreate(
                    ['id' => 6],
                    ['name' => 'Invester', 'access_level' => 'invester', 'status' => 1, 'its_default' => 1]
                );

                $userData = [
                    'name' => $userName,
                    'email' => $userEmail,
                    'role_id' => 6,
                    'ref' => $invester->invester_id,
                    'phone_number' => $invester->contact,
                    'is_active' => $invester->status ? 1 : 0,
                ];

                $plainPass = !empty($userPass) ? $userPass : 'Investor@123';
                $userData['password'] = Hash::make($plainPass);

                $user = User::where('email', $userEmail)->first();
                if ($user) {
                    $user->update($userData);
                } else {
                    $user = User::create($userData);
                }

                $invester->update([
                    'user_id' => $user->id,
                    'portal_username' => $userName,
                    'portal_password' => $plainPass,
                ]);
            }
        }

        return redirect()->route('admin.investers.index')->with('success', 'Investor ' . $invester->name . ' (' . $invester->invester_id . ') created successfully.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $isInvestorUser = $user && ($user->isInvestor() || (int) $user->role_id === 6);
        $isSuperAdmin = $user && ((int) $user->id === 1 || $user->isSuperAdmin());

        // For investor users, resolve their own profile
        if ($isInvestorUser && !$isSuperAdmin) {
            $linked = $user->linkedInvester();
            $invester = Invester::with([
                'accountManager',
                'teamManagements',
                'financialDetails',
                'legalCompliances',
                'deployments.employee',
                'deployments.client',
                'user',
            ])->where(function ($q) use ($id, $user) {
                $q->where('id', $id)
                    ->orWhere('invester_id', $id)
                    ->orWhere('user_id', $user->id);
            })->first() ?? $linked;

            if (!$invester) {
                abort(404, 'Investor profile not found.');
            }

            return view('backend.investers.show', compact('invester'));
        }

        $this->authorize('read', Invester::class);

        $invester = Invester::with([
            'accountManager',
            'teamManagements',
            'financialDetails',
            'legalCompliances',
            'deployments.employee',
            'deployments.client',
            'user',
        ])->where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('invester_id', $id);
        })->firstOrFail();

        return view('backend.investers.show', compact('invester'));
    }

    public function edit($id)
    {
        $this->authorize('edit', Invester::class);

        $invester = Invester::with([
            'accountManager',
            'teamManagements',
            'financialDetails',
            'legalCompliances',
            'deployments.employee',
            'deployments.client',
            'user',
        ])->findOrFail($id);
        $accountManagers = $this->getExternalAccountManagers();

        $assignedIds = (array) ($invester->account_manager_ids ?: ($invester->account_manager_id ? [$invester->account_manager_id] : []));
        foreach ($assignedIds as $aId) {
            if ($aId && !$accountManagers->contains('id', (int) $aId)) {
                $currentManager = Employee::with(['mode', 'client'])->find($aId);
                if ($currentManager) {
                    $accountManagers->push($currentManager);
                }
            }
        }

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
            'account_manager_name' => 'nullable|string|max:255',
            'account_manager_email' => 'nullable|email|max:150',
            'account_manager_contact' => 'nullable|string|max:50',
            'account_manager_role' => 'nullable|string|max:100',
            'account_manager_id' => 'nullable|exists:employees,id',
            'account_manager_ids' => 'nullable|array',
            'account_manager_ids.*' => 'integer|exists:employees,id',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:150',
            'user_password' => 'nullable|string|max:255',
            'internal_notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $managerIds = $request->input('account_manager_ids');
        if (is_array($managerIds)) {
            $managerIds = array_values(array_filter(array_map('intval', $managerIds)));
        } elseif (!empty($validated['account_manager_id'])) {
            $managerIds = [(int) $validated['account_manager_id']];
        } else {
            $managerIds = [];
        }

        $primaryManagerId = !empty($managerIds) ? $managerIds[0] : null;

        $data = [
            'invester_id' => $validated['invester_id'],
            'name' => $validated['name'],
            'investor_type' => $validated['investor_type'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'primary_email' => $validated['primary_email'] ?? null,
            'alternate_email' => $validated['alternate_email'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_manager_name' => $request->input('account_manager_name'),
            'account_manager_email' => $request->input('account_manager_email'),
            'account_manager_contact' => $request->input('account_manager_contact'),
            'account_manager_role' => $request->input('account_manager_role'),
            'account_manager_id' => $primaryManagerId,
            'account_manager_ids' => !empty($managerIds) ? $managerIds : null,
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

        // Update or create linked User Account (Role ID 6)
        if ($request->boolean('create_user_account') || $request->filled('user_email') || $request->filled('user_password') || $request->filled('user_name')) {
            $userEmail = trim($request->input('user_email') ?: ($invester->primary_email ?? ''));
            $userName = trim($request->input('user_name') ?: ($invester->portal_username ?: $invester->name));
            $userPass = $request->input('user_password');

            if (!empty($userEmail)) {
                Role::firstOrCreate(
                    ['id' => 6],
                    ['name' => 'Invester', 'access_level' => 'invester', 'status' => 1, 'its_default' => 1]
                );

                $existingUser = $invester->user_id ? User::find($invester->user_id) : User::where('email', $userEmail)->first();

                $userData = [
                    'name' => $userName,
                    'email' => $userEmail,
                    'role_id' => 6,
                    'ref' => $invester->invester_id,
                    'phone_number' => $invester->contact,
                    'is_active' => $invester->status ? 1 : 0,
                ];

                if (!empty($userPass)) {
                    $userData['password'] = Hash::make($userPass);
                    $data['portal_password'] = $userPass;
                }

                $data['portal_username'] = $userName;

                if ($existingUser) {
                    $existingUser->update($userData);
                    $user = $existingUser;
                } else {
                    if (empty($userData['password'])) {
                        $defaultPass = 'Investor@123';
                        $userData['password'] = Hash::make($defaultPass);
                        $data['portal_password'] = $defaultPass;
                    }
                    $user = User::create($userData);
                }

                $data['user_id'] = $user->id;
            }
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
                $row->account_manager_names,
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

