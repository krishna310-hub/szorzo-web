@extends('backend.layouts.master')
@section('title', ($client->client ?? 'Client') . ' - Detailed Report')

@push('style')
<style>
    @media print {
        body, .main-content, .page-content {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .app-menu, .navbar-header, .page-title-box, .btn, .no-print, header, footer {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                {{-- Page Header --}}
                <div class="row no-print">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-3">
                            <h4 class="mb-sm-0 text-primary">
                                <i class="ri-pie-chart-line me-1"></i> {{ $client->client }} - Detailed Client Report
                            </h4>
                            <div class="page-title-right d-flex gap-2">
                                <a href="{{ route('admin.client-requirements.index') }}#client-reports" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri-arrow-left-line me-1"></i> Back to Client Requirements
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="ri-printer-line me-1"></i> Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Client Report Content --}}
                <div class="row">
                    <div class="col-lg-12">
                        @include('backend.client-requirements.partials.client-report-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

