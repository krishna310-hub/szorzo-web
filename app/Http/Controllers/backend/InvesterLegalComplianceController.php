<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Invester;
use App\Models\InvesterLegalCompliance;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvesterLegalComplianceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', InvesterLegalCompliance::class);

        if ($request->ajax()) {
            $compliances = InvesterLegalCompliance::with('invester')->latest();

            return DataTables::of($compliances)
                ->addIndexColumn()
                ->addColumn('invester_name', function ($row) {
                    if (!$row->invester) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    return '<div><strong>' . e($row->invester->name) . '</strong><br><small class="text-muted">' . e($row->invester->invester_id) . '</small></div>';
                })
                ->editColumn('kyc_aml_status', function ($row) {
                    $class = match ($row->kyc_aml_status) {
                        'Verified' => 'bg-success-subtle text-success',
                        'Rejected' => 'bg-danger-subtle text-danger',
                        default => 'bg-warning-subtle text-warning',
                    };
                    return '<span class="badge ' . $class . '">' . e($row->kyc_aml_status) . '</span>';
                })
                ->addColumn('document_link', function ($row) {
                    if ($row->nda_signed_doc && file_exists(public_path($row->nda_signed_doc))) {
                        return '<a href="' . asset($row->nda_signed_doc) . '" target="_blank" class="btn btn-sm btn-soft-info" title="View Document"><i class="ri-attachment-line me-1"></i> View Agreement</a>';
                    }
                    return '<span class="text-muted">No attachment</span>';
                })
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', InvesterLegalCompliance::class)) {
                        $buttons .= '<a href="' . route('admin.invester-legal-compliances.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', InvesterLegalCompliance::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.invester-legal-compliances.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['invester_name', 'kyc_aml_status', 'document_link', 'status', 'action'])
                ->make(true);
        }

        return view('backend.invester-legal-compliances.index');
    }

    public function create()
    {
        $this->authorize('create', InvesterLegalCompliance::class);

        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-legal-compliances.create', compact('investers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvesterLegalCompliance::class);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'tax_id_number' => 'nullable|string|max:100',
            'kyc_aml_status' => 'required|in:Verified,Pending,Rejected',
            'nda_signed_doc' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg,jpeg|max:10240',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'invester_id' => $validated['invester_id'],
            'tax_id_number' => $validated['tax_id_number'] ?? null,
            'kyc_aml_status' => $validated['kyc_aml_status'],
            'notes' => $validated['notes'] ?? null,
            'status' => (bool) $validated['status'],
        ];

        if ($request->hasFile('nda_signed_doc')) {
            $file = $request->file('nda_signed_doc');
            $dir = public_path('uploads/investers/agreements');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['nda_signed_doc'] = 'uploads/investers/agreements/' . $filename;
        }

        InvesterLegalCompliance::create($data);

        return redirect()->route('admin.invester-legal-compliances.index')->with('success', 'Legal, Compliance & Governance record created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', InvesterLegalCompliance::class);

        $legalCompliance = InvesterLegalCompliance::findOrFail($id);
        $investers = Invester::orderBy('name')->get();

        return view('backend.invester-legal-compliances.edit', compact('legalCompliance', 'investers'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', InvesterLegalCompliance::class);

        $legalCompliance = InvesterLegalCompliance::findOrFail($id);

        $validated = $request->validate([
            'invester_id' => 'required|exists:investers,id',
            'tax_id_number' => 'nullable|string|max:100',
            'kyc_aml_status' => 'required|in:Verified,Pending,Rejected',
            'nda_signed_doc' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg,jpeg|max:10240',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'invester_id' => $validated['invester_id'],
            'tax_id_number' => $validated['tax_id_number'] ?? null,
            'kyc_aml_status' => $validated['kyc_aml_status'],
            'notes' => $validated['notes'] ?? null,
            'status' => (bool) $validated['status'],
        ];

        if ($request->hasFile('nda_signed_doc')) {
            if ($legalCompliance->nda_signed_doc && file_exists(public_path($legalCompliance->nda_signed_doc))) {
                @unlink(public_path($legalCompliance->nda_signed_doc));
            }

            $file = $request->file('nda_signed_doc');
            $dir = public_path('uploads/investers/agreements');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['nda_signed_doc'] = 'uploads/investers/agreements/' . $filename;
        }

        $legalCompliance->update($data);

        return redirect()->route('admin.invester-legal-compliances.index')->with('success', 'Legal, Compliance & Governance record updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', InvesterLegalCompliance::class);

        $legalCompliance = InvesterLegalCompliance::findOrFail($id);
        if ($legalCompliance->nda_signed_doc && file_exists(public_path($legalCompliance->nda_signed_doc))) {
            @unlink(public_path($legalCompliance->nda_signed_doc));
        }
        $legalCompliance->delete();

        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', InvesterLegalCompliance::class);

        $rows = InvesterLegalCompliance::with('invester')->get()->map(function ($row) {
            return [
                $row->invester?->invester_id ?? '-',
                $row->invester?->name ?? 'N/A',
                $row->tax_id_number,
                $row->kyc_aml_status,
                $row->nda_signed_doc ? url($row->nda_signed_doc) : 'No attachment',
                $row->notes,
                $row->status ? 'Active' : 'Inactive',
                $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            ];
        })->toArray();

        $headings = [
            'Invester ID',
            'Invester Name',
            'Tax Identification Number (TIN) / PAN / SSN',
            'KYC / AML Status',
            'NDA / Legal Agreements Signed Document Link',
            'Notes',
            'Status',
            'Created Date',
        ];

        $dropdowns = [
            'KYC / AML Status' => ['Verified', 'Pending', 'Rejected'],
            'Status' => ['Active', 'Inactive'],
        ];

        return Excel::download(new MasterDataExport($headings, $rows, $dropdowns), 'invester-legal-compliance-' . date('Y-m-d') . '.xlsx');
    }
}

