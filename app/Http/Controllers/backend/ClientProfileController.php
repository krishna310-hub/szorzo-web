<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ClientProfileController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ClientProfile::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (!$row->is_converted_to_client) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-primary me-1 convert-client" data-route="'.route('admin.client-profiles.convert', $row->id).'" title="Convert to Main Client">Convert</button>';
                    }
                    $buttons .= '<a href="'.route('admin.client-profiles.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.client-profiles.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.client-profiles.index');
    }

    public function create()
    {
        return view('backend.client-profiles.create');
    }

    public function store(Request $request)
    {
        ClientProfile::create($this->validatedData($request));
        return redirect()->route('admin.client-profiles.index')->with('success', 'Client Profile created successfully.');
    }

    public function edit($id)
    {
        return view('backend.client-profiles.edit', [
            'model' => ClientProfile::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        ClientProfile::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.client-profiles.index')->with('success', 'Client Profile updated successfully.');
    }

    public function destroy($id)
    {
        ClientProfile::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function convertToClient($id)
    {
        $profile = ClientProfile::findOrFail($id);
        
        if ($profile->is_converted_to_client) {
            return response()->json(['status' => false, 'message' => 'Already converted.']);
        }
        
        DB::transaction(function () use ($profile) {
            Client::create([
                'client' => $profile->account_name,
                'poc_name' => $profile->account_owner,
                'email' => null,
                'contact_number' => null,
                'status' => $profile->status,
            ]);
            $profile->update(['is_converted_to_client' => true]);
        });
        
        return response()->json(['status' => true, 'message' => 'Client profile converted to Main Client successfully.']);
    }

    public function export()
    {
        $data = \App\Models\ClientProfile::all()->map(function ($row) {
            return [
                $row->account_id,
                $row->client_id,
                $row->service_id ? \App\Models\ServiceOffered::find($row->service_id)?->service_name : null,
                $row->legal_entity_name,
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
                $row->account_source,
                $row->customer_domain,
                $row->account_owner,
                $row->relationship_manager,
                $row->customer_since ? \Carbon\Carbon::parse($row->customer_since)->format("Y-m-d") : null,
                $row->account_created_date ? \Carbon\Carbon::parse($row->account_created_date)->format("Y-m-d") : null,
                $row->last_updated_date ? \Carbon\Carbon::parse($row->last_updated_date)->format("Y-m-d") : null,
                $row->relationship_status,
                $row->primary_contact_name_designation,
                $row->primary_email,
                $row->primary_contact_number,
                $row->status ? "Active" : "Inactive"
            ];
        });
        
        $dropdowns = [
            'Industry' => \App\Models\Division::where("status", 1)->pluck("name")->toArray() ?: ["-- No Data --"],
            'Service' => \App\Models\ServiceOffered::where("status", 1)->pluck("service_name")->toArray() ?: ["-- No Data --"],
            'Country of Origin' => \App\Models\Country::pluck("name")->toArray() ?: ["-- No Data --"],
            'State' => \App\Models\State::pluck("name")->toArray() ?: ["-- No Data --"],
            'City' => \App\Models\City::pluck("name")->toArray() ?: ["-- No Data --"],
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Account ID',
  1 => 'Client ID',
  2 => 'Service',
  3 => 'Legal Entity Name',
  4 => 'Account Name (Display)',
  5 => 'Industry',
  6 => 'Sub Industry',
  7 => 'Website URL',
  8 => 'Country of Origin',
  9 => 'Region',
  10 => 'State',
  11 => 'City',
  12 => 'PIN Code',
  13 => 'Registered Address',
  14 => 'Ownership Type',
  15 => 'Registration / Entity ID',
  16 => 'GSTIN / Tax ID',
  17 => 'Account / Lead Source',
  18 => 'Customer Domain',
  19 => 'Account Owner',
  20 => 'Relationship Manager',
  21 => 'Customer Since',
  22 => 'Account Created Date',
  23 => 'Last Updated Date',
  24 => 'Relationship Status',
  25 => 'Primary Contact Name & Designation',
  26 => 'Primary Email',
  27 => 'Primary Contact Number',
  28 => 'Status',
),
            $data->toArray(),
            $dropdowns
        ), 'ClientProfiles-export.xlsx');
    }

    public function importTemplate()
    {
        $dropdowns = [
            'Industry' => \App\Models\Division::where("status", 1)->pluck("name")->toArray() ?: ["-- No Data --"],
            'Service' => \App\Models\ServiceOffered::where("status", 1)->pluck("service_name")->toArray() ?: ["-- No Data --"],
            'Country of Origin' => \App\Models\Country::pluck("name")->toArray() ?: ["-- No Data --"],
            'State' => \App\Models\State::pluck("name")->toArray() ?: ["-- No Data --"],
            'City' => \App\Models\City::pluck("name")->toArray() ?: ["-- No Data --"],
            'Status' => ["Active", "Inactive"],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            array (
  0 => 'Account ID',
  1 => 'Client ID',
  2 => 'Service',
  3 => 'Legal Entity Name',
  4 => 'Account Name (Display)',
  5 => 'Industry',
  6 => 'Sub Industry',
  7 => 'Website URL',
  8 => 'Country of Origin',
  9 => 'Region',
  10 => 'State',
  11 => 'City',
  12 => 'PIN Code',
  13 => 'Registered Address',
  14 => 'Ownership Type',
  15 => 'Registration / Entity ID',
  16 => 'GSTIN / Tax ID',
  17 => 'Account / Lead Source',
  18 => 'Customer Domain',
  19 => 'Account Owner',
  20 => 'Relationship Manager',
  21 => 'Customer Since',
  22 => 'Account Created Date',
  23 => 'Last Updated Date',
  24 => 'Relationship Status',
  25 => 'Primary Contact Name & Designation',
  26 => 'Primary Email',
  27 => 'Primary Contact Number',
  28 => 'Status',
),
            [],
            $dropdowns
        ), 'ClientProfiles-template.xlsx');
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
                'client_id' => $row['client_id'] ?? null,
                'service_id' => $row['service'] ? \App\Models\ServiceOffered::where('service_name', $row['service'])->value('id') : null,
                'legal_entity_name' => $row['legal_entity_name'] ?? null,
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
                'account_source' => $row['account_lead_source'] ?? null,
                'customer_domain' => $row['customer_domain'] ?? null,
                'account_owner' => $row['account_owner'] ?? null,
                'relationship_manager' => $row['relationship_manager'] ?? null,
                'customer_since' => $row['customer_since'] ?? null,
                'account_created_date' => !empty($row['account_created_date']) ? \Carbon\Carbon::parse($row['account_created_date'])->format('Y-m-d') : null,
                'last_updated_date' => !empty($row['last_updated_date']) ? \Carbon\Carbon::parse($row['last_updated_date'])->format('Y-m-d') : null,
                'relationship_status' => $row['relationship_status'] ?? null,
                'primary_contact_name_designation' => $row['primary_contact_name_designation'] ?? null,
                'primary_email' => $row['primary_email'] ?? null,
                'primary_contact_number' => $row['primary_contact_number'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\ClientProfile::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'account_id' => 'nullable|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'service_id' => 'nullable|string|max:255',
            'legal_entity_name' => 'nullable|string|max:255',
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
            'account_source' => 'nullable|string|max:255',
            'customer_domain' => 'nullable|string|max:255',
            'account_owner' => 'nullable|string|max:255',
            'relationship_manager' => 'nullable|string|max:255',
            'customer_since' => 'nullable|string|max:255',
            'account_created_date' => 'nullable|date',
            'last_updated_date' => 'nullable|date',
            'relationship_status' => 'nullable|string|max:255',
            'primary_contact_name_designation' => 'nullable|string|max:255',
            'primary_email' => 'nullable|email|max:255',
            'primary_contact_number' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);
    }
}
