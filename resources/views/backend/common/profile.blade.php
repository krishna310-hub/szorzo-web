@extends('backend.layouts.master')
@section('title')
    {{'Settings'}}
@endsection
@section('content')

<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="position-relative mx-n4 mt-n4">
                <div class="profile-wid-bg profile-setting-img">
                    <img src="{{ asset('admin/images/profile-bg.jpg')}}" class="profile-wid-img" alt="">
                    <div class="overlay-content">
                        <div class="text-end p-3">
                            <div class="p-0 ms-auto rounded-circle profile-photo-edit">
                                <input id="profile-foreground-img-file-input" type="file" class="profile-foreground-img-file-input">
                                <label for="profile-foreground-img-file-input" class="profile-photo-edit btn btn-light">
                                    <i class="ri-image-edit-line align-bottom me-1"></i> Change Cover
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xxl-3">
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                    <img src="{{ Auth::user()?->profile_picture ? asset(Auth::user()->profile_picture) : asset('admin/images/users/user-dummy-img.jpg') }}" class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="fs-16 mb-1">{{ Auth::user()->name}}</h5>
                                <p class="text-muted mb-0">{{ Auth::user()->role->name }}</p>
                            </div>
                        </div>
                    </div>
                    <!--end card-->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-5">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Complete Your Profile</h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="javascript:void(0);" class="badge bg-light text-primary fs-12"><i class="ri-edit-box-line align-bottom me-1"></i> Edit</a>
                                </div>
                            </div>
                            <div class="progress animated-progress custom-progress progress-label">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                                    <div class="label">30%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Portfolio</h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="javascript:void(0);" class="badge bg-light text-primary fs-12"><i class="ri-add-fill align-bottom me-1"></i> Add</a>
                                </div>
                            </div>
                            <div class="mb-3 d-flex">
                                <div class="avatar-xs d-block flex-shrink-0 me-3">
                                    <span class="avatar-title rounded-circle fs-16 bg-body text-body material-shadow">
                                        <i class="ri-github-fill"></i>
                                    </span>
                                </div>
                                <input type="email" class="form-control" id="gitUsername" placeholder="Username" value="@daveadame">
                            </div>
                            <div class="mb-3 d-flex">
                                <div class="avatar-xs d-block flex-shrink-0 me-3">
                                    <span class="avatar-title rounded-circle fs-16 bg-primary material-shadow">
                                        <i class="ri-global-fill"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3 d-flex">
                                <div class="avatar-xs d-block flex-shrink-0 me-3">
                                    <span class="avatar-title rounded-circle fs-16 bg-success material-shadow">
                                        <i class="ri-dribbble-fill"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="dribbleName" placeholder="Username" value="@dave_adame">
                            </div>
                            <div class="d-flex">
                                <div class="avatar-xs d-block flex-shrink-0 me-3">
                                    <span class="avatar-title rounded-circle fs-16 bg-danger material-shadow">
                                        <i class="ri-pinterest-fill"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="pinterestName" placeholder="Username" value="Advance Dave">
                            </div>
                        </div>
                    </div> --}}
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-xxl-9">
                    <div class="card mt-xxl-n5">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab" aria-selected="true">
                                        <i class="fas fa-home"></i> Personal Details
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab" aria-selected="false" tabindex="-1">
                                        <i class="far fa-user"></i> Change Password
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#privacy" role="tab" aria-selected="false" tabindex="-1">
                                        <i class="far fa-envelope"></i> Privacy Policy
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <div class="tab-pane active show" id="personalDetails" role="tabpanel">
                                    <form action="javascript:void(0);">
                                        <div class="row">
                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Personal Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="firstnameInput" class="form-label">Name</label>
                                                                <input type="text" class="form-control" id="firstnameInput" placeholder="Enter your firstname" value="{{ auth()->user()->name }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Date of Birth</label>
                                                            <input type="date" class="form-control" name="dob">
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Gender</label>
                                                            <select class="form-select" name="gender">
                                                                <option value="">Select Gender</option>
                                                                <option>Male</option>
                                                                <option>Female</option>
                                                                <option>Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Marital Status</label>
                                                            <select class="form-select" name="marital_status">
                                                                <option value="">Select Status</option>
                                                                <option>Single</option>
                                                                <option>Married</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Nationality</label>
                                                            <input type="text" class="form-control" name="nationality">
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Blood Group</label>
                                                            <input type="text" class="form-control" name="blood_group">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Employment Details</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="employee_no" class="form-label">Employee ID</label>
                                                                <input type="text" class="form-control" id="employee_no" value="">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="designationInput" class="form-label">Designation</label>
                                                                <input type="text" class="form-control" id="designationInput" value="{{ auth()->user()->role->name }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="uan_pf_number" class="form-label">UAN / PF Number</label>
                                                                <input type="text" class="form-control" id="uan_pf_number" name="uan_pf_number">
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="esi_number" class="form-label">ESI Number</label>
                                                                <input type="text" class="form-control" id="esi_number" name="esi_number">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Contact Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="phonenumberInput" class="form-label">Phone Number</label>
                                                                <input type="text" class="form-control" id="phonenumberInput" placeholder="Enter your phone number" value="{{ auth()->user()->phone_number }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Alternate Mobile Number</label>
                                                            <input type="text" class="form-control" name="alternate_mobile">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label for="emailInput" class="form-label">Official Mail</label>
                                                                <input type="email" class="form-control" id="emailInput" placeholder="Enter your email" value="{{ auth()->user()->email }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Personal Mail</label>
                                                            <input type="email" class="form-control" name="personal_email">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Address Details</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Permanent Address</label>
                                                            <textarea class="form-control" name="permanent_address" rows="3"></textarea>
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Current Residential Address</label>
                                                            <textarea class="form-control" name="current_address" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Emergency Contact</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Emergency Contact Name</label>
                                                            <input type="text" class="form-control" name="emergency_contact_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Relationship</label>
                                                            <input type="text" class="form-control" name="emergency_relationship">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Emergency Contact Phone</label>
                                                            <input type="text" class="form-control" name="emergency_phone">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Emergency Contact Email</label>
                                                            <input type="email" class="form-control" name="emergency_email">
                                                        </div>

                                                        <div class="col-lg-12 mb-3">
                                                            <label class="form-label">Emergency Contact Address</label>
                                                            <textarea class="form-control" name="emergency_address" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Identity Documents</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">PAN Card Number</label>
                                                            <input type="text" class="form-control" name="pan_number">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Aadhaar Card Number</label>
                                                            <input type="text" class="form-control" name="aadhaar_number">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Passport Number</label>
                                                            <input type="text" class="form-control" name="passport_number">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Passport Validity Till</label>
                                                            <input type="date" class="form-control" name="passport_validity">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Family Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Father's Name</label>
                                                            <input type="text" class="form-control" name="father_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Father's Mobile Number</label>
                                                            <input type="text" class="form-control" name="father_mobile">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Mother's Name</label>
                                                            <input type="text" class="form-control" name="mother_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Sibling(s) Name</label>
                                                            <input type="text" class="form-control" name="siblings_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Husband / Wife Name</label>
                                                            <input type="text" class="form-control" name="spouse_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Husband / Wife DOB</label>
                                                            <input type="date" class="form-control" name="spouse_dob">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Spouse Mobile Number</label>
                                                            <input type="text" class="form-control" name="spouse_mobile">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Children's Name & DOB</label>
                                                            <textarea class="form-control" name="children_details" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Bank Details</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Bank Name</label>
                                                            <input type="text" class="form-control" name="bank_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Account Holder's Name</label>
                                                            <input type="text" class="form-control" name="account_holder_name">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Account Number</label>
                                                            <input type="text" class="form-control" name="account_number">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Branch & IFSC / SWIFT Code</label>
                                                            <input type="text" class="form-control" name="ifsc_code">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Mode of Salary Payment</label>
                                                            <select class="form-select" name="salary_payment_mode">
                                                                <option value="">Select</option>
                                                                <option>Bank Transfer</option>
                                                                <option>Cheque</option>
                                                                <option>Cash</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">UAN / PF Number</label>
                                                            <input type="text" class="form-control" name="uan_pf_number">
                                                        </div>

                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">ESI Number</label>
                                                            <input type="text" class="form-control" name="esi_number">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Health Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-3">
                                                            <label class="form-label">Do you have any Health Issues?</label>
                                                            <textarea class="form-control" name="health_issues" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Additional Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-12 mb-3">
                                                            <label class="form-label">What is Your Passion?</label>
                                                            <textarea class="form-control" name="passion" rows="3"></textarea>
                                                        </div>

                                                        <div class="col-lg-12 mb-3">
                                                            <label class="form-label">Awards / Appreciations</label>
                                                            <textarea class="form-control" name="awards" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end col-->
                                            {{-- <div class="col-lg-12">
                                                <div class="hstack gap-2 justify-content-end">
                                                    <button type="submit" class="btn btn-primary">Updates</button>
                                                    <button type="button" class="btn btn-soft-success">Cancel</button>
                                                </div>
                                            </div> --}}
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </form>
                                </div>
                                <!--end tab-pane-->
                                <div class="tab-pane" id="changePassword" role="tabpanel">
                                    <form action="{{ route('admin.settings.store','change_password')}}" method="POST" id="change-pass">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="oldpasswordInput" class="form-label">Old Password*</label>
                                                    <input type="password" class="form-control required" name="current_password" id="oldpasswordInput" placeholder="Enter current password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="newpasswordInput" class="form-label">New Password*</label>
                                                    <input type="password" class="form-control required" name="new_password" id="newpasswordInput" placeholder="Enter new password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="confirmpasswordInput" class="form-label">Confirm Password*</label>
                                                    <input type="password" class="form-control required" name="new_password_confirmation" id="confirmpasswordInput" placeholder="Confirm password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-12">
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success">Change Password</button>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </form>
                                </div>
                                <!--end tab-pane-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->

        </div>
        <!-- container-fluid -->
    </div><!-- End Page-content -->
</div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $("#change-pass").validate({
                rules: {
                    current_password: {
                        required: true,
                        minlength: 6
                    },
                    new_password: {
                        required: true,
                        minlength: 8
                    },
                    new_password_confirmation: {
                        required: true,
                        // equalTo: "#new_password"
                    }
                },
                messages: {
                    current_password: {
                        required: "Please enter your current password",
                        minlength: "Your password must be at least 6 characters long"
                    },
                    new_password: {
                        required: "Please enter a new password",
                        minlength: "New password must be at least 8 characters long"
                    },
                    new_password_confirmation: {
                        required: "Please confirm your new password",
                        equalTo: "Passwords do not match"
                    }
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("text-danger");
                    error.insertAfter(element);
                }
            });
        });
    </script>
@endsection
