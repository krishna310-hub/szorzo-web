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
                ->editColumn('priority', fn ($row) => ucfirst($row->priority ?? 'medium'))
                ->editColumn('pipeline_stage', fn ($row) => ucfirst(str_replace('_', ' ', $row->pipeline_stage)))
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (in_array($row->pipeline_stage, ['agreement_signed', 'won'], true) && !$row->client_profile_id) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success me-1 convert-lead" data-route="'.route('admin.lead-generations.convert', $row->id).'">Create Profile</button>';
                    }
                    $buttons .= '<a href="'.route('admin.lead-generations.edit', $row->id).'" class="btn btn-sm btn-outline-primary me-1" title="Open lead and activity timeline">Timeline</a>';
                    $buttons .= '<a href="'.route('admin.lead-generations.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit lead"><i class="bx bxs-edit"></i></a>';
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
        $data['assigned_to'] = $this->resolveAssignee($request);
        $data['created_by_user_id'] = auth()->id();
        $data['pipeline_stage'] = $data['assigned_to'] && $data['pipeline_stage'] === 'new' ? 'assigned' : $data['pipeline_stage'];
        if (!in_array($data['pipeline_stage'], ['new', 'assigned'], true)) $data['first_contact_at'] = now();

        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $lead = LeadGeneration::create($data);
            $this->logActivity($lead, 'lead_created', 'Lead created', 'Lead entered the sales pipeline.');
            if ($lead->assigned_to) $this->recordAssignment($lead, null, $lead->assigned_to, 'Initial assignment');
        });

        return redirect()->route('admin.lead-generations.index')->with('success', 'Lead created and added to the activity timeline.');
    }

    public function edit($id)
    {
        $model = $this->visibleLeads()->with(['activities.performer', 'activities.responsibleUser', 'assignmentHistory'])->findOrFail($id);

        return view('backend.lead-generations.edit', [
            'model' => $model,
            'salesUsers' => $this->salesUsers(),
            'activityTypes' => array_diff_key(\App\Models\LeadActivity::TYPES, array_flip([
                'stage_changed', 'assignment', 'lead_created', 'follow_up_completed', 'follow_up_rescheduled', 'follow_up_missed',
            ])),
            'pendingFollowUps' => $model->activities->filter(fn ($activity) => $activity->next_follow_up_at && in_array($activity->follow_up_status, ['pending', 'rescheduled'], true)),
        ]);
    }

    public function update(Request $request, $id)
    {
        $lead = $this->visibleLeads()->findOrFail($id);
        $data = $this->validatedData($request);
        $newAssignee = $this->resolveAssignee($request);
        $oldAssignee = $lead->assigned_to;
        $oldStage = $lead->pipeline_stage;
        $data['assigned_to'] = $newAssignee;
        if (!in_array($data['pipeline_stage'], ['new', 'assigned'], true) && !$lead->first_contact_at) $data['first_contact_at'] = now();
        if ($data['pipeline_stage'] === 'proposal_sent' && !$lead->proposal_sent_at) $data['proposal_sent_at'] = now();
        if (in_array($data['pipeline_stage'], ['agreement_signed', 'won'], true) && !$lead->agreement_signed_at) {
            $data['agreement_signed_at'] = now();
            $data['agreement_status'] = 'signed';
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($lead, $data, $oldAssignee, $newAssignee, $oldStage, $request) {
            $lead->update($data);
            if ((string) $oldStage !== (string) $lead->pipeline_stage) {
                $this->logActivity($lead, 'stage_changed', 'Lead stage updated', (LeadGeneration::STAGES[$oldStage] ?? $oldStage).' → '.(LeadGeneration::STAGES[$lead->pipeline_stage] ?? $lead->pipeline_stage));
            }
            if ((string) $oldAssignee !== (string) $newAssignee) {
                $reason = $request->input('assignment_reason', 'Lead assignment changed');
                $this->recordAssignment($lead, $oldAssignee, $newAssignee, $reason);
            }
        });

        return redirect()->route('admin.lead-generations.edit', $lead->id)->with('success', 'Lead updated. Stage and assignment changes were added to its timeline.');
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
                'pipeline_stage' => 'new',
                'priority' => 'medium'
            ];
        }
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($validRows) {
            foreach ($validRows as $data) {
                $data['created_by_user_id'] = auth()->id();
                $lead = LeadGeneration::create($data);
                $this->logActivity($lead, 'lead_created', 'Lead imported', 'Lead imported into the sales pipeline.');
                if ($lead->assigned_to) $this->recordAssignment($lead, null, $lead->assigned_to, 'Initial assignment on import');
            }
        });
        
        return back()->with('success', 'Imported successfully');
    }

    public function convertToClientProfile($id)
    {
        $lead = $this->visibleLeads()->whereKey($id)->firstOrFail();
        if (!in_array($lead->pipeline_stage, ['agreement_signed', 'won'], true)) {
            return response()->json(['status' => false, 'message' => 'Mark the lead Agreement Signed or Won before creating a Client Profile.'], 422);
        }
        if ($lead->client_profile_id) {
            return response()->json(['status' => false, 'message' => 'This lead already has a client profile.']);
        }

        $profile = \Illuminate\Support\Facades\DB::transaction(function () use ($lead) {
            $lead = LeadGeneration::whereKey($lead->id)->lockForUpdate()->firstOrFail();
            if ($lead->client_profile_id) return \App\Models\ClientProfile::findOrFail($lead->client_profile_id);
            $profile = \App\Models\ClientProfile::create([
                'account_id' => $lead->account_id, 'lead_generation_id' => $lead->id, 'assigned_to' => $lead->assigned_to,
                'account_name' => $lead->account_name, 'industry' => $lead->industry, 'sub_industry' => $lead->sub_industry,
                'website_url' => $lead->website_url, 'country_of_origin' => $lead->country_of_origin, 'region' => $lead->region,
                'state' => $lead->state, 'city' => $lead->city, 'registered_address' => $lead->registered_address,
                'pin_code' => $lead->pin_code, 'ownership_type' => $lead->ownership_type, 'registration_id' => $lead->registration_id,
                'gstin' => $lead->gstin, 'account_source' => $lead->account_source, 'account_owner' => $lead->account_owner,
                'relationship_manager' => $lead->relationship_manager, 'status' => $lead->status,
                'primary_contact_name_designation' => $lead->contact_person,
                'primary_email' => $lead->email,
                'primary_contact_number' => $lead->mobile,
                'relationship_status' => 'New Client',
            ]);
            $lead->update(['client_profile_id' => $profile->id, 'pipeline_stage' => 'converted', 'status' => $lead->status]);
            $this->logActivity($lead, 'stage_changed', 'Lead converted', 'Agreement signed. Client Profile #'.$profile->id.' created; original lead and timeline retained.');
            return $profile;
        });

        return response()->json(['status' => true, 'message' => 'Lead converted to Client Profile.', 'url' => route('admin.client-profiles.edit', $profile->id)]);
    }

    private function resolveAssignee(Request $request): ?int
    {
        if (auth()->user()->isSales()) return auth()->id();
        $validated = $request->validate([
            'assigned_to' => ['nullable', \Illuminate\Validation\Rule::exists('users', 'id')],
        ]);
        $id = $validated['assigned_to'] ?? null;
        if ($id && !$this->salesUsers()->contains(fn ($user) => (int) $user->id === (int) $id)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'assigned_to' => 'Choose an active Sales role user.',
            ]);
        }
        return $id ? (int) $id : null;
    }

    private function recordAssignment(LeadGeneration $lead, $previousUserId, $newUserId, ?string $reason): void
    {
        \App\Models\LeadAssignmentHistory::create([
            'lead_generation_id' => $lead->id,
            'assigned_by' => auth()->id(),
            'previous_assigned_to' => $previousUserId,
            'assigned_to' => $newUserId,
            'reason' => $reason,
            'assigned_at' => now(),
        ]);
        $previousName = $previousUserId ? \App\Models\User::find($previousUserId)?->name : 'Unassigned';
        $newName = $newUserId ? \App\Models\User::find($newUserId)?->name : 'Unassigned';
        $this->logActivity($lead, 'assignment', 'Lead assignment changed', trim(($previousName ?? 'Unassigned').' → '.($newName ?? 'Unassigned').'. '.($reason ?? '')));
    }

    private function logActivity(LeadGeneration $lead, string $type, string $outcome, string $notes): void
    {
        $lead->activities()->create([
            'activity_type' => $type,
            'occurred_at' => now(),
            'performed_by' => auth()->id(),
            'outcome' => $outcome,
            'notes' => $notes,
        ]);
    }

    private function visibleLeads()
    {
        $query = LeadGeneration::query();
        if (auth()->check() && auth()->user()->isSales()) $query->where('assigned_to', auth()->id());
        return $query;
    }

    private function salesUsers()
    {
        return \App\Models\User::with('role')->where('is_active', 1)->get()->filter(fn ($user) => $user->isSales())->values();
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'account_id' => 'nullable|string|max:255',
            'account_source' => 'nullable|string|max:255',
            'account_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:5000',
            'interested_service' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high',
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
            'pipeline_stage' => 'required|in:new,assigned,contact_attempted,contacted,follow_up,qualified,proposal_sent,negotiation,agreement_signed,won,lost,on_hold,converted',
            'opportunity_status' => 'nullable|string|max:255',
            'agreement_status' => 'nullable|in:draft,sent,under_review,signed,rejected',
            'lost_reason' => 'nullable|required_if:pipeline_stage,lost|string|max:255',
            'competitor' => 'nullable|string|max:255',
            'nurture_at' => 'nullable|date',
            'customer_since' => 'nullable|string|max:255',
            'account_created_date' => 'nullable|date',
            'last_updated_date' => 'nullable|date',
            'status' => 'required|boolean',
        ]);
    }
}