<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\BusinessIntelligence;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BusinessIntelligenceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(BusinessIntelligence::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    $buttons .= '<a href="'.route('admin.business-intelligences.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.business-intelligences.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.business-intelligences.index');
    }

    public function create()
    {
        return view('backend.business-intelligences.create');
    }

    public function store(Request $request)
    {
        BusinessIntelligence::create($this->validatedData($request));
        return redirect()->route('admin.business-intelligences.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.business-intelligences.edit', [
            'model' => BusinessIntelligence::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        BusinessIntelligence::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.business-intelligences.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        BusinessIntelligence::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $data = \App\Models\BusinessIntelligence::all()->map(function ($row) {
            return [
                $row->contact_id,
                $row->account_id,
                $row->account_name,
                $row->contact_name,
                $row->designation,
                $row->department,
                $row->mobile_number,
                $row->alternate_contact,
                $row->email_id,
                $row->contact_type,
                $row->last_contacted_date ? \Carbon\Carbon::parse($row->last_contacted_date)->format("Y-m-d") : null,
                $row->next_follow_up_date ? \Carbon\Carbon::parse($row->next_follow_up_date)->format("Y-m-d") : null,
                $row->contact_notes,
                $row->status ? "Active" : "Inactive"
            ];
        });
        
        $dropdowns = [
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Contact ID',
  1 => 'Account ID',
  2 => 'Account Name',
  3 => 'Contact Name',
  4 => 'Designation',
  5 => 'Department',
  6 => 'Mobile Number',
  7 => 'Alternate Contact',
  8 => 'Email ID',
  9 => 'Contact Type',
  10 => 'Last Contacted Date',
  11 => 'Next Follow-up Date',
  12 => 'Contact Notes',
  13 => 'Status',
),
            $data->toArray(),
            $dropdowns
        ), 'BusinessIntelligence-export.xlsx');
    }

    public function importTemplate()
    {
        $dropdowns = [
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Contact ID',
  1 => 'Account ID',
  2 => 'Account Name',
  3 => 'Contact Name',
  4 => 'Designation',
  5 => 'Department',
  6 => 'Mobile Number',
  7 => 'Alternate Contact',
  8 => 'Email ID',
  9 => 'Contact Type',
  10 => 'Last Contacted Date',
  11 => 'Next Follow-up Date',
  12 => 'Contact Notes',
  13 => 'Status',
),
            [],
            $dropdowns
        ), 'BusinessIntelligence-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $rows = \App\Support\MasterDataSpreadsheet::rows($request->file('import_file'));
        
        if ($rows->isEmpty()) return back()->with('error', 'Empty file');
        
        $validRows = [];
        foreach ($rows as $row) {
            $validRows[] = [
                'contact_id' => $row['contact_id'] ?? null,
                'account_id' => $row['account_id'] ?? null,
                'account_name' => $row['account_name'] ?? null,
                'contact_name' => $row['contact_name'] ?? null,
                'designation' => $row['designation'] ?? null,
                'department' => $row['department'] ?? null,
                'mobile_number' => $row['mobile_number'] ?? null,
                'alternate_contact' => $row['alternate_contact'] ?? null,
                'email_id' => $row['email_id'] ?? null,
                'contact_type' => $row['contact_type'] ?? null,
                'last_contacted_date' => !empty($row['last_contacted_date']) ? \Carbon\Carbon::parse($row['last_contacted_date'])->format('Y-m-d') : null,
                'next_follow_up_date' => !empty($row['next_follow_up_date']) ? \Carbon\Carbon::parse($row['next_follow_up_date'])->format('Y-m-d') : null,
                'contact_notes' => $row['contact_notes'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\BusinessIntelligence::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'contact_id' => 'nullable|string|max:255',
            'account_id' => 'nullable|string|max:255',
            'account_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:255',
            'alternate_contact' => 'nullable|string|max:255',
            'email_id' => 'nullable|email|max:255',
            'contact_type' => 'nullable|string|max:255',
            'last_contacted_date' => 'nullable|date',
            'next_follow_up_date' => 'nullable|date',
            'contact_notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);
    }
}