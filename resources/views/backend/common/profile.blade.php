@extends('backend.layouts.master')
@section('title', 'Profile')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('admin.upload.profile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="position-relative mx-n4 mt-n4">
                    <div class="profile-wid-bg profile-setting-img">
                        <img src="{{ Auth::user()->cover_picture ? asset(Auth::user()->cover_picture) : asset('admin/images/profile-bg.jpg') }}" class="profile-wid-img" alt="Cover image">
                        <div class="overlay-content"><div class="text-end p-3">
                            <label class="btn btn-light mb-0"><i class="ri-image-edit-line me-1"></i> Banner Image
                                <input type="file" name="cover_image" class="d-none" accept="image/jpeg,image/png,image/webp">
                            </label>
                        </div></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-3">
                        <div class="card mt-n5"><div class="card-body text-center p-4">
                            <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                <img src="{{ Auth::user()->profile_picture ? asset(Auth::user()->profile_picture) : ($employee?->employee_image ? asset('uploads/employees/'.$employee->employee_image) : asset('admin/images/users/user-dummy-img.jpg')) }}" class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow" alt="Profile image">
                                @can('profileEdit', \App\Models\General::class)
                                    <label class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <span class="avatar-title rounded-circle bg-light text-body material-shadow"><i class="ri-camera-fill"></i></span>
                                        <input type="file" name="profile_image" class="d-none" accept="image/jpeg,image/png,image/webp">
                                    </label>
                                @endcan
                            </div>
                            <h5 class="fs-16 mb-1">{{ $employee?->employee_name ?? Auth::user()->name }}</h5>
                            <p class="text-muted mb-3">{{ $employee?->designation ?? Auth::user()->role?->name }}</p>
                            <button type="submit" class="btn btn-success btn-sm">Update Images</button>
                            @error('profile_image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            @error('cover_image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div></div>
                    </div>

                    <div class="col-xxl-9">
                        <div class="card mt-xxl-n5"><div class="card-header"><h5 class="mb-0">Employee Details</h5></div>
                            <div class="card-body">
                                @if (! $employee)
                                    <div class="alert alert-warning mb-0">No employee record is linked to {{ Auth::user()->email }}. Add this email as the employee's official or personal email in the Employee Module.</div>
                                @else
                                    @php
                                        $sections = [
                                            'Personal Information' => ['employee_name'=>'Employee Name','dob'=>'Date of Birth','gender'=>'Gender','marital_status'=>'Marital Status','nationality'=>'Nationality','blood_group'=>'Blood Group'],
                                            'Employee Details' => ['employee_no'=>'Employee ID','designation'=>'Designation','date_of_joining'=>'Date of Joining (DOJ)','employee_uan_pf_number'=>'UAN / PF Number','employee_esi_number'=>'ESI Number','status'=>'Status'],
                                            'Contact Information' => ['mobile_number'=>'Mobile Number','alternate_mobile_number'=>'Alternate Mobile Number','official_mail'=>'Official Mail','personal_mail'=>'Personal Mail'],
                                            'Address Details' => ['permanent_address'=>'Permanent Address','current_residential_address'=>'Current Residential Address'],
                                            'Emergency Contact' => ['emergency_contact_name'=>'Contact Name','relationship'=>'Relationship','emergency_contact_number'=>'Contact Number','emergency_contact_mail'=>'Contact Mail','emergency_contact_address'=>'Contact Address'],
                                            'Identity Documents' => ['pan_card_number'=>'PAN Card Number','aadhaar_card_number'=>'Aadhaar Card Number','passport_number'=>'Passport Number','passport_validity_date'=>'Passport Validity Date'],
                                            'Family Information' => ['fathers_name'=>"Father's Name",'fathers_mobile_number'=>"Father's Mobile Number",'mothers_name'=>"Mother's Name",'siblings_name'=>'Siblings Name','husband_wife_name'=>'Husband / Wife Name','husband_wife_dob'=>'Husband / Wife DOB','spouse_mobile_number'=>'Spouse Mobile Number','childrens_name_dob'=>"Children's Name / DOB"],
                                            'Bank Details' => ['bank_name'=>'Bank Name','account_holders_name'=>"Account Holder's Name",'account_number'=>'Account Number','branch_ifsc_code'=>'Branch / IFSC Code','mode_of_salary'=>'Mode of Salary','bank_uan_pf_number'=>'Bank UAN / PF Number','bank_esi_number'=>'Bank ESI Number'],
                                            'Health Information' => ['any_health_issue'=>'Health Issues'],
                                            'Additional Information' => ['passion'=>'Passion','awards_appreciation'=>'Awards / Appreciation'],
                                        ];
                                        $dateFields = ['dob','date_of_joining','passport_validity_date','husband_wife_dob'];
                                    @endphp
                                    @foreach ($sections as $title => $fields)
                                        <h5 class="text-primary {{ $loop->first ? '' : 'mt-4' }} mb-3">{{ $title }}</h5>
                                        <div class="row g-3">
                                            @foreach ($fields as $field => $label)
                                                @php
                                                    $value = $employee->{$field};
                                                    if ($value && in_array($field, $dateFields, true)) $value = \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
                                                    if ($field === 'status') $value = $employee->status ? 'Active' : 'Inactive';
                                                @endphp
                                                <div class="col-md-6 col-xl-4">
                                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                                        <small class="text-muted d-block mb-1">{{ $label }}</small>
                                                        <span class="fw-medium text-break">{{ filled($value) ? $value : '-' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach

                                    @php
                                        $profileDocuments = collect([
                                            ['field' => 'offer_letter', 'label' => 'Offer Letter', 'icon' => 'ri-file-text-line', 'color' => 'primary'],
                                            ['field' => 'intent_letter', 'label' => 'Intent Letter', 'icon' => 'ri-draft-line', 'color' => 'success'],
                                        ])->filter(fn ($document) => filled($employee->{$document['field']}));
                                    @endphp

                                    <h5 class="text-primary mt-4 mb-3">Documents</h5>
                                    @if ($profileDocuments->isNotEmpty())
                                        <div class="row g-3">
                                            @foreach ($profileDocuments as $document)
                                                @php
                                                    $fileName = $employee->{$document['field']};
                                                    $documentUrl = asset('uploads/employees/documents/'.$fileName);
                                                    $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle d-flex align-items-center gap-3">
                                                        <div class="avatar-sm flex-shrink-0">
                                                            <span class="avatar-title rounded-3 bg-{{ $document['color'] }}-subtle text-{{ $document['color'] }} fs-3">
                                                                <i class="{{ $document['icon'] }}"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <h6 class="mb-1">{{ $document['label'] }}</h6>
                                                            <span class="badge bg-secondary-subtle text-secondary">{{ $extension ?: 'FILE' }}</span>
                                                        </div>
                                                        <div class="d-flex gap-1 flex-shrink-0">
                                                            <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="View {{ $document['label'] }}">
                                                                <i class="ri-eye-line"></i> View
                                                            </a>
                                                            <a href="{{ $documentUrl }}" download class="btn btn-sm btn-outline-success" title="Download {{ $document['label'] }}">
                                                                <i class="ri-download-2-line"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="border rounded-3 bg-light-subtle text-center text-muted py-4">
                                            <i class="ri-file-upload-line fs-2 d-block mb-1"></i>
                                            No offer or intent letter has been uploaded yet.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            @if (Auth::user()->isSuperAdmin())
                <div class="row justify-content-end mt-4">
                    <div class="col-xxl-9">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.profile.change-password') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                                            @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                                            <input type="password" id="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                                            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
