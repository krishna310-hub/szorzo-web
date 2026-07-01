@extends('backend.layouts.master')

@section('title', 'Add Client Requirement')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Add Client Requirement</h4>
                            <a href="{{ route('admin.client-requirements.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.client-requirements.store') }}" method="POST">
                                @csrf
                                @include('backend.client-requirements.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
