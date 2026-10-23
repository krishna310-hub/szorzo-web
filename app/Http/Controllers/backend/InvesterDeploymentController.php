<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Invester;
use App\Models\InvesterDeployment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterDeploymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', InvesterDeployment::class);

        $user = auth()->user();
        $isInvestorUser = $user->isInvestor() || (int) $user->role_id === 6;
        $linkedInvester = $isInvestorUser ? $user->linkedInvester() : null;

        if ($request->ajax()) {
            $query = InvesterDeployment::with(['invester', 'employee', 'client'])->latest();

            if ($isInvestorUser && $linkedInvester) {
                $query->where('invester_id', $linkedInvester->id);
            } elseif ($request->filled('invester_id')) {
                $query->where('invester_id', $request->invester_id);
            }

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->filled('mode_of_engagement')) {
                $query->where('mode_of_engagement', $request->mode_of_engagement);
            }

            if ($request->filled('status')) {
                $query->where('status', (bool) $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('invester_name', function ($row) {
                    if (!$row->invester) {
                        return '-';
                    }
                    return '<a href="' . route('admin.investers.show', $row->invester->id) . '" class="fw-semibold text-primary">'
                        . e($row->invester->name) . ' <span class="badge bg-primary-subtle text-primary fs-11">' . e($row->invester->invester_id) . '</span></a>';
                })
                ->addColumn('employee_name', function ($row) {
                    $assigned = $row->assigned_employees;
                    if ($assigned->count() > 1) {
                        return '<div><span class="badge bg-primary-subtle text-primary mb-1">' . $assigned->count() . ' External Employees</span><br><small class="text-muted">' . e($assigned->pluck('employee_name')->take(2)->implode(', ')) . ($assigned->count() > 2 ? ' +' . ($assigned->count() - 2) . ' more' : '') . '</small></div>';
                    } elseif ($assigned->count() === 1) {
                        $emp = $assigned->first();
                        $no = $emp->employee_no ?: ('SZ' . str_pad($emp->id, 3, '0', STR_PAD_LEFT));
                        return '<div><strong>' . e($emp->employee_name) . '</strong><br><small class="text-muted">' . e($no) . '</small></div>';
                    } elseif ($row->employee) {
                        $no = $row->employee->employee_no ?: ('SZ' . str_pad($row->employee->id, 3, '0', STR_PAD_LEFT));
                        return '<div><strong>' . e($row->employee->employee_name) . '</strong><br><small class="text-muted">' . e($no) . '</small></div>';
                    }
                    return '<span class="text-muted">Unassigned</span>';
                })
                ->addColumn('client_name', function ($row) {
                    $assignedClients = $row->assigned_clients;
                    if ($assignedClients->count() > 1) {
                        return '<div><span class="badge bg-info-subtle text-info mb-1">' . $assignedClients->count() . ' Clients</span><br><small class="text-muted">' . e($assignedClients->pluck('client')->take(2)->implode(', ')) . ($assignedClients->count() > 2 ? ' +' . ($assignedClients->count() - 2) . ' more' : '') . '</small></div>';
                    } elseif ($assignedClients->count() === 1) {
                        return e($assignedClients->first()->client);
                    } elseif ($row->client) {
                        return e($row->client->client);
                    }
                    return '-';
                })
                ->addColumn('mode_badge', function ($row) {
                    return '<span class="badge bg-info-subtle text-info">' . e($row->mode_of_engagement ?: 'Contract') . '</span>';
                })
                ->addColumn('rate_card_formatted', function ($row) {
                    $amount = (float) ($row->employee_ctc > 0 ? $row->employee_ctc : $row->approved_rate_card);
                    $html = '₹ ' . number_format($amount, 2);
                    if ($row->billing_percentage !== null) {
                        $html .= '<br><small class="text-muted">Billing: ' . (float) $row->billing_percentage . '%</small>';
                    }
                    return $html;
                })
                ->addColumn('margin_in_rupees', function ($row) {
                    $html = '<span class="badge bg-warning text-dark fs-12 fw-bold">₹ ' . number_format((float) $row->margin_amount, 2) . '</span>';
                    if ($row->tds_amount > 0) {
                        $html .= '<br><small class="text-muted">TDS (' . (float) $row->tds_percentage . '%): ₹' . number_format((float) $row->tds_amount, 2) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('commission_split', function ($row) {
                    $sz = 'SZ (' . (float) $row->szorzo_share_percentage . '%): ₹' . number_format((float) $row->szorzo_commission_amount, 2);
                    $inv = 'Investor (' . (float) $row->investor_share_percentage . '%): ₹' . number_format((float) $row->investor_commission_amount, 2);
                    return '<small class="d-block text-primary fw-medium">' . e($sz) . '</small><small class="d-block text-success fw-bold">' . e($inv) . '</small>';
                })
                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success-subtle text-success">Active</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', InvesterDeployment::class)) {
                        $buttons .= '<a href="' . route('admin.invester-deployments.edit', $row->id) . '" class="text-info fs-4 me-2" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', InvesterDeployment::class)) {
                        $buttons .= '<a href="javascript:void(0)" class="text-danger fs-4 delete-btn" data-id="' . $row->id . '" title="Delete"><i class="bx bx-trash"></i></a>';
                    }
                    return $buttons ?: '-';
                })
                ->rawColumns(['invester_name', 'employee_name', 'mode_badge', 'rate_card_formatted', 'margin_in_rupees', 'commission_split', 'status', 'action'])
                ->make(true);
        }

        $investers = Invester::where('status', true)->orderBy('name')->get();
        $clients = Client::where('status', true)->orderBy('client')->get();

        return view('backend.invester-deployments.index', compact('investers', 'clients', 'isInvestorUser', 'linkedInvester'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', InvesterDeployment::class);

        $investers = Invester::where('status', true)->orderBy('name')->get();
        $clients = Client::with('billing')->where('status', true)->orderBy('client')->get();
        $preselectedInvesterId = $request->query('invester_id') ? (int) $request->query('invester_id') : null;

        // Exclude employees already deployed on another investor
        $excludedEmployeeIds = InvesterDeployment::getDeployedEmployeeIds($preselectedInvesterId);

        $allEmployees = Employee::with('client')->orderBy('employee_name')->get();
        $employees = $allEmployees->filter(fn ($emp) => $emp->isExternal() && !in_array($emp->id, $excludedEmployeeIds, true))->values();

        return view('backend.invester-deployments.create', compact('investers', 'employees', 'clients', 'preselectedInvesterId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvesterDeployment::class);

        $validated = $this->validateDeployment($request);

        $deployment = InvesterDeployment::create($validated);

        $employeeName = $deployment->employee_names ?: ($deployment->employee?->employee_name ?? 'External Employees');

        return redirect()->route('admin.invester-deployments.index')
            ->with('success', 'Invest Module Deployment for ' . $employeeName . ' created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InvesterDeployment::class);

        $deployment = InvesterDeployment::findOrFail($id);
        $investers = Invester::where('status', true)->orderBy('name')->get();
        $clients = Client::with('billing')->where('status', true)->orderBy('client')->get();

        // Exclude employees already deployed on another investor (keep current deployment employees available)
        $excludedEmployeeIds = InvesterDeployment::getDeployedEmployeeIds($deployment->invester_id, $deployment->id);

        $allEmployees = Employee::with('client')->orderBy('employee_name')->get();
        $employees = $allEmployees->filter(fn ($emp) => $emp->isExternal() && !in_array($emp->id, $excludedEmployeeIds, true))->values();

        return view('backend.invester-deployments.edit', compact('deployment', 'investers', 'employees', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', InvesterDeployment::class);

        $deployment = InvesterDeployment::findOrFail($id);
        $validated = $this->validateDeployment($request);

        $deployment->update($validated);

        return redirect()->route('admin.invester-deployments.index')
            ->with('success', 'Invest Module Deployment updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', InvesterDeployment::class);

        $deployment = InvesterDeployment::findOrFail($id);
        $deployment->delete();

        return response()->json(['status' => true, 'message' => 'Invest Module Deployment deleted successfully.']);
    }

    /**
     * AJAX endpoint to return external employees and auto-calculated billing/TDS for selected client(s).
     */
    public function getClientEmployees(Request $request)
    {
        $rawClientIds = $request->input('client_ids');
        if (is_string($rawClientIds)) {
            $clientIds = array_filter(explode(',', $rawClientIds));
        } elseif (is_array($rawClientIds)) {
            $clientIds = array_filter($rawClientIds);
        } else {
            $clientIds = [];
        }

        if (empty($clientIds) && $request->filled('client_id')) {
            $clientIds = [(int) $request->client_id];
        }

        $clientIds = array_map('intval', array_values($clientIds));
        $investerId = $request->filled('invester_id') ? (int) $request->invester_id : null;
        $deploymentId = $request->filled('deployment_id') ? (int) $request->deployment_id : null;

        $clients = Client::with('billing')->whereIn('id', $clientIds)->get();

        $billingPercentages = [];
        foreach ($clients as $client) {
            $billingVal = $client->billing ? (float) $client->billing->value : null;
            if ($billingVal !== null) {
                $billingPercentages[$client->id] = $billingVal;
            }
        }

        // Sum the billing value percentages of all selected clients
        $totalBilling = count($billingPercentages) > 0
            ? round(array_sum($billingPercentages), 2)
            : 0;

        // Exclude employees already chosen on another investor
        $excludedEmployeeIds = InvesterDeployment::getDeployedEmployeeIds($investerId, $deploymentId);

        // External contract employees ONLY for the selected client(s) and not chosen on another investor
        $employees = Employee::with(['client.billing'])
            ->whereIn('client_id', $clientIds)
            ->whereNotIn('id', $excludedEmployeeIds)
            ->where(function ($q) {
                $q->where('employment_type', Employee::EMPLOYMENT_TYPE_EXTERNAL)
                  ->orWhereNull('employment_type');
            })
            ->get()
            ->filter(fn ($emp) => $emp->isExternal())
            ->values()
            ->map(function ($emp) {
                $no = $emp->employee_no ?: ('SZ' . str_pad($emp->id, 3, '0', STR_PAD_LEFT));
                $salaryData = $emp->getContractReportSalary();
                $salary = (float) $salaryData['salary'];
                $durationInfo = $emp->getContractDurationInfo();
                $durLabel = !empty($durationInfo['duration']) ? ' [' . $durationInfo['duration'] . ']' : '';
                $billingPct = $emp->getClientRequirementBillingPercentage();
                $billingLabel = $billingPct !== null ? ' [' . $billingPct . '% Billing]' : '';

                return [
                    'id' => $emp->id,
                    'client_id' => $emp->client_id,
                    'client_name' => $emp->client?->client,
                    'name' => $emp->employee_name,
                    'employee_name' => $emp->employee_name,
                    'employee_no' => $no,
                    'designation' => $emp->designation,
                    'monthly_gross' => (float) ($emp->monthly_gross ?? 0),
                    'salary' => $salary,
                    'ctc' => $salary,
                    'present_days' => $salaryData['present_days'],
                    'absent_days' => $salaryData['absent_days'],
                    'days_in_month' => $salaryData['days_in_month'],
                    'source' => $salaryData['source'],
                    'contract_duration' => $durationInfo['duration'],
                    'contract_duration_detail' => $durationInfo['detail'],
                    'contract_from' => $durationInfo['from_date'],
                    'contract_to' => $durationInfo['to_date'],
                    'billing_percentage' => $billingPct,
                    'salary_formatted' => number_format($salary, 2),
                    'label' => $emp->employee_name . ' (' . $no . ($emp->designation ? ' - ' . $emp->designation : '') . ')' . ($salary > 0 ? ' - ₹' . number_format($salary, 2) . '/mo' : '') . $billingLabel . $durLabel . ($emp->client ? ' [' . $emp->client->client . ']' : ''),
                ];
            });

        return response()->json([
            'status' => true,
            'clients' => $clients->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->client,
            ]),
            'tds_percentage' => 10.00,
            'employees' => $employees,
            'total_external_employees' => $employees->count(),
        ]);
    }

    protected function validateDeployment(Request $request): array
    {
        $data = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'nullable|exists:employees,id',
            'client_id' => 'nullable|exists:clients,id',
            'client_ids' => 'nullable|array',
            'client_ids.*' => 'nullable|exists:clients,id',
            'mode_of_engagement' => 'nullable|string|max:100',
            'contract_duration' => 'nullable|string|max:100',
            'approved_rate_card' => 'nullable|numeric|min:0',
            'employee_ctc' => 'nullable|numeric|min:0',
            'billing_percentage' => 'nullable|numeric|between:0,100',
            'margin_percentage' => 'nullable|numeric|between:0,100',
            'tds_percentage' => 'nullable|numeric|between:0,100',
            'tds_amount' => 'nullable|numeric|min:0',
            'margin_amount' => 'nullable|numeric|min:0',
            'szorzo_share_percentage' => 'nullable|numeric|between:0,100',
            'investor_share_percentage' => 'nullable|numeric|between:0,100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        // Process client_ids and client_id
        $clientIds = array_values(array_filter((array) ($request->input('client_ids') ?? [])));
        if (empty($clientIds) && !empty($data['client_id'])) {
            $clientIds = [(int) $data['client_id']];
        }
        $data['client_ids'] = !empty($clientIds) ? array_map('intval', $clientIds) : null;
        $data['client_id'] = $clientIds[0] ?? ($data['client_id'] ?? null);

        // Process employee_ids and employee_id
        $employeeIds = array_values(array_filter((array) ($request->input('employee_ids') ?? [])));
        if (empty($employeeIds) && !empty($data['employee_id'])) {
            $employeeIds = [(int) $data['employee_id']];
        }
        $data['employee_ids'] = !empty($employeeIds) ? array_map('intval', $employeeIds) : null;
        $data['employee_id'] = $employeeIds[0] ?? ($data['employee_id'] ?? null);

        $data['mode_of_engagement'] = !empty($data['mode_of_engagement']) ? $data['mode_of_engagement'] : 'Contract';
        $data['employee_ctc'] = (float) ($data['employee_ctc'] ?? 0);
        $data['approved_rate_card'] = (float) ($data['approved_rate_card'] ?? $data['employee_ctc']);

        // Auto-compute billing percentage from chosen employees' client requirements if not explicitly provided
        if (!isset($data['billing_percentage']) || $data['billing_percentage'] === '' || $data['billing_percentage'] === null) {
            if (!empty($data['employee_ids'])) {
                $selectedEmps = Employee::with(['client.billing'])->whereIn('id', $data['employee_ids'])->get();
                $sumBilling = 0;
                foreach ($selectedEmps as $emp) {
                    $bVal = $emp->getClientRequirementBillingPercentage();
                    if ($bVal !== null && $bVal > 0) {
                        $sumBilling += $bVal;
                    }
                }
                if ($sumBilling > 0) {
                    $data['billing_percentage'] = round($sumBilling, 2);
                }
            }
        } else {
            $data['billing_percentage'] = (float) $data['billing_percentage'];
        }

        $data['margin_percentage'] = isset($data['margin_percentage']) && $data['margin_percentage'] !== ''
            ? (float) $data['margin_percentage']
            : ($data['billing_percentage'] ?? 15.0);
        $data['tds_percentage'] = isset($data['tds_percentage']) && $data['tds_percentage'] !== '' ? (float) $data['tds_percentage'] : 10.0;
        $data['szorzo_share_percentage'] = isset($data['szorzo_share_percentage']) && $data['szorzo_share_percentage'] !== '' ? (float) $data['szorzo_share_percentage'] : 70.0;
        $data['investor_share_percentage'] = 100.0 - $data['szorzo_share_percentage'];

        $base = (float) ($data['employee_ctc'] > 0 ? $data['employee_ctc'] : $data['approved_rate_card']);

        // Compute Margin in Rupees if not manually provided: Base (Employee CTC) * (Margin % / 100)
        if (empty($data['margin_amount']) || (float) $data['margin_amount'] <= 0) {
            $data['margin_amount'] = round($base * ($data['margin_percentage'] / 100), 2);
        } else {
            $data['margin_amount'] = (float) $data['margin_amount'];
        }

        // TDS deduction calculation on base (Employee CTC)
        $data['tds_amount'] = round($base * ($data['tds_percentage'] / 100), 2);

        $data['szorzo_commission_amount'] = round($data['margin_amount'] * ($data['szorzo_share_percentage'] / 100), 2);
        $data['investor_commission_amount'] = round($data['margin_amount'] * ($data['investor_share_percentage'] / 100), 2);
        $data['status'] = (bool) $data['status'];

        return $data;
    }

    public function export()
    {
        $this->authorize('read', InvesterDeployment::class);

        $rows = InvesterDeployment::with(['invester', 'employee', 'client'])->get()->map(function ($row) {
            return [
                $row->invester?->name ?: '-',
                $row->employee_names ?: ($row->employee?->employee_name ?: '-'),
                $row->client_names ?: ($row->client?->client ?: '-'),
                $row->contract_duration,
                $row->approved_rate_card,
                $row->employee_ctc,
                $row->billing_percentage !== null ? $row->billing_percentage : '-',
                $row->margin_percentage,
                $row->tds_percentage,
                $row->tds_amount,
                $row->margin_amount,
                $row->szorzo_share_percentage,
                $row->szorzo_commission_amount,
                $row->investor_share_percentage,
                $row->investor_commission_amount,
                $row->status ? 'Active' : 'Inactive',
                $row->created_at ? $row->created_at->format('Y-m-d') : '-',
            ];
        })->toArray();

        $headings = [
            'Invester',
            'Employee Name(s)',
            'Deployed To (Client(s))',
            'Contract Duration',
            'Approved Rate Card (INR)',
            'Employee CTC (INR)',
            'Billing %',
            'Margin %',
            'TDS %',
            'TDS Amount (INR)',
            'Margin In Rupees',
            'SZ Share %',
            'SZ Commission (INR)',
            'Investor Share %',
            'Investor Commission (INR)',
            'Status',
            'Created Date',
        ];

        return Excel::download(new MasterDataExport($headings, $rows), 'invest-deployments-export-' . date('Y-m-d') . '.xlsx');
    }
}

