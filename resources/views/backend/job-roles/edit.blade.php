@extends('backend.layouts.master')

@section('title', 'Edit Job Role')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Job Role</h4>
                            <a href="{{ route('admin.job-roles.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.job-roles.update', $jobRole->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.job-roles.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
