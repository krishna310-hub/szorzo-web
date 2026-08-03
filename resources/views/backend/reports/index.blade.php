@extends('backend.layouts.master')
@section('title', 'Reports')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div><h4 class="mb-1">Recruitment Reports</h4><div class="text-muted">{{ $scopeLabel }}</div></div>
        @can('export', \App\Models\Report::class)
            <div class="d-flex gap-2">
                <a class="btn btn-danger" href="{{ route('admin.reports.pdf', request()->query()) }}"><i class="ri-file-pdf-2-line me-1"></i>Download PDF</a>
                <a class="btn btn-success" href="{{ route('admin.reports.export', request()->query()) }}"><i class="ri-file-excel-2-line me-1"></i>Export CSV</a>
            </div>
        @endcan
    </div>

    @if (!$filters['linked'])
        <div class="alert alert-warning">Your login email is not linked to a recruiter record. Ask an administrator to use the same email in Recruiter Master.</div>
    @endif

    <div class="card"><div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
            @if (!$filters['is_recruiter'])
            <div class="col-lg-4"><label class="form-label">Recruiter</label><select name="recruiter_id" class="form-select">
                <option value="">All recruiters</option>
                @foreach($recruiters as $recruiter)<option value="{{ $recruiter->id }}" @selected((int)$filters['recruiter_id'] === $recruiter->id)>{{ $recruiter->recruiter_name }}</option>@endforeach
            </select></div>
            @endif
            <div class="col-md-3"><label class="form-label">From date</label><input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">To date</label><input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="form-control"></div>
            <div class="col-auto"><button class="btn btn-primary">Apply</button> <a href="{{ route('admin.reports.index') }}" class="btn btn-light">Reset</a></div>
        </form>
        @error('from_date')<div class="text-danger mt-2">{{ $message }}</div>@enderror
        @error('to_date')<div class="text-danger mt-2">{{ $message }}</div>@enderror
    </div></div>

    <div class="row"><div class="col-xl-4"><div class="card bg-primary text-white"><div class="card-body">
        <div class="text-uppercase opacity-75">Total candidates</div><div class="display-5 fw-semibold">{{ number_format($total) }}</div><div>{{ $scopeLabel }}</div>
    </div></div></div></div>

    <div class="row">
        @foreach([['Recruiter report', $recruiterReport, true], ['Client report', $clientReport, false], ['Level of interview report', $levelReport, false]] as [$title, $rows, $drilldown])
        <div class="col-xl-{{ $loop->last ? 12 : 6 }}"><div class="card"><div class="card-header"><h5 class="card-title mb-0">{{ $title }}</h5></div><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Name</th><th class="text-end">Count</th><th class="text-end">Percentage</th>@if($drilldown && !$filters['is_recruiter'])<th></th>@endif</tr></thead><tbody>
            @forelse($rows as $row)<tr><td>{{ $row->label }}</td><td class="text-end fw-semibold">{{ number_format($row->total) }}</td><td class="text-end">{{ number_format($row->percentage, 2) }}%</td>
                @if($drilldown && !$filters['is_recruiter'])<td class="text-end">@if($row->item_id)<div class="d-flex justify-content-end gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.index', array_merge(request()->except('page'), ['recruiter_id' => $row->item_id])) }}">View</a>@can('export', \App\Models\Report::class)<a class="btn btn-sm btn-outline-danger" title="Download individual PDF" href="{{ route('admin.reports.pdf', array_merge(request()->except(['page', 'recruiter_id']), ['recruiter_id' => $row->item_id])) }}"><i class="ri-file-pdf-2-line"></i></a>@endcan</div>@endif</td>@endif
            </tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No candidate data found.</td></tr>@endforelse
            </tbody><tfoot><tr class="fw-bold"><td>Total Result</td><td class="text-end">{{ number_format($total) }}</td><td class="text-end">{{ $total ? '100.00%' : '0.00%' }}</td>@if($drilldown && !$filters['is_recruiter'])<td></td>@endif</tr></tfoot></table>
        </div></div></div></div>
        @endforeach
    </div>
</div></div></div>
@endsection
