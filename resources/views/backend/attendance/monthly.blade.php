@extends('backend.layouts.master')
@section('title','Monthly Attendance')
@section('content')
@include('backend.attendance._styles')
<div class="main-content atl-shell">
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-3">
                <h3>Monthly Attendance</h3>
                <a class="btn btn-outline-dark" href="{{route('admin.attendance.dashboard')}}">Dashboard</a>
            </div>

            <div class="card atl-card mb-3">
                <div class="card-body">
                    <form class="row g-2">
                        <div class="col-md-3">
                            <select name="month" class="form-select">
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{$m}}" @selected($month===$m)>{{date('F',mktime(0,0,0,$m,1))}}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input name="year" type="number" min="2000" max="2100" value="{{$year}}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <select name="employee_id" class="form-select">
                                <option value="">All internal employees</option>
                                @foreach($allEmployees as $emp)
                                    <option value="{{$emp->id}}" @selected(request('employee_id')==$emp->id)>{{$emp->employee_name}} ({{$emp->employee_no}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-dark w-100">View</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mb-3 d-flex flex-wrap gap-2">
                @foreach(['present'=>'P','absent'=>'A','half_day'=>'HD','on_leave'=>'L','holiday'=>'H','week_off'=>'WO','unmarked'=>'—'] as $s=>$code)
                    <span class="status-badge s-{{$s}}">{{$code}} {{ucwords(str_replace('_',' ',$s))}}</span>
                @endforeach
            </div>

            <div class="card atl-card">
                <div class="table-responsive">
                    <table class="table table-bordered attendance-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                @foreach($days as $day)
                                    <th class="calendar-cell">{{$day}}</th>
                                @endforeach
                                <th>P</th>
                                <th>A</th>
                                <th>HD</th>
                                <th>L</th>
                                <th>H</th>
                                <th>WO</th>
                                <th>UM</th>
                                <th>Working</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                @php
                                    $map = $employee->attendances->keyBy(fn($a) => (int)$a->attendance_date->day);
                                    $totals = array_fill_keys(\App\Models\Attendance::STATUSES, 0);
                                    $unmarked = 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{$employee->employee_name}}</strong><br>
                                        <small class="text-muted">{{$employee->employee_no}}</small>
                                    </td>
                                    @foreach($days as $day)
                                        @php
                                            $status = $map->get($day)?->status ?? 'unmarked';
                                            if ($status === 'unmarked') {
                                                $unmarked++;
                                            } else {
                                                $totals[$status]++;
                                            }
                                        @endphp
                                        <td class="calendar-cell">
                                            <span class="calendar-dot s-{{$status}}" title="{{ucwords(str_replace('_',' ',$status))}}">
                                                {{['present'=>'P','absent'=>'A','half_day'=>'½','on_leave'=>'L','holiday'=>'H','week_off'=>'W','unmarked'=>'—'][$status]}}
                                            </span>
                                        </td>
                                    @endforeach
                                    <td>{{$totals['present']}}</td>
                                    <td>{{$totals['absent']}}</td>
                                    <td>{{$totals['half_day']}}</td>
                                    <td>{{$totals['on_leave']}}</td>
                                    <td>{{$totals['holiday']}}</td>
                                    <td>{{$totals['week_off']}}</td>
                                    <td>{{$unmarked}}</td>
                                    <td>{{$totals['present']+$totals['absent']+$totals['half_day']+$totals['on_leave']+$unmarked}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{count($days)+9}}" class="text-center p-4">No eligible internal employees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{$employees->links()}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
