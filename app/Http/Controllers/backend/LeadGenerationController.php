<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\LeadGeneration;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeadGenerationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(LeadGeneration::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    $buttons .= '<a href="'.route('admin.lead-generations.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.lead-generations.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.lead-generations.index');
    }

    public function create()
    {
        return view('backend.lead-generations.create');
    }

    public function store(Request $request)
    {
        LeadGeneration::create($this->validatedData($request));
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.lead-generations.edit', [
            'model' => LeadGeneration::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        LeadGeneration::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        LeadGeneration::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $data = \App\Models\LeadGeneration::all()->map(function ($row) {
            return collect($row->toArray())->only(['id', 'status', 'created_at'])->merge([
                'status' => $row->status ? 'Active' : 'Inactive'
            ])->values()->toArray();
        });
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Account ID',
  1 => 'Account/Lead Source',
  2 => 'Account Name (Display)',
  3 => 'Industry',
  4 => 'Sub Industry',
  5 => 'Website URL',
  6 => 'Country',
  7 => 'Region',
  8 => 'State',
  9 => 'City',
  10 => 'Status',
), $data->toArray()), 'LeadGeneration-export.xlsx');
    }

    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Account ID',
  1 => 'Account/Lead Source',
  2 => 'Account Name (Display)',
  3 => 'Industry',
  4 => 'Sub Industry',
  5 => 'Website URL',
  6 => 'Country',
  7 => 'Region',
  8 => 'State',
  9 => 'City',
  10 => 'Status',
), []), 'LeadGeneration-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $rows = \App\Support\MasterDataSpreadsheet::rows($request->file('import_file'));
        
        if ($rows->isEmpty()) return back()->with('error', 'Empty file');
        
        $validRows = [];
        foreach ($rows as $row) {
            $validRows[] = [
                'account_id' => $row['account_id'] ?? null,
                'account_source' => $row['account_lead_source'] ?? null,
                'account_name' => $row['account_name_display'] ?? $row['account_name'] ?? '',
                'industry' => $row['industry'] ?? null,
                'sub_industry' => $row['sub_industry'] ?? null,
                'website_url' => $row['website_url'] ?? null,
                'country_of_origin' => $row['country'] ?? null,
                'region' => $row['region'] ?? null,
                'state' => $row['state'] ?? null,
                'city' => $row['city'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0,
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\LeadGeneration::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'account_id' => 'nullable|string|max:255',
            'account_source' => 'nullable|string|max:255',
            'account_name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'sub_industry' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'registered_address' => 'nullable|string',
            'pin_code' => 'nullable|string|max:20',
            'ownership_type' => 'nullable|string|max:255',
            'registration_id' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'account_owner' => 'nullable|string|max:255',
            'relationship_manager' => 'nullable|string|max:255',
            'customer_since' => 'nullable|string|max:255',
            'account_created_date' => 'nullable|date',
            'last_updated_date' => 'nullable|date',
            'status' => 'required|boolean',
        ]);
    }
}