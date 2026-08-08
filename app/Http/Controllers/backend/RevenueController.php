<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Revenue;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Revenue::class);

        if ($request->ajax()) {
            return DataTables::of(Revenue::with(['candidate', 'client'])->latest())
                ->addIndexColumn()
                ->editColumn('invoice_date', fn ($row) => $row->invoice_date->format('d-m-Y'))
                ->editColumn('service_amount', fn ($row) => '₹'.number_format((float) $row->service_amount, 2))
                ->editColumn('total_amount', fn ($row) => '₹'.number_format((float) $row->total_amount, 2))
                ->addColumn('candidate_name', fn ($row) => e($row->candidate?->candidate_name ?? '-'))
                ->addColumn('action', function ($row) {
                    $actions = '<a href="'.route('admin.revenues.show', $row).'" class="text-primary fs-4 me-2" title="View"><i class="ri-eye-line"></i></a>';
                    if (auth()->user()->can('edit', Revenue::class)) {
                        $actions .= '<a href="'.route('admin.revenues.edit', $row).'" class="text-info fs-4 me-2" title="Edit"><i class="bx bxs-edit"></i></a>';
                    }
                    if (auth()->user()->can('download', Revenue::class)) {
                        $actions .= '<a href="'.route('admin.revenues.download', $row).'" class="text-success fs-4" title="Download PDF"><i class="ri-download-2-line"></i></a>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.revenues.index');
    }

    public function create()
    {
        $this->authorize('create', Revenue::class);
        $candidates = Candidate::with(['client.billing'])
            ->where('level_of_interview_id', 20)
            ->whereDoesntHave('revenue')
            ->orderBy('candidate_name')
            ->get();

        $invoiceNumber = $this->nextInvoiceNumber();
        return view('backend.revenues.create', compact('candidates', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Revenue::class);
        $data = $this->validated($request);
        $candidate = $this->eligibleCandidate((int) $data['candidate_id']);
        $data = $this->calculatedData($data, $candidate);
        DB::transaction(function () use ($data) {
            $data['invoice_number'] = $this->nextInvoiceNumber();
            Revenue::create($data);
        });

        return redirect()->route('admin.revenues.index')->with('success', 'Revenue invoice created successfully.');
    }

    public function show(Revenue $revenue)
    {
        $this->authorize('read', Revenue::class);
        $revenue->load(['candidate.jobRole', 'client']);
        $amountInWords = $this->amountInWords((float) $revenue->total_amount);
        return view('backend.revenues.show', compact('revenue', 'amountInWords'));
    }

    public function edit(Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $revenue->load(['candidate.client.billing']);
        return view('backend.revenues.edit', compact('revenue'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $data = $this->validated($request, $revenue);
        $candidate = $this->eligibleCandidate((int) $revenue->candidate_id);
        unset($data['candidate_id']);
        $revenue->update($this->calculatedData($data, $candidate));

        return redirect()->route('admin.revenues.index')->with('success', 'Revenue invoice updated successfully.');
    }

    public function download(Revenue $revenue)
    {
        $this->authorize('download', Revenue::class);
        $revenue->load(['candidate.jobRole', 'client']);
        $amountInWords = $this->amountInWords((float) $revenue->total_amount);
        $pdf = Pdf::loadView('backend.revenues.invoice', compact('revenue', 'amountInWords'))->setPaper('a4');
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', $revenue->invoice_number).'.pdf';
        return $pdf->download($name);
    }

    private function validated(Request $request, ?Revenue $revenue = null): array
    {
        return $request->validate([
            'candidate_id' => [$revenue ? 'nullable' : 'required', 'integer', 'exists:candidates,id',
                Rule::unique('revenues', 'candidate_id')->ignore($revenue?->id)],
            'invoice_number' => [$revenue ? 'required' : 'nullable', 'string', 'max:100',
                Rule::unique('revenues', 'invoice_number')->ignore($revenue?->id)],
            'invoice_date' => 'required|date',
            'universe_number' => 'nullable|string|max:100',
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string|max:2000',
            'client_gst_number' => 'nullable|string|max:30',
            'offered_ctc' => 'required|numeric|min:0',
            'billing_percentage' => 'required|numeric|min:0|max:100',
            'service_amount' => 'required|numeric|min:0',
            'gst_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    private function eligibleCandidate(int $id): Candidate
    {
        return Candidate::with(['client.billing'])
            ->whereKey($id)
            ->where('level_of_interview_id', 20)
            ->firstOrFail();
    }

    private function calculatedData(array $data, Candidate $candidate): array
    {
        $data['invoice_number'] ??= $this->nextInvoiceNumber();
        $service = round((float) $data['service_amount'], 2);
        $gst = round($service * (float) $data['gst_percentage'] / 100, 2);
        $data['candidate_id'] = $candidate->id;
        $data['client_id'] = $candidate->client_id;
        $data['service_amount'] = $service;
        $data['gst_amount'] = $gst;
        $data['total_amount'] = round($service + $gst, 2);
        return $data;
    }

    private function nextInvoiceNumber(): string
    {
        $date = now();
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;
        $yearLabel = $startYear.'-'.($startYear + 1);
        $latest = Revenue::where('invoice_number', 'like', 'SZ % '.$yearLabel)
            ->lockForUpdate()->get(['invoice_number'])->map(function ($revenue) {
                return preg_match('/^SZ\s+(\d+)/i', $revenue->invoice_number, $matches) ? (int) $matches[1] : 0;
            })->max() ?? 0;

        return 'SZ '.($latest + 1).' '.$yearLabel;
    }

    private function amountInWords(float $amount): string
    {
        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
        $words = $formatter->format((int) round($amount));
        return ucfirst(str_replace('-', ' ', $words)).' Rupees Only.';
    }
}
