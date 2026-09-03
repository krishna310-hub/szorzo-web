<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\ContractReport;
use App\Models\Revenue;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

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

    public function create(Request $request)
    {
        $this->authorize('create', Revenue::class);
        $candidates = Candidate::with(['clientRequirement.billing', 'client'])
            ->where('level_of_interview_id', 20)
            ->whereNotNull('onboarding_ctc')
            ->whereHas('clientRequirement', fn ($query) => $this->requirementMode($query, 1)->whereHas('billing'))
            ->whereDoesntHave('revenue')
            ->orderBy('candidate_name')
            ->get();
        $contractMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->contract_month)
            ? $request->contract_month
            : now()->format('Y-m');
        $contractCandidates = $this->contractReportsForMonth($contractMonth)
            ->pluck('candidate')
            ->sortBy('candidate_name')
            ->values();

        $invoiceNumber = $this->nextInvoiceNumber();

        return view('backend.revenues.create', compact('candidates', 'contractCandidates', 'contractMonth', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Revenue::class);
        $data = $this->validated($request);
        $candidateIds = $data['invoice_type'] === 'contract'
            ? array_values(array_unique(array_map('intval', $data['candidate_ids'])))
            : [(int) $data['candidate_id']];
        $candidates = $this->eligibleCandidates($candidateIds, $data['invoice_type'], $data['contract_month'] ?? null);

        DB::transaction(function () use ($data, $candidates) {
            foreach ($candidates as $index => $candidate) {
                $invoiceData = $this->calculatedData($data, $candidate);
                $invoiceData['invoice_number'] = $this->invoiceNumberForSelection($data['invoice_number'], $index);
                Revenue::create($invoiceData);
            }
        });

        return redirect()->route('admin.revenues.index')->with('success', $candidates->count().' revenue invoice(s) created successfully.');
    }

    public function show(Revenue $revenue)
    {
        $this->authorize('read', Revenue::class);
        $revenue->load(['candidate.jobRole', 'client']);
        [$amountInWords, $offeredCtcDisplay] = $this->invoiceDisplayValues($revenue);

        return view('backend.revenues.show', compact('revenue', 'amountInWords', 'offeredCtcDisplay'));
    }

    public function edit(Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $revenue->load(['candidate.clientRequirement.billing', 'candidate.client']);

        return view('backend.revenues.edit', compact('revenue'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $data = $this->validated($request, $revenue);
        $candidate = Candidate::with(['clientRequirement.billing', 'client'])->findOrFail($revenue->candidate_id);
        unset($data['candidate_ids'], $data['invoice_type']);
        if ($this->candidateHasRequirementMode($candidate, 2)) {
            $data['candidate_id'] = $candidate->id;
            $data['client_id'] = $candidate->client_id;
            $data['onboarding_ctc'] = $revenue->onboarding_ctc;
            $data['offered_ctc'] = $revenue->offered_ctc;
            $data['billing_percentage'] = $revenue->billing_percentage;
            $data['service_amount'] = $revenue->service_amount;
            $data['gst_amount'] = round((float) $revenue->service_amount * (float) $data['gst_percentage'] / 100, 2);
            $data['total_amount'] = round((float) $revenue->service_amount + $data['gst_amount'], 2);
            unset($data['contract_month']);
            $revenue->update($data);
        } else {
            $revenue->update($this->calculatedData($data, $candidate));
        }

        return redirect()->route('admin.revenues.index')->with('success', 'Revenue invoice updated successfully.');
    }

    public function download(Revenue $revenue)
    {
        $this->authorize('download', Revenue::class);
        $revenue->load(['candidate.jobRole', 'client']);
        [$amountInWords, $offeredCtcDisplay] = $this->invoiceDisplayValues($revenue);
        $pdf = Pdf::loadView('backend.revenues.invoice', compact('revenue', 'amountInWords', 'offeredCtcDisplay'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'Helvetica',
                'dpi' => 96,
            ]);
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', $revenue->invoice_number).'.pdf';

        return $pdf->download($name);
    }

    private function validated(Request $request, ?Revenue $revenue = null): array
    {
        return $request->validate([
            'invoice_type' => [$revenue ? 'nullable' : 'required', Rule::in(['fte', 'contract'])],
            'candidate_id' => ['nullable', 'required_if:invoice_type,fte', 'integer', 'exists:candidates,id'],
            'candidate_ids' => ['nullable', 'required_if:invoice_type,contract', 'array', 'min:1'],
            'candidate_ids.*' => ['integer', 'distinct', 'exists:candidates,id', Rule::unique('revenues', 'candidate_id')],
            'contract_month' => ['nullable', 'required_if:invoice_type,contract', 'date_format:Y-m'],
            'invoice_number' => ['required', 'string', 'max:100',
                Rule::unique('revenues', 'invoice_number')->ignore($revenue?->id)],
            'invoice_date' => 'required|date',
            'universe_number' => 'nullable|string|max:100',
            'client_name' => [$revenue ? 'required' : 'nullable', 'string', 'max:255'],
            'client_address' => 'nullable|string|max:2000',
            'client_gst_number' => 'nullable|string|max:30',
            'onboarding_ctc' => 'nullable|numeric|min:0',
            'billing_percentage' => [$revenue ? 'required' : 'nullable', 'numeric', 'min:0', 'max:100'],
            'service_amount' => [$revenue ? 'required' : 'nullable', 'numeric', 'min:0'],
            'gst_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    private function eligibleCandidates(array $ids, string $type, ?string $contractMonth = null)
    {
        if ($type === 'contract') {
            $candidates = $this->contractReportsForMonth((string) $contractMonth)
                ->whereIn('candidate_id', $ids)
                ->pluck('candidate');
            if ($candidates->count() !== count($ids)) {
                throw ValidationException::withMessages(['candidate_ids' => 'One or more candidates are not available in the selected month Contract Report or are already invoiced.']);
            }
            if ($candidates->pluck('client_id')->unique()->count() !== 1) {
                throw ValidationException::withMessages(['candidate_ids' => 'All selected candidates must belong to the same client.']);
            }

            return $candidates->sortBy(fn ($candidate) => array_search($candidate->id, $ids, true))->values();
        }

        $query = Candidate::with(['clientRequirement.billing', 'client'])
            ->whereIn('id', $ids)
            ->whereDoesntHave('revenue');

        $query->where('level_of_interview_id', 20)
            ->whereNotNull('onboarding_ctc')
            ->whereHas('clientRequirement', fn ($requirement) => $this->requirementMode($requirement, 1)->whereHas('billing'));

        $candidates = $query->get();
        if ($candidates->count() !== count($ids)) {
            throw ValidationException::withMessages(['candidate_ids' => 'One or more selected candidates are not eligible or are already invoiced.']);
        }
        if ($candidates->pluck('client_id')->unique()->count() !== 1) {
            throw ValidationException::withMessages(['candidate_ids' => 'All selected candidates must belong to the same client.']);
        }

        return $candidates->sortBy(fn ($candidate) => array_search($candidate->id, $ids, true))->values();
    }

    private function calculatedData(array $data, Candidate $candidate): array
    {
        // Creation must follow the selected invoice type. On edit, where the
        // type is not posted, derive it from the candidate's mapped requirement.
        $isContract = array_key_exists('invoice_type', $data)
            ? $data['invoice_type'] === 'contract'
            : $this->candidateHasRequirementMode($candidate, 2);
        $base = $isContract
            ? (float) $candidate->getAttribute('contract_invoice_base')
            : (float) $candidate->onboarding_ctc;
        $billing = (float) $candidate->clientRequirement?->billing?->value;
        $service = $isContract
            ? (float) $candidate->getAttribute('contract_invoice_service')
            : round($base * $billing / 100, 2);
        $gst = round($service * (float) $data['gst_percentage'] / 100, 2);
        $data['candidate_id'] = $candidate->id;
        $data['client_id'] = $candidate->client_id;
        $data['client_name'] = $candidate->client?->client ?? $data['client_name'];
        $data['onboarding_ctc'] = $base;
        $data['offered_ctc'] = $base;
        $data['billing_percentage'] = $billing;
        $data['service_amount'] = $service;
        $data['gst_amount'] = $gst;
        $data['total_amount'] = round($service + $gst, 2);
        if ($isContract && isset($data['contract_month'])) {
            $monthLabel = CarbonImmutable::createFromFormat('!Y-m', $data['contract_month'])->format('F Y');
            $data['notes'] = trim(implode("\n", array_filter([$data['notes'] ?? null, 'Contract billing month: '.$monthLabel])));
        }
        unset($data['candidate_ids'], $data['invoice_type'], $data['contract_month']);

        return $data;
    }

    private function contractReportsForMonth(string $month)
    {
        $salaryMonth = CarbonImmutable::createFromFormat('!Y-m', $month)->startOfMonth()->toDateString();

        return ContractReport::with(['candidate.clientRequirement.billing', 'candidate.client'])
            ->whereDate('salary_month', $salaryMonth)
            ->whereHas('candidate', fn ($candidate) => $candidate
                ->where('status', true)
                ->whereNotNull('client_id')
                ->whereHas('client')
                ->whereDoesntHave('revenue')
                ->whereHas('clientRequirement', fn ($requirement) => $this->requirementMode($requirement, 2)
                    ->whereHas('billing')))
            ->get()
            ->each(function (ContractReport $report) {
                $requirement = $report->candidate->clientRequirement;
                $base = $report->is_hourly
                    ? (float) $requirement->ctc * (float) ($report->worked_hours ?? 0)
                    : (float) $report->monthly_take_home;
                $billing = (float) $requirement->billing?->value;
                $service = $report->is_hourly
                    ? round(round((float) $requirement->ctc * $billing / 100, 2) * (float) ($report->worked_hours ?? 0), 2)
                    : round((float) $report->monthly_take_home * $billing / 100, 2);
                $report->candidate->setAttribute('contract_invoice_base', round($base, 2));
                $report->candidate->setAttribute('contract_invoice_service', $service);
                $report->candidate->setAttribute('contract_report_id', $report->id);
                $report->candidate->setAttribute('contract_is_hourly', (bool) $report->is_hourly);
                $report->candidate->setAttribute('contract_worked_hours', (float) ($report->worked_hours ?? 0));
            });
    }

    private function requirementMode($query, int $modeId)
    {
        return $query->where(function ($query) use ($modeId) {
            $query->whereJsonContains('mode_ids', $modeId)
                ->orWhere(fn ($query) => $query
                    ->where(fn ($query) => $query->whereNull('mode_ids')->orWhereJsonLength('mode_ids', 0))
                    ->where('mode_id', $modeId));
        });
    }

    private function candidateHasRequirementMode(Candidate $candidate, int $modeId): bool
    {
        $modes = $candidate->clientRequirement?->mode_ids
            ?: array_filter([$candidate->clientRequirement?->mode_id]);

        return in_array($modeId, array_map('intval', $modes), true);
    }

    private function invoiceNumberForSelection(string $base, int $index): string
    {
        if ($index === 0) {
            return $base;
        }

        $suffix = $index + 1;
        $candidate = $base.'-'.$suffix;
        while (Revenue::where('invoice_number', $candidate)->exists()) {
            $candidate = $base.'-'.++$suffix;
        }

        return $candidate;
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

        return mb_convert_case(str_replace('-', ' ', $words), MB_CASE_TITLE, 'UTF-8').' Rupees Only.';
    }

    private function invoiceDisplayValues(Revenue $revenue): array
    {
        return [
            $this->amountInWords((float) $revenue->total_amount),
            $this->indianNumber((float) ($revenue->offered_ctc ?? $revenue->onboarding_ctc)),
        ];
    }

    private function indianNumber(float $amount): string
    {
        $whole = (string) (int) round($amount);
        if (strlen($whole) <= 3) {
            return $whole;
        }

        $lastThree = substr($whole, -3);
        $leading = substr($whole, 0, -3);
        $groups = [];
        while ($leading !== '') {
            array_unshift($groups, substr($leading, -2));
            $leading = substr($leading, 0, -2);
        }

        return implode(',', $groups).','.$lastThree;
    }
}
