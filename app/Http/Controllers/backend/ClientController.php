<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Client::class);

        if ($request->ajax()) {
            $clients = Client::latest();

            return DataTables::of($clients)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success-subtle text-success">Active</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', Client::class)) {
                        $buttons .= '<a href="' . route('admin.clients.edit', $row->id) . '" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }

                    if (auth()->user()->can('delete', Client::class)) {
                        $buttons .= '<button type="button" data-route="' . route('admin.clients.delete', $row->id) . '" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.clients.index');
    }

    public function create()
    {
        $this->authorize('create', Client::class);
        return view('backend.clients.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:clients,name',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_no' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);

        $client = Client::create($data);

        if (!$client->client_code) {
            $client->update(['client_code' => '#CL' . $client->id]);
        }

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Client::class);
        $client = Client::findOrFail($id);

        return view('backend.clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Client::class);
        $client = Client::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:clients,name,' . $client->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_no' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);

        $client->update($data);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Client::class);
        Client::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Client deleted successfully.']);
    }
}
