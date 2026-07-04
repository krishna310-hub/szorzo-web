<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Division;
use App\Models\Location;
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
            return DataTables::of(Client::with(['location', 'division', 'billing'])->latest())
                ->addIndexColumn()
                ->addColumn('location_name', fn ($row) => $row->location->location ?? '-')
                ->addColumn('division_name', fn ($row) => $row->division->name ?? '-')
                ->addColumn('billing_value', fn ($row) => $row->billing->value . '%' ?? '-')
                ->editColumn('signed_date', fn ($row) => $row->signed_date?->format('d-m-Y') ?? '-')
                ->editColumn('renewal_date', fn ($row) => $row->renewal_date?->format('d-m-Y') ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
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
        return view('backend.clients.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);
        Client::create($this->validatedData($request));
        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', Client::class);
        return view('backend.clients.edit', array_merge(
            ['client' => Client::findOrFail($id)],
            $this->formData()
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Client::class);
        Client::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Client::class);
        Client::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Client deleted successfully.']);
    }

    private function formData(): array
    {
        return [
            'locations' => Location::where('status', true)->orderBy('location')->get(),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'billings'  => Billing::where('status', true)->orderBy('value')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'client' => 'required|string|max:255',
            'billing_id' => 'nullable|exists:billings,id',
            'location_id' => 'nullable|exists:locations,id',
            'poc_name' => 'nullable|string|max:255',
            'signed_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:signed_date',
            'division_id' => 'nullable|exists:divisions,id',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:30',
            'status' => 'required|in:0,1',
        ]);
    }
}
