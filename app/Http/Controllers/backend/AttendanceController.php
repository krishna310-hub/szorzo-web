<?php
namespace App\Http\Controllers\backend;
use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

    public function dashboard()
    {
        $this->authorize('read', Attendance::class);
        $today=now()->toDateString(); $eligible=User::eligibleForAttendance()->count();
        $counts=Attendance::eligible()->whereDate('attendance_date',$today)->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status');
        $marked=(int)$counts->sum();
        $monthCounts=Attendance::eligible()->whereYear('attendance_date',now()->year)->whereMonth('attendance_date',now()->month)->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status');
        return view('backend.attendance.dashboard',['eligible'=>$eligible,'counts'=>$counts,'unmarked'=>max(0,$eligible-$marked),'monthCounts'=>$monthCounts,'recentAttendance'=>Attendance::eligible()->with('user')->latest('updated_at')->limit(8)->get(),'recentLeaves'=>LeaveRequest::eligible()->with('user')->latest()->limit(8)->get()]);
    }

    public function index(Request $request)
    {
        $this->authorize('read', Attendance::class);
        $filters = $request->validate(['date'=>['nullable','date_format:Y-m-d'],'user_id'=>['nullable','integer'],'resource_type'=>['nullable','string','max:100'],'status'=>['nullable','in:'.implode(',',Attendance::STATUSES)]]);
        $date = $filters['date'] ?? now()->toDateString();
        $users = User::eligibleForAttendance()->with(['attendances'=>fn($q)=>$q->whereDate('attendance_date',$date)])->when($filters['user_id']??null,fn($q,$id)=>$q->whereKey($id))->when($filters['resource_type']??null,fn($q,$v)=>$q->where('resource_type',$v))->orderBy('name')->paginate(50)->withQueryString();
        $recent = Attendance::eligible()->with('user')->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))->when($filters['user_id']??null,fn($q,$id)=>$q->where('user_id',$id))->latest('attendance_date')->latest('id')->paginate(20,['*'],'recent_page')->withQueryString();
        return view('backend.attendance.index',['users'=>$users,'recent'=>$recent,'date'=>$date,'filters'=>$filters,'allUsers'=>User::eligibleForAttendance()->orderBy('name')->get(['id','name']),'resourceTypes'=>User::eligibleForAttendance()->whereNotNull('resource_type')->distinct()->orderBy('resource_type')->pluck('resource_type')]);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $data = $request->validated();
        $existingUserIds=Attendance::whereDate('attendance_date',$data['attendance_date'])->whereIn('user_id',collect($data['records'])->pluck('user_id'))->pluck('user_id');
        if($existingUserIds->isNotEmpty() && ! $request->user()->can('edit',Attendance::class)) abort(403,'You do not have permission to edit existing attendance.');
        DB::transaction(function () use ($data,$request) {
            foreach ($data['records'] as $record) {
                $values = collect($record)->only(['status','check_in','check_out','remarks'])->all();
                if (!in_array($values['status'],['present','half_day'],true)) $values['check_in']=$values['check_out']=null;
                $attendance=Attendance::where('user_id',$record['user_id'])->whereDate('attendance_date',$data['attendance_date'])->first();
                $attendance ? $attendance->update($values+['marked_by'=>$request->user()->id,'leave_request_id'=>null]) : Attendance::create(['user_id'=>$record['user_id'],'attendance_date'=>$data['attendance_date']]+$values+['marked_by'=>$request->user()->id,'leave_request_id'=>null]);
            }
        });
        return back()->with('success','Attendance saved successfully.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('edit', Attendance::class);
        abort_unless(User::eligibleForAttendance()->whereKey($attendance->user_id)->exists(),404);
        $data=$request->validate(['status'=>['required','in:'.implode(',',Attendance::STATUSES)],'check_in'=>['nullable','date_format:H:i'],'check_out'=>['nullable','date_format:H:i','after:check_in'],'remarks'=>['nullable','string','max:1000']]);
        if (!in_array($data['status'],['present','half_day'],true)) $data['check_in']=$data['check_out']=null;
        $attendance->update($data+['marked_by'=>$request->user()->id,'leave_request_id'=>$data['status']==='on_leave'?$attendance->leave_request_id:null]);
        return back()->with('success','Attendance updated successfully.');
    }

    public function monthly(Request $request)
    {
        $this->authorize('read', Attendance::class);
        $validated=$request->validate(['month'=>['nullable','integer','between:1,12'],'year'=>['nullable','integer','between:2000,2100'],'user_id'=>['nullable','integer']]);
        $month=(int)($validated['month']??now()->month); $year=(int)($validated['year']??now()->year);
        $start=CarbonImmutable::create($year,$month,1); $days=range(1,$start->daysInMonth);
        $users=User::eligibleForAttendance()->when($validated['user_id']??null,fn($q,$id)=>$q->whereKey($id))->with(['attendances'=>fn($q)=>$q->whereBetween('attendance_date',[$start,$start->endOfMonth()])])->orderBy('name')->paginate(25)->withQueryString();
        return view('backend.attendance.monthly',compact('users','days','month','year')+['allUsers'=>User::eligibleForAttendance()->orderBy('name')->get(['id','name'])]);
    }

    public function report(Request $request)
    {
        $this->authorize('read', Attendance::class);
        [$records,$filters,$users,$summary]=$this->reportRecords($request,true);
        return view('backend.attendance.report',compact('records','filters','users','summary'));
    }

    public function export(Request $request,string $format)
    {
        $this->authorize('export', Attendance::class);
        abort_unless(in_array($format,['csv','xlsx','pdf','print'],true),404);
        [$records,$filters]=$this->reportRecords($request,false); $name='attendance-report-'.now()->format('Ymd-His');
        if ($format==='pdf') return Pdf::loadView('backend.attendance.pdf',compact('records','filters'))->setPaper('a4','landscape')->download($name.'.pdf');
        if ($format==='print') return view('backend.attendance.pdf',compact('records','filters')+['print'=>true]);
        return Excel::download(new AttendanceReportExport($records),$name.'.'.$format, $format==='csv'?\Maatwebsite\Excel\Excel::CSV:\Maatwebsite\Excel\Excel::XLSX);
    }

    private function reportRecords(Request $request,bool $paginate): array
    {
        $filters=$request->validate(['from_date'=>['nullable','date_format:Y-m-d'],'to_date'=>['nullable','date_format:Y-m-d','after_or_equal:from_date'],'month'=>['nullable','integer','between:1,12'],'year'=>['nullable','integer','between:2000,2100'],'user_id'=>['nullable','integer'],'status'=>['nullable','in:'.implode(',',Attendance::STATUSES)]]);
        $query=Attendance::eligible()->with(['user','leaveRequest'])->when($filters['user_id']??null,fn($q,$v)=>$q->where('user_id',$v))->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))->when($filters['from_date']??null,fn($q,$v)=>$q->whereDate('attendance_date','>=',$v))->when($filters['to_date']??null,fn($q,$v)=>$q->whereDate('attendance_date','<=',$v))->when($filters['month']??null,fn($q,$v)=>$q->whereMonth('attendance_date',$v))->when($filters['year']??null,fn($q,$v)=>$q->whereYear('attendance_date',$v))->latest('attendance_date')->latest('id');
        $summary=(clone $query)->reorder()->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status');
        $records=$paginate?$query->paginate(50)->withQueryString():$query->get();
        return [$records,$filters,User::eligibleForAttendance()->orderBy('name')->get(['id','name']),$summary];
    }
}
