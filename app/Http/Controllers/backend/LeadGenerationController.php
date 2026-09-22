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
            return [
                $row->account_id,
                $row->account_source,
                $row->account_name,
                $row->industry,
                $row->sub_industry,
                $row->website_url,
                $row->country_of_origin,
                $row->region,
                $row->state,
                $row->city,
                $row->pin_code,
                $row->registered_address,
                $row->ownership_type,
                $row->registration_id,
                $row->gstin,
                $row->account_owner,
                $row->relationship_manager,
                $row->customer_since ? \Carbon\Carbon::parse($row->customer_since)->format("Y-m-d") : null,
                $row->account_created_date ? \Carbon\Carbon::parse($row->account_created_date)->format("Y-m-d") : null,
                $row->last_updated_date ? \Carbon\Carbon::parse($row->last_updated_date)->format("Y-m-d") : null,
                $row->status ? "Active" : "Inactive"
            ];
        });
        
        $dropdowns = [
            'Industry' => \App\Models\Division::where("status", 1)->pluck("name")->toArray() ?: ["-- No Data --"],
            'Country of Origin' => \App\Models\Country::pluck("name")->toArray() ?: ["-- No Data --"],
            'State' => \App\Models\State::pluck("name")->toArray() ?: ["-- No Data --"],
            'City' => \App\Models\City::pluck("name")->toArray() ?: ["-- No Data --"],
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Account ID',
  1 => 'Account / Lead Source',
  2 => 'Account Name (Display)',
  3 => 'Industry',
  4 => 'Sub Industry',
  5 => 'Website URL',
  6 => 'Country of Origin',
  7 => 'Region',
  8 => 'State',
  9 => 'City',
  10 => 'PIN Code',
  11 => 'Registered Address',
  12 => 'Ownership Type',
  13 => 'Registration / Entity ID',
  14 => 'GSTIN / Tax ID',
  15 => 'Account Owner',
  16 => 'Relationship Manager',
  17 => 'Customer Since',
  18 => 'Account Created Date',
  19 => 'Last Updated Date',
  20 => 'Status',
),
            $data->toArray(),
            $dropdowns
        ), 'LeadGenerations-export.xlsx');
    }

    public function importTemplate()
    {
        $dropdowns = [
            'Industry' => \App\Models\Division::where("status", 1)->pluck("name")->toArray() ?: ["-- No Data --"],
            'Country of Origin' => \App\Models\Country::pluck("name")->toArray() ?: ["-- No Data --"],
            'State' => \App\Models\State::pluck("name")->toArray() ?: ["-- No Data --"],
            'City' => \App\Models\City::pluck("name")->toArray() ?: ["-- No Data --"],
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Account ID',
  1 => 'Account / Lead Source',
  2 => 'Account Name (Display)',
  3 => 'Industry',
  4 => 'Sub Industry',
  5 => 'Website URL',
  6 => 'Country of Origin',
  7 => 'Region',
  8 => 'State',
  9 => 'City',
  10 => 'PIN Code',
  11 => 'Registered Address',
  12 => 'Ownership Type',
  13 => 'Registration / Entity ID',
  14 => 'GSTIN / Tax ID',
  15 => 'Account Owner',
  16 => 'Relationship Manager',
  17 => 'Customer Since',
  18 => 'Account Created Date',
  19 => 'Last Updated Date',
  20 => 'Status',
),
            [],
            $dropdowns
        ), 'LeadGenerations-template.xlsx');
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
                'account_name' => $row['account_name_display'] ?? null,
                'industry' => $row['industry'] ?? null,
                'sub_industry' => $row['sub_industry'] ?? null,
                'website_url' => $row['website_url'] ?? null,
                'country_of_origin' => $row['country_of_origin'] ?? null,
                'region' => $row['region'] ?? null,
                'state' => $row['state'] ?? null,
                'city' => $row['city'] ?? null,
                'pin_code' => $row['pin_code'] ?? null,
                'registered_address' => $row['registered_address'] ?? null,
                'ownership_type' => $row['ownership_type'] ?? null,
                'registration_id' => $row['registration_entity_id'] ?? null,
                'gstin' => $row['gstin_tax_id'] ?? null,
                'account_owner' => $row['account_owner'] ?? null,
                'relationship_manager' => $row['relationship_manager'] ?? null,
                'customer_since' => $row['customer_since'] ?? null,
                'account_created_date' => !empty($row['account_created_date']) ? \Carbon\Carbon::parse($row['account_created_date'])->format('Y-m-d') : null,
                'last_updated_date' => !empty($row['last_updated_date']) ? \Carbon\Carbon::parse($row['last_updated_date'])->format('Y-m-d') : null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0
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