<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Invester;
use App\Models\InvesterFinancialDetail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterFinancialDetailController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', InvesterFinancialDetail::class);

        if ($request->ajax()) {
            $financials = InvesterFinancialDetail::with('invester')->latest();

            return DataTables::of($financials)
                ->addIndexColumn()
                ->addColumn('invester_name', function ($row) {
                    if (!$row->invester) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    return '<div><strong>' . e($row->invester->name) . '</strong><br><small class="text-muted">' . e($row->invester->invester_id) . '</small></div>';
                })
                ->editColumn('investment_amount', function ($row) {
                    return $row->investment_amount !== null
                        ? number_format((float) $row->investment_amount, 2)
                        : '<span class="text-muted">-</span>';
                })
                ->editColumn('equity_percentage', function ($row) {
                    return $row->equity_percentage !== null
                        ? number_format((float) $row->equity_percentage, 2) . '%'
                        : '<span class="text-muted">-</span>';
                })
                ->editColumn('investment_date', function ($row) {
                    return $row->investment_date
                        ? $row->investment_date->format('Y-m-d')
                        : '<span class="text-muted">-</span>';
                })
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', InvesterFinancialDetail::class)) {
                        $buttons .= '<a href="' . route('admin.invester-financial-details.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', InvesterFinancialDetail::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.invester-financial-details.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['invester_name', 'investment_amount', 'equity_percentage', 'investment_date', 'status', 'action'])
                ->make(true);
        }

        return view('backend.invester-financial-details.index');
    }

    public function create()
    {
        $this->authorize('create', InvesterFinancialDetail::class);

        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-financial-details.create', compact('investers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvesterFinancialDetail::class);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'investment_amount' => 'nullable|numeric|min:0',
            'equity_percentage' => 'nullable|numeric|between:0,100',
            'shares_units_held' => 'nullable|string|max:100',
            'investment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        InvesterFinancialDetail::create($validated);

        return redirect()->route('admin.invester-financial-details.index')->with('success', 'Financial & Investment Details record created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InvesterFinancialDetail::class);

        $financialDetail = InvesterFinancialDetail::findOrFail($id);
        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-financial-details.edit', compact('financialDetail', 'investers'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', InvesterFinancialDetail::class);

        $financialDetail = InvesterFinancialDetail::findOrFail($id);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'investment_amount' => 'nullable|numeric|min:0',
            'equity_percentage' => 'nullable|numeric|between:0,100',
            'shares_units_held' => 'nullable|string|max:100',
            'investment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $financialDetail->update($validated);

        return redirect()->route('admin.invester-financial-details.index')->with('success', 'Financial & Investment Details record updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', InvesterFinancialDetail::class);

        $financialDetail = InvesterFinancialDetail::findOrFail($id);
        $financialDetail->delete();

        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', InvesterFinancialDetail::class);

        $rows = InvesterFinancialDetail::with('invester')->get()->map(function ($row) {
            return [
                $row->invester?->invester_id ?? '-',
                $row->invester?->name ?? 'N/A',
                $row->investment_amount !== null ? number_format((float) $row->investment_amount, 2) : '',
                $row->equity_percentage !== null ? number_format((float) $row->equity_percentage, 2) . '%' : '',
                $row->shares_units_held,
                $row->investment_date ? $row->investment_date->format('Y-m-d') : '',
                $row->notes,
                $row->status ? 'Active' : 'Inactive',
                $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            ];
        })->toArray();

        $headings = [
            'Invester ID',
            'Invester Name',
            'Total Investment Amount / Committed Capital',
            'Equity Ownership Percentage (%)',
            'Number of Shares / Units Held',
            'Date of Investment',
            'Notes',
            'Status',
            'Created Date',
        ];

        $dropdowns = [
            'Status' => ['Active', 'Inactive'],
        ];

        return Excel::download(new MasterDataExport($headings, $rows, $dropdowns), 'invester-financial-details-' . date('Y-m-d') . '.xlsx');
    }
}

