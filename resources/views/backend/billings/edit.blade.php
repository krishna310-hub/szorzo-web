@extends('backend.layouts.master')
@section('title', 'Edit Billing')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Edit Billing %</h5><a
                                    href="{{ route('admin.billings.index') }}" class="btn btn-sm btn-light">Back</a>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.billings.update', $billing->id) }}" method="POST">@csrf
                                    @method('PUT') @include('backend.billings.form')</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
