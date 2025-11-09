@extends('backend.layouts.master')
@section('title')
    {{'User'}}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Edit User</h4>
                            </div>
                            <div class="card-body">
                                <form id="user-form" action="{{ route('admin.user.update',$user->id) }}" method="POST" enctype="multipart/form-data">
                                    @method('PUT')
                                    @csrf
                                    <div class="live-preview mb-3">
                                        <div class="row gy-4">
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="role_id" class="form-label">Select Role <span class="text-danger"> *</span></label>
                                                    <select class="form-select" id="role_id" name="role_id">
                                                        <option value="">-- Select Role --</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->id }}" {{ $role->id == $user->role_id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('role_id'))
                                                        <span class="text-danger small">
                                                            {{ $errors->first('role_id') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="user_name" class="form-label">User Name <span class="text-danger"> *</span></label>
                                                    <input type="text" class="form-control" id="user_name" name="user_name"value="{{ $user->name }}" placeholder="Enter Role Name">
                                                    @if ($errors->has('user_name'))
                                                        <span class="text-danger small">
                                                            {{ $errors->first('user_name') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="email" class="form-label">Email <span class="text-danger"> *</span></label>
                                                    <input type="email" class="form-control" id="email" name="email"value="{{ $user->email }}" placeholder="Enter Email">
                                                    @if ($errors->has('email'))
                                                        <span class="text-danger small">
                                                            {{ $errors->first('email') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="password" class="form-label">Change New Password <span class="text-danger">*</span></label>
                                                    <input type="password" id="password" class="form-control" name="password"
                                                        placeholder="Enter password" value="">
                                                        <button
                                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button" id="password-addon" style=" margin-top: 28px; margin-right: 35px;"><i
                                                            toggle="#password" class="ri-eye-fill align-middle toggle-password"></i></button>
                                                    @error('password')
                                                        <div class="error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div>
                                                    <label for="mobile" class="form-label">Mobile No <span class="text-danger">*</span></label>
                                                    <input type="number" id="mobile" class="form-control" name="mobile"
                                                        placeholder="Enter Mobile No" value="{{ $user->phone_number }}">
                                                    @error('mobile')
                                                        <div class="error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div>
                                                    <label for="status" class="form-label">Status</label>
                                                    <div class="d-flex">
                                                        <div class="form-check form-radio-success me-3">
                                                            <input class="form-check-input" type="radio" name="status" id="flexRadioDefault1" value="1" {{ $user->is_active == 1 ? 'checked' : ''}}>
                                                            <label class="form-check-label" for="flexRadioDefault1">Active</label>
                                                        </div>
                                                        <div class="form-check form-radio-danger ms-3">
                                                            <input class="form-check-input" type="radio" name="status" id="flexRadioDefault2" value="0" {{ $user->is_active == 0 ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="flexRadioDefault2">Inactive</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <hr> --}}
                                    {{-- <div class="align-items-center d-flex mb-2">
                                        <h5 class="mb-0 flex-grow-1">Permissions</h5>
                                    </div> --}}
                                    {{-- <div class="row" id="permissionAccordionWrapper">
                                        @foreach ($permissions as $page => $groupedPermissions)
                                            <div class="col-md-6 mb-3">
                                                <div class="accordion" id="permissionAccordion{{ $loop->index }}">
                                                    <div class="accordion-item  border border-success">
                                                        <h2 class="accordion-header bg-success" id="heading{{ $loop->index }}">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false"
                                                                    aria-controls="collapse{{ $loop->index }}">
                                                                {{ ucfirst($page) }}
                                                            </button>
                                                        </h2>
                                                        <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse"
                                                            aria-labelledby="heading{{ $loop->index }}"
                                                            data-bs-parent="#permissionAccordion{{ $loop->index }}">
                                                            <div class="accordion-body d-flex flex-wrap gap-3">
                                                                @foreach($groupedPermissions as $permission)
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            id="permission{{ $permission->id }}"
                                                                            name="permissions[]" value="{{ $permission->id }}">
                                                                        <label class="form-check-label"
                                                                            for="permission{{ $permission->id }}">{{ $permission->name }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div> --}}
                                    <div class="row">
                                        <div class="d-flex gap-3 mt-5 justify-content-center">
                                            {{-- <button type="reset" class="btn btn-danger">Clear</button> --}}
                                            <button type="submit" class="btn btn-success">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function () {
        $.validator.addMethod("strongPassword", function(value, element) {
            return /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/.test(value);
        }, "Password must contain uppercase, number, and special character.");

       $("#user-form").validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                },
                status: {
                    required: true
                },
                user_name: {
                    required: true
                },
                role_id: {
                    required: true
                },
                mobile: {
                    required: true,
                    number: true,
                    maxlength: 10,
                    minlength: 10
                },
            },
            messages: {
                role_name: {
                    required: "Please enter a role name",
                    maxlength: "Maximum 15 characters allowed"
                },
                status: {
                    required: "Please select a status"
                }
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },

            submitHandler: function (form) {
                form.submit();
            }
        });
    });
</script>

@endsection

