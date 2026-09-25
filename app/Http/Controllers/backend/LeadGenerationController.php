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
            return DataTables::of($this->visibleLeads()->with('assignee')->latest())
                ->addIndexColumn()
                ->addColumn('assignee_name', fn ($row) => $row->assignee?->name ?? 'Unassigned')
                ->editColumn('pipeline_stage', fn ($row) => ucfirst(str_replace('_', ' ', $row->pipeline_stage)))
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if ($row->pipeline_stage === 'interested' && !$row->client_profile_id) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success me-1 convert-lead" data-route="'.route('admin.lead-generations.convert', $row->id).'">Create Profile</button>';
                    }
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
        return view('backend.lead-generations.create', ['salesUsers' => $this->salesUsers()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['assigned_to'] = auth()->user()->isSales() ? auth()->id() : ($request->validate(['assigned_to' => ['nullable', \Illuminate\Validation\Rule::exists('users', 'id')]])['assigned_to'] ?? null);
        if (!empty($data['pipeline_stage']) && in_array($data['pipeline_stage'], ['contacted', 'follow_up', 'interested', 'opportunity', 'proposal', 'negotiation', 'agreement_signed'], true)) $data['first_contact_at'] = now();
        LeadGeneration::create($data);
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.lead-generations.edit', [
            'model' => $this->visibleLeads()->findOrFail($id),
            'salesUsers' => $this->salesUsers()
        ]);
    }

    public function update(Request $request, $id)
    {
        $lead = $this->visibleLeads()->findOrFail($id);
        $data = $this->validatedData($request);
        $data['assigned_to'] = auth()->user()->isSales() ? auth()->id() : ($request->validate(['assigned_to' => ['nullable', \Illuminate\Validation\Rule::exists('users', 'id')]])['assigned_to'] ?? null);
        if (in_array($data['pipeline_stage'] ?? '', ['contacted', 'follow_up', 'interested', 'opportunity', 'proposal', 'negotiation', 'agreement_signed'], true) && !$lead->first_contact_at) $data['first_contact_at'] = now();
        $lead->update($data);
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $this->visibleLeads()->findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $data = $this->visibleLeads()->get()->map(function ($row) {
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
                $row->status ? "Active" : "Inactive",
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
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0,
                'assigned_to' => auth()->user()->isSales() ? auth()->id() : null,
                'pipeline_stage' => 'new'
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\LeadGeneration::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    public function convertToClientProfile($id)
    {
        $lead = $this->visibleLeads()->findOrFail($id);
        if ($lead->pipeline_stage !== 'interested') {
            return response()->json(['status' => false, 'message' => 'Qualify the lead as interested before creating a client profile.'], 422);
        }
        if ($lead->client_profile_id) {
            return response()->json(['status' => false, 'message' => 'This lead already has a client profile.']);
        }

        $profile = \Illuminate\Support\Facades\DB::transaction(function () use ($lead) {
            $profile = \App\Models\ClientProfile::create([
                'account_id' => $lead->account_id, 'lead_generation_id' => $lead->id, 'assigned_to' => $lead->assigned_to,
                'account_name' => $lead->account_name, 'industry' => $lead->industry, 'sub_industry' => $lead->sub_industry,
                'website_url' => $lead->website_url, 'country_of_origin' => $lead->country_of_origin, 'region' => $lead->region,
                'state' => $lead->state, 'city' => $lead->city, 'registered_address' => $lead->registered_address,
                'pin_code' => $lead->pin_code, 'ownership_type' => $lead->ownership_type, 'registration_id' => $lead->registration_id,
                'gstin' => $lead->gstin, 'account_source' => $lead->account_source, 'account_owner' => $lead->account_owner,
                'relationship_manager' => $lead->relationship_manager, 'status' => $lead->status,
            ]);
            $lead->update(['client_profile_id' => $profile->id, 'pipeline_stage' => 'converted']);
            return $profile;
        });

        return response()->json(['status' => true, 'message' => 'Lead converted to Client Profile.', 'url' => route('admin.client-profiles.edit', $profile->id)]);
    }

    private function visibleLeads()
    {
        $query = LeadGeneration::query();
        if (auth()->check() && auth()->user()->isSales()) $query->where('assigned_to', auth()->id());
        return $query;
    }

    private function salesUsers()
    {
        return \App\Models\User::with('role')->get()->filter(fn ($user) => $user->isSales())->values();
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
            'assigned_to' => 'nullable|exists:users,id',
            'pipeline_stage' => 'required|in:new,assigned,contacted,follow_up,interested,opportunity,proposal,negotiation,agreement_signed,converted,lost',
            'opportunity_status' => 'nullable|string|max:255',
            'next_follow_up_at' => 'nullable|date',
            'follow_up_notes' => 'nullable|string|max:5000',
            'customer_since' => 'nullable|string|max:255',
            'account_created_date' => 'nullable|date',
            'last_updated_date' => 'nullable|date',
            'status' => 'required|boolean',
        ]);
    }
}