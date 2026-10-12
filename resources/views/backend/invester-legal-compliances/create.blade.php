@extends('backend.layouts.master')
@section('title', 'Add Legal, Compliance & Governance')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Add Legal, Compliance & Governance</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.invester-legal-compliances.index') }}">Legal, Compliance & Governance</a></li>
                                <li class="breadcrumb-item active">Add</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Create Legal, Compliance & Governance Record</h5>
                            <a href="{{ route('admin.invester-legal-compliances.index') }}" class="btn btn-sm btn-secondary">
                                <i class="ri-arrow-left-line align-middle me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.invester-legal-compliances.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @include('backend.invester-legal-compliances.form')
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line align-middle me-1"></i> Save Record
                                    </button>
                                    <a href="{{ route('admin.invester-legal-compliances.index') }}" class="btn btn-secondary">Cancel</a>
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

