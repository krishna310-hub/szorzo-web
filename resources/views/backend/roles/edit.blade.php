@extends('backend.layouts.master')
@section('title')
    {{ 'Role' }}
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
                                <h4 class="card-title mb-0 flex-grow-1">Add Role</h4>
                            </div>
                            <div class="card-body">
                                <form id="role-form" action="{{ route('admin.role.update', $role->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="live-preview mb-3">
                                        <div class="row gy-4">
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="role_name" class="form-label">Role Name <span
                                                            class="text-danger"> *</span></label>
                                                    <input type="text" class="form-control" id="role_name"
                                                        name="role_name"value="{{ $role->name }}"
                                                        placeholder="Enter Role Name">
                                                    @if ($errors->has('role_name'))
                                                        <span class="text-danger small">
                                                            {{ $errors->first('role_name') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div>
                                                    <label for="status" class="form-label">Status</label>
                                                    <div class="d-flex">
                                                        <div class="form-check form-radio-success me-3">
                                                            <input class="form-check-input" type="radio" name="status"
                                                                id="flexRadioDefault1" value="1" checked>
                                                            <label class="form-check-label"
                                                                for="flexRadioDefault1">Active</label>
                                                        </div>
                                                        <div class="form-check form-radio-danger ms-3">
                                                            <input class="form-check-input" type="radio" name="status"
                                                                id="flexRadioDefault2" value="0">
                                                            <label class="form-check-label"
                                                                for="flexRadioDefault2">Inactive</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="align-items-center d-flex mb-2">
                                        <h5 class="mb-0 flex-grow-1">Permissions</h5>
                                    </div>
                                    <div class="row" id="permissionAccordionWrapper">
                                        @foreach ($permissions as $page => $groupedPermissions)
                                            <div class="col-md-6 mb-3">
                                                <div class="accordion" id="permissionAccordion{{ $loop->index }}">
                                                    <div class="accordion-item border border-success">
                                                        <h2 class="accordion-header"
                                                            style="background: linear-gradient(to right,  pink, purple,pink);"
                                                            id="heading{{ $loop->index }}">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse{{ $loop->index }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse{{ $loop->index }}">
                                                                {{ ucfirst($page) }}
                                                            </button>
                                                        </h2>

                                                        <div id="collapse{{ $loop->index }}"
                                                            class="accordion-collapse collapse"
                                                            aria-labelledby="heading{{ $loop->index }}"
                                                            data-bs-parent="#permissionAccordion{{ $loop->index }}">
                                                            <div class="accordion-body d-flex flex-wrap gap-3">
                                                                @foreach ($groupedPermissions as $permission)
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            id="permission{{ $permission->id }}"
                                                                            name="permissions[]"
                                                                            value="{{ $permission->id }}"
                                                                            @checked($role->permissions->contains('id', $permission->id))>
                                                                        <label class="form-check-label"
                                                                            for="permission{{ $permission->id }}">{{ $permission->name }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="row">
                                        <div class="d-flex gap-3 mt-5 justify-content-center">
                                            {{-- <button type="reset" class="btn btn-danger">Clear</button> --}}
                                            <button type="submit" class="btn btn-success">Update</button>
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
        $(document).ready(function() {
            // $("#role-form").validate({
            //     rules: {
            //         role_name: {
            //             required: true,
            //             maxlength: 15
            //         },
            //         status: {
            //             required: true
            //         }
            //     },
            //     messages: {
            //         role_name: {
            //             required: "Please enter a role name",
            //             maxlength: "Maximum 15 characters allowed"
            //         },
            //         status: {
            //             required: "Please select a status"
            //         }
            //     },
            //     highlight: function (element) {
            //         $(element).addClass("is-invalid").removeClass("is-valid");
            //     },
            //     unhighlight: function (element) {
            //         $(element).removeClass("is-invalid").addClass("is-valid");
            //     },
            //     errorElement: 'div',
            //     errorClass: 'invalid-feedback',
            //     errorPlacement: function (error, element) {
            //         error.insertAfter(element);
            //     }
            // });
        });
    </script>
@endsection
