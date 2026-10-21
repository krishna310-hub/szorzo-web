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
                    if (!$row->employee) {
                        return '<span class="text-muted">Unassigned</span>';
                    }
                    $no = $row->employee->employee_no ?: ('SZ' . str_pad($row->employee->id, 3, '0', STR_PAD_LEFT));
                    return '<div><strong>' . e($row->employee->employee_name) . '</strong><br><small class="text-muted">' . e($no) . '</small></div>';
                })
                ->addColumn('client_name', function ($row) {
                    return $row->client ? e($row->client->client) : '-';
                })
                ->addColumn('mode_badge', function ($row) {
                    return '<span class="badge bg-info-subtle text-info">' . e($row->mode_of_engagement ?: 'Contract') . '</span>';
                })
                ->addColumn('rate_card_formatted', function ($row) {
                    return '₹ ' . number_format((float) $row->approved_rate_card, 2);
                })
                ->addColumn('margin_in_rupees', function ($row) {
                    return '<span class="badge bg-warning text-dark fs-12 fw-bold">₹ ' . number_format((float) $row->margin_amount, 2) . '</span>';
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
                ->rawColumns(['invester_name', 'employee_name', 'mode_badge', 'margin_in_rupees', 'commission_split', 'status', 'action'])
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
        $employees = Employee::orderBy('employee_name')->get();
        $clients = Client::where('status', true)->orderBy('client')->get();
        $preselectedInvesterId = $request->query('invester_id');

        return view('backend.invester-deployments.create', compact('investers', 'employees', 'clients', 'preselectedInvesterId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvesterDeployment::class);

        $validated = $this->validateDeployment($request);

        $deployment = InvesterDeployment::create($validated);

        return redirect()->route('admin.invester-deployments.index')
            ->with('success', 'Invest Module Deployment for ' . ($deployment->employee?->employee_name ?? 'Employee') . ' created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InvesterDeployment::class);

        $deployment = InvesterDeployment::findOrFail($id);
        $investers = Invester::where('status', true)->orderBy('name')->get();
        $employees = Employee::orderBy('employee_name')->get();
        $clients = Client::where('status', true)->orderBy('client')->get();

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

    protected function validateDeployment(Request $request): array
    {
        $data = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'client_id' => 'nullable|exists:clients,id',
            'mode_of_engagement' => 'required|string|max:100',
            'contract_duration' => 'nullable|string|max:100',
            'approved_rate_card' => 'nullable|numeric|min:0',
            'employee_ctc' => 'nullable|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|between:0,100',
            'tds_percentage' => 'nullable|numeric|between:0,100',
            'margin_amount' => 'nullable|numeric|min:0',
            'szorzo_share_percentage' => 'nullable|numeric|between:0,100',
            'investor_share_percentage' => 'nullable|numeric|between:0,100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $data['approved_rate_card'] = (float) ($data['approved_rate_card'] ?? 0);
        $data['employee_ctc'] = (float) ($data['employee_ctc'] ?? 0);
        $data['margin_percentage'] = isset($data['margin_percentage']) && $data['margin_percentage'] !== '' ? (float) $data['margin_percentage'] : 15.0;
        $data['tds_percentage'] = isset($data['tds_percentage']) && $data['tds_percentage'] !== '' ? (float) $data['tds_percentage'] : 10.0;
        $data['szorzo_share_percentage'] = isset($data['szorzo_share_percentage']) && $data['szorzo_share_percentage'] !== '' ? (float) $data['szorzo_share_percentage'] : 70.0;
        $data['investor_share_percentage'] = 100.0 - $data['szorzo_share_percentage'];

        // Compute Margin in Rupees if not manually provided
        if (empty($data['margin_amount']) || (float) $data['margin_amount'] <= 0) {
            $data['margin_amount'] = round($data['approved_rate_card'] * ($data['margin_percentage'] / 100), 2);
        } else {
            $data['margin_amount'] = (float) $data['margin_amount'];
        }

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
                $row->employee?->employee_name ?: '-',
                $row->client?->client ?: '-',
                $row->mode_of_engagement,
                $row->contract_duration,
                $row->approved_rate_card,
                $row->employee_ctc,
                $row->margin_percentage,
                $row->tds_percentage,
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
            'Employee Name',
            'Deployed To (Client)',
            'Mode of Engagement',
            'Contract Duration',
            'Approved Rate Card (INR)',
            'Employee CTC (INR)',
            'Margin %',
            'TDS %',
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

