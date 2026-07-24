<?php

namespace App\Http\Controllers\backend;

use App\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Division;
use App\Models\Location;
use App\Support\MasterDataSpreadsheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

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
                ->addColumn('billing_value', fn ($row) => $row->billing ? $row->billing->value.'%' : '-')
                ->addColumn('payment_cycle', function ($row) {
                    return $row->payment_cycle > 0
                        ? $row->payment_cycle . ' Days'
                        : '-';
                })
                ->editColumn('signed_date', fn ($row) => $row->signed_date?->format('d-m-Y') ?? '-')
                ->editColumn('renewal_date', fn ($row) => $row->renewal_date?->format('d-m-Y') ?? '-')
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->editColumn('created_at', fn ($row) => $row->created_at?->format('d-m-Y H:i:s') ?? '-')
                ->addColumn('agreement_preview', function ($row) {
                    if ($row->upload_agreement) {
                        return '<a href="'.asset($row->upload_agreement).'" target="_blank" class="btn btn-sm btn-outline-primary">View Agreement</a>';
                    }
                    return '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if (auth()->user()->can('edit', Client::class)) {
                        $buttons .= '<a href="'.route('admin.clients.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('delete', Client::class)) {
                        $buttons .= '<button type="button" data-route="'.route('admin.clients.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action', 'agreement_preview'])
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
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '25M');
        $this->authorize('create', Client::class);

        $data = $this->validatedData($request);

        if ($request->hasFile('upload_agreement')) {

            $file = $request->file('upload_agreement');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;

            $file->move(public_path('uploads/clients'), $filename);

            $data['upload_agreement'] = 'uploads/clients/' . $filename;
        }

        Client::create($data);

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

        $client = Client::findOrFail($id);

        $data = $this->validatedData($request, $client);

        if ($request->hasFile('upload_agreement')) {

            if ($client->upload_agreement && file_exists(public_path($client->upload_agreement))) {
                unlink(public_path($client->upload_agreement));
            }

            $file = $request->file('upload_agreement');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;
            $file->move(public_path('uploads/candidates'), $filename);

            $data['upload_agreement'] = 'uploads/candidates/' . $filename;
        }

        $client->update($data);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Client::class);
        Client::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Client deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('read', Client::class);

        $rows = Client::with(['billing', 'location', 'division'])->orderBy('client')->get()->map(fn ($client) => [
            $client->id,
            $client->client,
            $client->billing?->value,
            $client->location?->location,
            $client->poc_name,
            $client->signed_date?->format('Y-m-d'),
            $client->renewal_date?->format('Y-m-d'),
            $client->division?->name,
            $client->contact_number,
            $client->email,
            $client->mobile_number,
            $client->status ? 'Active' : 'Inactive',
        ])->all();

        return Excel::download(new MasterDataExport($this->importHeadings(), $rows), 'clients-'.now()->format('Y-m-d').'.xlsx');
    }

    public function importTemplate()
    {
        $this->authorize('create', Client::class);

        return Excel::download(new MasterDataExport($this->importHeadings(), [[
            null, 'Example Client', '8.5', 'Chennai', 'Contact Person', '2026-01-01', '2026-12-31',
            'Technology', '0441234567', 'contact@example.com', '9876543210', 'Active',
        ]]), 'clients-import-template.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorize('create', Client::class);
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        try {
            $rows = MasterDataSpreadsheet::rows($request->file('import_file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'The spreadsheet could not be read. Please use the provided template.');
        }

        if ($rows->isEmpty()) {
            return back()->with('error', 'The spreadsheet has no data rows.');
        }

        $validRows = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $number = $index + 2;
            $billingValue = MasterDataSpreadsheet::cleanPercent($row['billing'] ?? null);
            $location = $row['location'] ?? null;
            $division = $row['division'] ?? null;
            $status = MasterDataSpreadsheet::status($row['status'] ?? null);

            $data = [
                'id' => $row['record_id'] ?? null,
                'client' => trim((string) ($row['client'] ?? '')),
                'billing_id' => MasterDataSpreadsheet::lookupNumeric(Billing::class, 'value', $billingValue),
                'location_id' => MasterDataSpreadsheet::lookup(Location::class, 'location', $location),
                'poc_name' => MasterDataSpreadsheet::text($row['poc_name'] ?? null),
                'signed_date' => MasterDataSpreadsheet::date($row['signed_date'] ?? null),
                'renewal_date' => MasterDataSpreadsheet::date($row['renewal_date'] ?? null),
                'division_id' => MasterDataSpreadsheet::lookup(Division::class, 'name', $division),
                'contact_number' => MasterDataSpreadsheet::text($row['contact_number'] ?? null),
                'email' => MasterDataSpreadsheet::text($row['email'] ?? null),
                'mobile_number' => MasterDataSpreadsheet::text($row['mobile_number'] ?? null),
                'status' => $status,
            ];

            $validator = Validator::make($data, [
                'id' => 'nullable|integer|exists:clients,id',
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

            $validator->after(function ($validator) use ($billingValue, $location, $division, $data) {
                if ($billingValue !== null && $billingValue !== '' && ! $data['billing_id']) {
                    $validator->errors()->add('billing', 'Billing value was not found.');
                }
                if ($location !== null && trim((string) $location) !== '' && ! $data['location_id']) {
                    $validator->errors()->add('location', 'Location was not found.');
                }
                if ($division !== null && trim((string) $division) !== '' && ! $data['division_id']) {
                    $validator->errors()->add('division', 'Division was not found.');
                }
            });

            if ($validator->fails()) {
                $errors[] = 'Row '.$number.': '.implode(', ', $validator->errors()->all());
            } else {
                $validRows[] = $data;
            }
        }

        if ($errors) {
            return back()->with('error', 'Nothing was imported. '.MasterDataSpreadsheet::errors($errors));
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $data) {
                $id = $data['id'];
                unset($data['id']);
                $client = $id ? Client::findOrFail($id) : Client::whereRaw('LOWER(client) = ?', [mb_strtolower($data['client'])])->first();
                $client ? $client->update($data) : Client::create($data);
            }
        });

        return back()->with('success', count($validRows).' client row(s) imported successfully.');
    }

    private function formData(): array
    {
        return [
            'locations' => Location::where('status', true)->orderBy('location')->get(),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'billings' => Billing::where('status', true)->orderBy('value')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'client' => 'required|string|max:255',
            'billing_id' => 'nullable|exists:billings,id',
            'payment_cycle' => 'nullable|integer|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'poc_name' => 'nullable|string|max:255',
            'signed_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:signed_date',
            'division_id' => 'nullable|exists:divisions,id',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'upload_agreement' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'status' => 'required|in:0,1',
        ]);
    }

    private function importHeadings(): array
    {
        return ['Record ID', 'Client', 'Billing', 'Payment Cycle', 'Location', 'PoC Name', 'Signed Date', 'Renewal Date', 'Division', 'Contact Number', 'Email', 'Mobile Number', 'Status'];
    }
}
