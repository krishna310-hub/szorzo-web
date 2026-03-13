@extends('backend.layouts.master')

@section('title', 'Contact Enquiries')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">

                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Contact Enquiries</h5>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">

                                    <table id="scroll-vertical" class="table table-bordered dt-responsive nowrap w-100">

                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Company</th>
                                                <th>Phone</th>
                                                <th>Status</th>
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
                    url: '{{ route('admin.enquiry.index') }}',
                    type: 'GET'
                },

                columns: [
                    { data: 'DT_RowIndex', orderable:false, searchable:false },

                    { data: 'name', name:'name' },

                    { data: 'email', name:'email' },

                    { data: 'company', name:'company' },

                    { data: 'phone', name:'phone' },

                    { data: 'status', name:'status' },

                    { data: 'action', orderable:false, searchable:false }
                ]

            });


            /* STATUS CHANGE */

            $(document).on('change','.changeStatus',function(){

                var id = $(this).data('id');
                var status = $(this).val();

                $.ajax({
                    url:"{{ route('admin.enquiry.status') }}",
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
