<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ContactEnquiry::class);
        $enquiries = ContactEnquiry::orderBy('id','desc');

        if ($request->ajax()) {

            return DataTables::eloquent($enquiries)

                ->addIndexColumn()

                ->addColumn('name', function ($row) {
                    return $row->firstname.' '.$row->lastname;
                })

                ->addColumn('status', function ($row) {

                    $pending = $row->status == 'pending' ? 'selected' : '';
                    $reviewed = $row->status == 'reviewed' ? 'selected' : '';
                    $resolved = $row->status == 'resolved' ? 'selected' : '';

                    return '
                        <select class="form-select form-select-sm changeStatus" data-id="'.$row->id.'">
                            <option value="pending" '.$pending.'>Pending</option>
                            <option value="reviewed" '.$reviewed.'>Reviewed</option>
                            <option value="resolved" '.$resolved.'>Resolved</option>
                        </select>
                    ';
                })

                ->addColumn('action', function ($row) {

                    return '
                        <button data-route="'.route('admin.enquiry.delete', $row->id).'" class="btn btn-sm btn-danger destroy">
                            <i class="bx bxs-trash"></i>
                        </button>
                    ';
                })

                ->rawColumns(['status','action'])
                ->make(true);
        }

        return view('backend.contacts.index');
    }


    /*
    |--------------------------------------------------------------------------
    | Change Status
    |--------------------------------------------------------------------------
    */

    public function changeStatus(Request $request)
    {
        $this->authorize('edit', ContactEnquiry::class);
        $enquiry = ContactEnquiry::findOrFail($request->id);

        $enquiry->status = $request->status;
        $enquiry->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Enquiry
    |--------------------------------------------------------------------------
    */

    public function delete(Request $request, $id)
    {
        $this->authorize('delete', ContactEnquiry::class);
        $enquiry = ContactEnquiry::findOrFail($id);
        $enquiry->delete();

        return response()->json([
            'status' => true,
            'message' => 'Enquiry deleted successfully'
        ]);
    }
}