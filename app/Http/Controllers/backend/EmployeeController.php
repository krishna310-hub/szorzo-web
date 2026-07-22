<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Employee::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                        $buttons .= '<a href="'.route('admin.employees.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                        $buttons .= '<button type="button" data-route="'.route('admin.employees.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.employees.index');
    }

    public function create()
    {
        return view('backend.employees.create');
    }

    public function store(Request $request)
    {
         $data = $this->validatedData($request);

        if ($request->hasFile('employee_image')) {

            $image = $request->file('employee_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('uploads/employees'), $imageName);

            $data['employee_image'] = $imageName;
        }

        Employee::create($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        return view('backend.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update($this->validatedData($request, $employee->id));

        if ($request->hasFile('employee_image')) {

        if ($employee->employee_image && file_exists(public_path('uploads/employees/' . $employee->employee_image))) {
                unlink(public_path('uploads/employees/' . $employee->employee_image));
            }

            $image = $request->file('employee_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('uploads/employees'), $imageName);

            $data['employee_image'] = $imageName;
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Employee deleted successfully.']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            // Personal Information
            'employee_name' => 'required|string|max:255',
            'employee_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'nationality' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|max:10',

            // Employee Details
            'employee_no' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
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
        ]);
    }
}
