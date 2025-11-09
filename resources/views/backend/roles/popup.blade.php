{{-- <a href="javascript:void(0);" data-url="{{route('role.create')}}" data-size="modal-xl" data-title="Create Role" class="common_model btn btn-primary" data-key="t-chat">Add New Role</a> --}}

<div class="card">
    <div class="card-body">
        <form id="role-form" action="{{ route('admin.role.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="live-preview">
                <div class="row gy-4">
                    <div class="col-md-4">
                        <div>
                            <label for="role_name" class="form-label">Role Name <span class="text-danger"> *</span></label>
                            <input type="text" class="form-control" id="role_name" name="role_name" placeholder="Enter Role Name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <label for="resource_type" class="form-label">Resource Type <span class="text-danger"> *</span></label>
                        
                            <select class="form-select mb-3" name="resource_type" aria-label="Select Resource Type">
                                <option value = "" selected="">Select Resource Type </option>
                                @foreach ($resource_type as $data)
                                    <option value="{{$data->type}}">{{$data->resource_type}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <div class="d-flex">
                                <div class="form-check form-radio-success me-3">
                                    <input class="form-check-input" type="radio" name="status" id="flexRadioDefault1" value="0" >
                                    <label class="form-check-label" for="flexRadioDefault1">Active</label>
                                </div>
                                <div class="form-check form-radio-danger ms-3">
                                    <input class="form-check-input" type="radio" name="status" id="flexRadioDefault2" value="1" checked>
                                    <label class="form-check-label" for="flexRadioDefault2">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="d-flex gap-3 mt-5 justify-content-center">
                    <button type="reset" class="btn btn-danger">Clear</button>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
            {{-- <button type="submit" class="btn btn-success btn-label right nexttab">
                <i class="ri-check-line label-icon align-middle fs-16"></i>Submit
            </button> --}}
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>

<script>
    $(document).ready(function () {
        $("#role-form").validate({
            rules: {
                role_name: {
                    required: true,
                    maxlength: 5,
                },
                resource_type: {
                    required: true
                },
                status: {
                    required: true
                },
            },
            messages: {
                role_name: {
                    required: "Please Enter a Role Name",
                    maxlength: 'Reached Max Length',
                },
                resource_type: {
                    required: "Please enter a resource type"
                },
                status: {
                    required: "Please select a status"
                }
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).addClass("is-valid").removeClass("is-invalid");
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                if (element.attr("type") === "radio") {
                    error.insertAfter(element.closest('.d-flex'));
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>

