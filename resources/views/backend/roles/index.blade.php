@extends('backend.layouts.master')

@section('title', 'Role')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Roles</h5>
                            <div class="ms-2">
                                <a href="{{ route('admin.role.create') }}" class="btn btn-sm btn-primary">Add New Role</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="role-table" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Name</th>
                                            <th>Access Level</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>

        </div> <!-- container-fluid -->
    </div> <!-- End Page-content -->
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('#role-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.role.index') }}',
                type: 'GET'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'access_level', name: 'access_level' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
