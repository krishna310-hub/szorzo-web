@extends('backend.layouts.master')
@section('title', 'Edit Invester - ' . $invester->name)

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Edit Invester</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Edit Investor: {{ $invester->name }} ({{ $invester->invester_id }})</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.investers.show', $invester->id) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show align-middle me-1"></i> View Profile
                                </a>
                                <a href="{{ route('admin.investers.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="ri-arrow-left-line align-middle me-1"></i> Back to Directory
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.investers.update', $invester->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                @include('backend.investers.form')
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line align-middle me-1"></i> Update Investor
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

