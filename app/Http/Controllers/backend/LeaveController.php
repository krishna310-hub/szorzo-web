<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class LeaveController extends Controller {
    use AuthorizesRequests;
    public function index(Request $request) {
        $this->authorize('read',LeaveRequest::class); $canApprove=$request->user()->can('approve',LeaveRequest::class);
        $filters=$request->validate(['status'=>['nullable','in:'.implode(',',LeaveRequest::STATUSES)]]);
        $leaves=LeaveRequest::eligible()->with(['user','reviewer'])->when(!$canApprove,fn($q)=>$q->where('user_id',$request->user()->id))->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))->latest()->paginate(25)->withQueryString();
        return view('backend.leaves.index',compact('leaves','canApprove','filters'));
    }
    public function create() { $this->authorize('create',LeaveRequest::class); return view('backend.leaves.create'); }
    public function store(StoreLeaveRequest $request) {
        $data=$request->validated(); $from=CarbonImmutable::parse($data['from_date']); $to=CarbonImmutable::parse($data['to_date']);
        $conflict=LeaveRequest::where('user_id',$request->user()->id)->whereIn('status',['pending','approved'])->where('from_date','<=',$to)->where('to_date','>=',$from)->exists();
        if($conflict) throw ValidationException::withMessages(['from_date'=>'This period overlaps an existing pending or approved leave request.']);
        $data['number_of_days']=$from->diffInDays($to)+1; $data['user_id']=$request->user()->id;
        if($request->hasFile('attachment')) $data['attachment']=$request->file('attachment')->store('leave-attachments','public');
        LeaveRequest::create($data); return redirect()->route('admin.leaves.index')->with('success','Leave request submitted.');
    }
    public function review(Request $request,LeaveRequest $leave) {
        $this->authorize('approve',LeaveRequest::class); abort_unless($leave->user()->eligibleForAttendance()->exists(),404);
        $data=$request->validate(['status'=>['required','in:approved,rejected'],'review_remarks'=>['nullable','string','max:2000']]);
        if($leave->status!=='pending') return back()->with('error','Only pending leave requests can be reviewed.');
        DB::transaction(function() use($leave,$data,$request){
            $leave->update($data+['reviewed_by'=>$request->user()->id,'reviewed_at'=>now()]);
            if($data['status']==='approved') for($date=CarbonImmutable::parse($leave->from_date);$date->lte($leave->to_date);$date=$date->addDay()) {
                $values=['status'=>'on_leave','check_in'=>null,'check_out'=>null,'remarks'=>'Approved '.$leave->leave_type,'leave_request_id'=>$leave->id,'marked_by'=>$request->user()->id];
                $attendance=Attendance::where('user_id',$leave->user_id)->whereDate('attendance_date',$date->toDateString())->first();
                $attendance ? $attendance->update($values) : Attendance::create(['user_id'=>$leave->user_id,'attendance_date'=>$date->toDateString()]+$values);
            }
        }); return back()->with('success','Leave request '.$data['status'].'.');
    }
    public function attachment(LeaveRequest $leave) { $this->authorize('view',$leave); abort_unless($leave->attachment&&Storage::disk('public')->exists($leave->attachment),404); return Storage::disk('public')->download($leave->attachment); }
}
