@extends('backend.layouts.master')
@section('title', 'Add New Invester')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Add New Invester</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">Add New</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Create Investor Profile</h5>
                            <a href="{{ route('admin.investers.index') }}" class="btn btn-sm btn-secondary">
                                <i class="ri-arrow-left-line align-middle me-1"></i> Back to Directory
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.investers.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                @csrf
                                @include('backend.investers.form')
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line align-middle me-1"></i> Save Investor
                                    </button>
                                    <a href="{{ route('admin.investers.index') }}" class="btn btn-secondary">Cancel</a>
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

