@extends('backend.layouts.master')

@section('title', 'Add Client Job Role')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Add Client Job Role</h4>
                            <a href="{{ route('admin.client-job-roles.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.client-job-roles.store') }}" method="POST">
                                @csrf
                                @include('backend.client-job-roles.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
