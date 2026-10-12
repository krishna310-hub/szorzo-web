@extends('backend.layouts.master')
@section('title', 'Edit Team & Management Scope')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Edit Team & Management Scope</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.invester-team-managements.index') }}">Team & Management Scope</a></li>
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
                            <h5 class="card-title mb-0 flex-grow-1">Edit Team & Management Record: {{ $teamManagement->team_name }}</h5>
                            <a href="{{ route('admin.invester-team-managements.index') }}" class="btn btn-sm btn-secondary">
                                <i class="ri-arrow-left-line align-middle me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.invester-team-managements.update', $teamManagement->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.invester-team-managements.form')
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line align-middle me-1"></i> Update Record
                                    </button>
                                    <a href="{{ route('admin.invester-team-managements.index') }}" class="btn btn-secondary">Cancel</a>
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

