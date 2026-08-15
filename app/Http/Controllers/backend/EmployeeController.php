<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeOnboardingLink;
use App\Models\Mode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    use AuthorizesRequests;

    private const DOCUMENT_FIELDS = [
        'offer_letter',
        'intent_letter',
        'pan_card_file',
        'aadhaar_file',
        'twelfth_marksheet',
        'tenth_marksheet',
        'degree_certificate',
    ];

    private const MULTIPLE_DOCUMENT_FIELDS = [
        'previous_company_offer_letters',
        'relieving_letters',
        'pay_slips',
        'bank_statements',
        'passbook_cheques',
    ];

    public function index(Request $request)
    {
        $this->authorize('read', Employee::class);
        if ($request->ajax()) {
            return DataTables::of(Employee::orderBy('employee_no', 'asc')->get())
                ->addIndexColumn()
                ->editColumn('date_of_joining', fn ($row) => $row->date_of_joining?->format('d-m-Y') ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (! $row->status && $this->canManagePublicOnboarding()) {
                        $buttons .= '<button type="button" data-route="'.route('admin.employees.activate', $row->id).'" class="btn btn-sm btn-success me-2 activate-record">Activate</button>';
                    }
                    if (auth()->user()->can('edit', Employee::class)) {
                        $buttons .= '<a href="'.route('admin.employees.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Employee::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.employees.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.employees.index');
    }

    public function create()
    {
        $this->authorize('create', Employee::class);

        return view('backend.employees.create', $this->formOptions());
    }

    public function generateLink()
    {
        $this->authorize('create', Employee::class);
        abort_unless($this->canManagePublicOnboarding(), 403);

        do {
            $token = Str::random(64);
        } while (EmployeeOnboardingLink::where('token', $token)->exists());

        EmployeeOnboardingLink::create([
            'token' => $token,
            'created_by_user_id' => auth()->id(),
        ]);

        return back()->with('onboarding_link', route('employee-onboarding.form', $token));
    }

    public function publicForm(string $token)
    {
        $link = EmployeeOnboardingLink::where('token', $token)->whereNull('used_at')->firstOrFail();

        return view('backend.employees.public-form', [
            'onboardingLink' => $link,
            'publicEmployeeForm' => true,
        ]);
    }

    public function publicStore(Request $request, string $token)
    {
        EmployeeOnboardingLink::where('token', $token)->whereNull('used_at')->firstOrFail();
        $request->validate([
            'employee_no' => 'prohibited',
            'client_id' => 'prohibited',
            'date_of_joining' => 'prohibited',
            'mode_id' => 'prohibited',
            'offer_letter' => 'prohibited',
            'intent_letter' => 'prohibited',
            'official_mail' => 'prohibited',
        ]);
        $request->merge(['status' => 0]);
        $data = $this->validatedData($request);
        unset($data['client_id'], $data['date_of_joining'], $data['mode_id'], $data['offer_letter'], $data['intent_letter'], $data['official_mail']);
        $data['status'] = false;
        $data['employee_no'] = $this->generateEmployeeNumber();

        if ($request->hasFile('employee_image')) {
            $image = $request->file('employee_image');
            $imageName = Str::uuid().'.'.$image->getClientOriginalExtension();
            File::ensureDirectoryExists(public_path('uploads/employees'));
            $image->move(public_path('uploads/employees'), $imageName);
            $data['employee_image'] = $imageName;
        }
        $this->storeDocuments($request, $data, null, ['offer_letter', 'intent_letter']);

        DB::transaction(function () use ($token, $data) {
            $link = EmployeeOnboardingLink::where('token', $token)->whereNull('used_at')->lockForUpdate()->firstOrFail();
            $employee = Employee::create($data);
            $link->update(['employee_id' => $employee->id, 'used_at' => now()]);
        });

        return view('backend.employees.public-success');
    }

    public function activate($id)
    {
        $this->authorize('edit', Employee::class);
        abort_unless($this->canManagePublicOnboarding(), 403);
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => true]);

        return response()->json(['status' => true, 'message' => 'Employee activated successfully.']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Employee::class);
        $data = $this->validatedData($request);

        if ($request->hasFile('employee_image')) {

            $image = $request->file('employee_image');
            $imageName = Str::uuid().'.'.$image->getClientOriginalExtension();

            File::ensureDirectoryExists(public_path('uploads/employees'));
            $image->move(public_path('uploads/employees'), $imageName);

            $data['employee_image'] = $imageName;
        }

        $this->storeDocuments($request, $data);

        Employee::create($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Employee::class);
        $employee = Employee::findOrFail($id);

        return view('backend.employees.edit', array_merge(compact('employee'), $this->formOptions()));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Employee::class);
        $employee = Employee::findOrFail($id);
        $data = $this->validatedData($request, $employee->id);

        if ($request->hasFile('employee_image')) {

            if ($employee->employee_image && file_exists(public_path('uploads/employees/'.$employee->employee_image))) {
                unlink(public_path('uploads/employees/'.$employee->employee_image));
            }

            $image = $request->file('employee_image');
            $imageName = Str::uuid().'.'.$image->getClientOriginalExtension();

            File::ensureDirectoryExists(public_path('uploads/employees'));
            $image->move(public_path('uploads/employees'), $imageName);

            $data['employee_image'] = $imageName;
        }

        $this->storeDocuments($request, $data, $employee);

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Employee::class);
        $employee = Employee::findOrFail($id);
        $this->deleteEmployeeFiles($employee);
        $employee->delete();

        return response()->json(['status' => true, 'message' => 'Employee deleted successfully.']);
    }

    private function validatedData(Request $request, ?int $employeeId = null): array
    {
        $selectedMode = $request->filled('mode_id') ? Mode::find($request->integer('mode_id')) : null;
        $requiresContractDates = in_array(strtolower(trim($selectedMode?->mode ?? '')), ['contract', 'c2h'], true);

        $data = $request->validate([
            // Personal Information
            'employee_name' => 'required|string|max:255',
            'employee_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'nationality' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|max:10',

            // Employee Details
            'employee_no' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'employee_no')->ignore($employeeId)->whereNull('deleted_at')],
            'designation' => 'nullable|string|max:255',
            'date_of_joining' => 'nullable|date',
            'client_id' => 'nullable|integer|exists:clients,id',
            'mode_id' => 'nullable|integer|exists:modes,id',
            'contract_from_date' => [Rule::requiredIf($requiresContractDates), 'nullable', 'date'],
            'contract_to_date' => [Rule::requiredIf($requiresContractDates), 'nullable', 'date', 'after_or_equal:contract_from_date'],
            'offer_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'intent_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'previous_company_offer_letters' => 'nullable|array',
            'previous_company_offer_letters.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'relieving_letters' => 'nullable|array',
            'relieving_letters.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pay_slips' => 'nullable|array',
            'pay_slips.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bank_statements' => 'nullable|array',
            'bank_statements.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'passbook_cheques' => 'nullable|array',
            'passbook_cheques.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'removed_documents' => 'nullable|array',
            'removed_documents.*' => 'string|max:255',
            'employee_uan_pf_number' => 'nullable|string|max:50',
            'employee_esi_number' => 'nullable|string|max:50',

            // Contact Information
            'mobile_number' => 'nullable|digits_between:10,15',
            'alternate_mobile_number' => 'nullable|digits_between:10,15',
            'official_mail' => 'nullable|email|max:255',
            'personal_mail' => 'nullable|email|max:255',

            // Address Details
            'permanent_address' => 'nullable|string|max:1000',
            'current_residential_address' => 'nullable|string|max:1000',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'relationship' => 'nullable|string|max:100',
            'emergency_contact_number' => 'nullable|digits_between:10,15',
            'emergency_contact_mail' => 'nullable|email|max:255',
            'emergency_contact_address' => 'nullable|string|max:1000',

            // Identity Documents
            'pan_card_number' => 'nullable|string|max:20',
            'aadhaar_card_number' => 'nullable|digits:12',
            'passport_number' => 'nullable|string|max:20',
            'passport_validity_date' => 'nullable|date',
            'pan_card_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'aadhaar_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'twelfth_marksheet' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tenth_marksheet' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'degree_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Family Information
            'fathers_name' => 'nullable|string|max:255',
            'fathers_mobile_number' => 'nullable|digits_between:10,15',
            'mothers_name' => 'nullable|string|max:255',
            'siblings_name' => 'nullable|string|max:500',
            'husband_wife_name' => 'nullable|string|max:255',
            'husband_wife_dob' => 'nullable|date',
            'spouse_mobile_number' => 'nullable|digits_between:10,15',
            'childrens_name_dob' => 'nullable|string|max:1000',

            // Bank Details
            'bank_name' => 'nullable|string|max:255',
            'account_holders_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'branch_ifsc_code' => 'nullable|string|max:100',
            'mode_of_salary' => 'nullable|string|max:100',
            'bank_uan_pf_number' => 'nullable|string|max:50',
            'bank_esi_number' => 'nullable|string|max:50',

            // Health Information
            'any_health_issue' => 'nullable|string|max:1000',

            // Additional Information
            'passion' => 'nullable|string|max:1000',
            'awards_appreciation' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        if (! $requiresContractDates) {
            $data['contract_from_date'] = null;
            $data['contract_to_date'] = null;
        }

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'clients' => Client::orderByDesc('status')->orderBy('client')->get(['id', 'client', 'status']),
            'modes' => Mode::orderByDesc('status')->orderBy('mode')->get(['id', 'mode', 'status']),
        ];
    }

    private function storeDocuments(Request $request, array &$data, ?Employee $employee = null, array $excludedFields = []): void
    {
        File::ensureDirectoryExists(public_path('uploads/employees/documents'));

        foreach (self::DOCUMENT_FIELDS as $field) {
            if (in_array($field, $excludedFields, true)) {
                unset($data[$field]);
                continue;
            }
            if (! $request->hasFile($field)) {
                unset($data[$field]);

                continue;
            }

            if ($employee?->{$field}) {
                File::delete(public_path('uploads/employees/documents/'.$employee->{$field}));
            }

            $file = $request->file($field);
            $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/employees/documents'), $fileName);
            $data[$field] = $fileName;
        }

        $removedDocuments = $request->input('removed_documents', []);

        foreach (self::MULTIPLE_DOCUMENT_FIELDS as $field) {
            $documents = array_values($employee?->{$field} ?? []);
            $documentsToRemove = array_intersect($documents, $removedDocuments);

            foreach ($documentsToRemove as $fileName) {
                File::delete(public_path('uploads/employees/documents/'.$fileName));
            }

            $documents = array_values(array_diff($documents, $documentsToRemove));

            foreach ($request->file($field, []) as $file) {
                if (! $file) {
                    continue;
                }

                $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/employees/documents'), $fileName);
                $documents[] = $fileName;
            }

            $data[$field] = $documents ?: null;
        }
    }

    private function canManagePublicOnboarding(): bool
    {
        $accessLevel = str_replace('_', '-', strtolower((string) auth()->user()?->role?->access_level));

        return in_array($accessLevel, ['super-admin', 'delivery-lead', 'recruiter-dl'], true);
    }

    private function generateEmployeeNumber(): string
    {
        do {
            $employeeNumber = 'SZ-'.strtoupper(Str::random(10));
        } while (Employee::withTrashed()->where('employee_no', $employeeNumber)->exists());

        return $employeeNumber;
    }

    private function deleteEmployeeFiles(Employee $employee): void
    {
        if ($employee->employee_image) {
            File::delete(public_path('uploads/employees/'.$employee->employee_image));
        }

        foreach (self::DOCUMENT_FIELDS as $field) {
            if ($employee->{$field}) {
                File::delete(public_path('uploads/employees/documents/'.$employee->{$field}));
            }
        }

        foreach (self::MULTIPLE_DOCUMENT_FIELDS as $field) {
            foreach ($employee->{$field} ?? [] as $fileName) {
                File::delete(public_path('uploads/employees/documents/'.$fileName));
            }
        }
    }
}
