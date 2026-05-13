@extends('backend.layouts.master')

@section('title', 'Landing Pages')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">

                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Landing Pages</h5>
                                <div>
                                    <button id="deleteAllPages" class="btn btn-sm btn-danger ms-2 destroy" data-table-id="pages-table"
                                                    data-route="{{ route('admin.pages.deleteAll')}}">
                                        Delete All
                                    </button>
                                    <a href="{{ route('admin.pages.create') }}" class="btn btn-sm btn-success">Add New Page</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">

                                    <table id="scroll-vertical" class="table table-bordered dt-responsive nowrap w-100">

                                        <thead>
                                            <tr>
                                                <th>Page ID</th>
                                                <th>Name</th>
                                                <th>Location</th>
                                                <th>Url</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody></tbody>

                                    </table>

                                </div>
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

            var table = $('#scroll-vertical').DataTable({

                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ route('admin.pages.index') }}',
                    type: 'GET'
                },

                columns: [
                    { data: 'page_code', name: 'page_code', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'location', name: 'location' },
                    { data: 'url_slug', name: 'url_slug' },
                    { data: 'category', name: 'category' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });


            /* STATUS CHANGE */

            $(document).on('change','.changeStatus',function(){

                var id = $(this).data('id');
                var status = $(this).val();

                $.ajax({
                    url:"{{ route('admin.pages.index') }}",
                    type:"POST",
                    data:{
                        id:id,
                        status:status,
                        _token:"{{ csrf_token() }}"
                    },
                    success:function(res){
                        if(res.success){
                            toastr.success(res.message);
                        }
                    }
                });

            });

        });
    </script>

@endsection
