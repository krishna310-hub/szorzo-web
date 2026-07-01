@extends('backend.layouts.master')

@section('title', 'Edit Recruiter')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Recruiter</h4>
                            <a href="{{ route('admin.recruiters.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.recruiters.update', $recruiter->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.recruiters.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
