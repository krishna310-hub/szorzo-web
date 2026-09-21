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
            return collect($row->toArray())->only(['id', 'status', 'created_at'])->merge([
                'status' => $row->status ? 'Active' : 'Inactive'
            ])->values()->toArray();
        });
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Account ID',
  1 => 'Client ID',
  2 => 'Service ID',
  3 => 'Legal Entity Name',
  4 => 'Account Name (Display)',
  5 => 'Industry',
  6 => 'Sub Industry',
  7 => 'Website URL',
  8 => 'Country',
  9 => 'Region',
  10 => 'State',
  11 => 'City',
  12 => 'Status',
), $data->toArray()), 'ClientProfile-export.xlsx');
    }

    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Account ID',
  1 => 'Client ID',
  2 => 'Service ID',
  3 => 'Legal Entity Name',
  4 => 'Account Name (Display)',
  5 => 'Industry',
  6 => 'Sub Industry',
  7 => 'Website URL',
  8 => 'Country',
  9 => 'Region',
  10 => 'State',
  11 => 'City',
  12 => 'Status',
), []), 'ClientProfile-template.xlsx');
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
                'service_id' => $row['service_id'] ?? null,
                'legal_entity_name' => $row['legal_entity_name'] ?? null,
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
