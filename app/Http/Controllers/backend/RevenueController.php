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
            return DataTables::of(Revenue::with(['candidates', 'candidate', 'client'])->latest())
                ->addIndexColumn()
                ->editColumn('invoice_date', fn ($row) => $row->invoice_date->format('d-m-Y'))
                ->editColumn('service_amount', fn ($row) => '₹'.number_format((float) $row->service_amount, 2))
                ->editColumn('total_amount', fn ($row) => '₹'.number_format((float) $row->total_amount, 2))
                ->addColumn('candidate_name', fn ($row) => e(
                    $row->candidates->isNotEmpty()
                        ? $row->candidates->pluck('candidate_name')->join(', ')
                        : ($row->candidate?->candidate_name ?? '-')
                ))
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
            ->whereDoesntHave('revenues')
            ->orderBy('candidate_name')
            ->get();
        $contractMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->contract_month)
            ? $request->contract_month
            : now()->format('Y-m');
        $contractCandidates = $this->contractReportsForMonth($contractMonth)
            ->pluck('candidate')
            ->sortBy('candidate_name')
            ->values();

        $contractClients = $contractCandidates->pluck('client')->filter()->unique('id')->sortBy('client')->values()
            ->each(function ($client) {
                $prev = Revenue::where('client_id', $client->id)->whereNotNull('client_address')->latest()->first();
                $client->setAttribute('invoice_address', $prev?->client_address ?? ($client->location?->location ?? ''));
                $client->setAttribute('invoice_gst', $prev?->client_gst_number ?? '');
            });

        $invoiceNumber = $this->nextInvoiceNumber();

        return view('backend.revenues.create', compact('candidates', 'contractCandidates', 'contractClients', 'contractMonth', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Revenue::class);
        $data = $this->validated($request);

        $isContract = ($data['invoice_type'] ?? '') === 'contract';
        $candidateIds = $isContract
            ? array_values(array_filter(array_map('intval', (array) ($data['candidate_ids'] ?? ($data['contract_candidate_id'] ? [$data['contract_candidate_id']] : [])))))
            : [(int) $data['candidate_id']];

        $candidates = $this->eligibleCandidates(
            $candidateIds,
            $data['invoice_type'],
            $data['contract_month'] ?? null,
            isset($data['client_id']) ? (int) $data['client_id'] : null,
        );

        DB::transaction(function () use ($data, $candidates) {
            $revenueData = $this->calculatedData($data, $candidates);
            $revenue = Revenue::create($revenueData);

            $month = $data['contract_month'] ?? null;
            $pivotData = [];
            foreach ($candidates as $cand) {
                $pivotData[$cand->id] = [
                    'contract_month' => $month,
                    'payable_salary' => $cand->getAttribute('contract_invoice_base') ?? $cand->onboarding_ctc,
                    'service_amount' => $cand->getAttribute('contract_invoice_service') ?? ($revenue->service_amount / max(1, count($candidates))),
                ];
            }
            $revenue->candidates()->sync($pivotData);
        });

        return redirect()->route('admin.revenues.index')->with('success', 'Revenue invoice created successfully.');
    }

    public function show(Revenue $revenue)
    {
        $this->authorize('read', Revenue::class);
        $revenue->load(['candidates.jobRole', 'candidate.jobRole', 'client']);
        [$amountInWords, $offeredCtcDisplay] = $this->invoiceDisplayValues($revenue);

        return view('backend.revenues.show', compact('revenue', 'amountInWords', 'offeredCtcDisplay'));
    }

    public function edit(Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $revenue->load(['candidate.clientRequirement.billing', 'client']);

        return view('backend.revenues.edit', compact('revenue'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $this->authorize('edit', Revenue::class);
        $data = $this->validated($request, $revenue);
        $candidate = Candidate::with(['clientRequirement.billing', 'client'])->findOrFail($revenue->candidate_id);
        unset($data['candidate_ids'], $data['contract_candidate_id'], $data['invoice_type']);
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
        $revenue->load(['candidates.jobRole', 'candidate.jobRole', 'client']);
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
            'client_id' => ['nullable', 'required_if:invoice_type,contract', 'integer', 'exists:clients,id'],
            'candidate_id' => ['nullable', 'required_if:invoice_type,fte', 'integer', 'exists:candidates,id'],
            'candidate_ids' => ['nullable', 'required_if:invoice_type,contract', 'array', 'min:1'],
            'candidate_ids.*' => ['integer', 'distinct', 'exists:candidates,id'],
            'contract_candidate_id' => 'nullable',
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

    private function eligibleCandidates(
        array $ids,
        string $type,
        ?string $contractMonth = null,
        ?int $selectedClientId = null,
    )
    {
        if ($type === 'contract') {
            $candidates = $this->contractReportsForMonth((string) $contractMonth)
                ->whereIn('candidate_id', $ids)
                ->pluck('candidate');
            if ($candidates->count() !== count($ids)) {
                throw ValidationException::withMessages(['candidate_ids' => 'One or more selected candidates are not available in the selected month Contract Report or are already invoiced.']);
            }
            if ($candidates->pluck('client_id')->unique()->count() !== 1) {
                throw ValidationException::withMessages(['candidate_ids' => 'All selected candidates must belong to the same client.']);
            }
            if ($selectedClientId === null || (int) $candidates->first()?->client_id !== $selectedClientId) {
                throw ValidationException::withMessages(['client_id' => 'The selected candidates must belong to the chosen client.']);
            }

            return $candidates->sortBy(fn ($candidate) => array_search($candidate->id, $ids, true))->values();
        }

        $query = Candidate::with(['clientRequirement.billing', 'client'])
            ->whereIn('id', $ids)
            ->whereDoesntHave('revenue')
            ->whereDoesntHave('revenues');

        $query->where('level_of_interview_id', 20)
            ->whereNotNull('onboarding_ctc')
            ->whereHas('clientRequirement', fn ($requirement) => $this->requirementMode($requirement, 1)->whereHas('billing'));

        $candidates = $query->get();
        if ($candidates->count() !== count($ids)) {
            throw ValidationException::withMessages(['candidate_id' => 'The selected FTE candidate is not eligible or is already invoiced.']);
        }

        return $candidates->sortBy(fn ($candidate) => array_search($candidate->id, $ids, true))->values();
    }

    private function calculatedData(array $data, $candidates): array
    {
        $candidateCollection = $candidates instanceof \Illuminate\Support\Collection
            ? $candidates
            : collect([$candidates]);
        $firstCandidate = $candidateCollection->first();

        $isContract = array_key_exists('invoice_type', $data)
            ? $data['invoice_type'] === 'contract'
            : $this->candidateHasRequirementMode($firstCandidate, 2);

        if ($isContract) {
            $base = (float) $candidateCollection->sum(fn ($c) => (float) ($c->getAttribute('contract_invoice_base') ?? $c->onboarding_ctc ?? 0));
            $service = (float) $candidateCollection->sum(fn ($c) => (float) ($c->getAttribute('contract_invoice_service') ?? 0));
            $distinctBillings = $candidateCollection->map(fn ($c) => (float) ($c->clientRequirement?->billing?->value ?? 0))->unique();
            $billing = $distinctBillings->count() === 1 ? $distinctBillings->first() : ($base > 0 ? round(($service / $base) * 100, 2) : 0);
        } else {
            $base = (float) $firstCandidate->onboarding_ctc;
            $billing = (float) $firstCandidate->clientRequirement?->billing?->value;
            $service = round($base * $billing / 100, 2);
        }

        $gst = round($service * (float) $data['gst_percentage'] / 100, 2);

        $data['candidate_id'] = $firstCandidate->id;
        $data['client_id'] = $data['client_id'] ?? $firstCandidate->client_id;
        $data['client_name'] = !empty($data['client_name']) ? $data['client_name'] : ($firstCandidate->client?->client ?? '');
        $data['onboarding_ctc'] = $base;
        $data['offered_ctc'] = $base;
        $data['billing_percentage'] = $billing;
        $data['service_amount'] = $service;
        $data['gst_amount'] = $gst;
        $data['total_amount'] = round($service + $gst, 2);

        if ($isContract && isset($data['contract_month'])) {
            $monthLabel = CarbonImmutable::createFromFormat('!Y-m', $data['contract_month'])->format('F Y');
            $candNames = $candidateCollection->pluck('candidate_name')->filter()->join(', ');
            $notesLines = array_filter([
                $data['notes'] ?? null,
                'Contract billing month: '.$monthLabel,
                $candNames ? 'Candidates: '.$candNames : null,
            ]);
            $data['notes'] = trim(implode("\n", $notesLines));
        }

        unset($data['candidate_ids'], $data['contract_candidate_id'], $data['invoice_type'], $data['contract_month']);

        return $data;
    }

    private function contractReportsForMonth(string $month)
    {
        $selectedMonth = CarbonImmutable::createFromFormat('!Y-m', $month)->startOfMonth();
        $salaryMonth = $selectedMonth->toDateString();
        $monthPrefix = $selectedMonth->format('Y-m');

        return ContractReport::with(['candidate.clientRequirement.billing', 'candidate.client.location'])
            ->whereDate('salary_month', $salaryMonth)
            ->whereHas('candidate', fn ($candidate) => $candidate
                ->where('status', true)
                ->where('mode_id', 2)
                ->whereNotNull('client_id')
                ->whereHas('client')
                ->whereDate('contract_from_date', '<=', $selectedMonth->endOfMonth()->toDateString())
                ->whereDate('contract_to_date', '>=', $selectedMonth->toDateString())
                ->whereDoesntHave('revenues', fn ($q) => $q->where('candidate_revenue.contract_month', $monthPrefix))
                ->whereHas('clientRequirement', fn ($requirement) => $this->requirementMode($requirement, [2, 3])
                    ->whereHas('billing')))
            ->get()
            ->each(function (ContractReport $report) {
                $requirement = $report->candidate->clientRequirement;
                // Match the Contract Report table exactly: its Total Revenue is
                // payable salary (after attendance/hours) multiplied by billing %.
                $base = (float) $report->payable_salary;
                $billing = (float) $requirement->billing?->value;
                $service = round($base * $billing / 100, 2);
                $report->candidate->setAttribute('contract_invoice_base', round($base, 2));
                $report->candidate->setAttribute('contract_invoice_service', $service);
                $report->candidate->setAttribute('contract_report_id', $report->id);
                $report->candidate->setAttribute('contract_is_hourly', (bool) $report->is_hourly);
                $report->candidate->setAttribute('contract_worked_hours', (float) ($report->worked_hours ?? 0));
                $report->candidate->setAttribute('contract_billing_percentage', $billing);
            });
    }

    private function requirementMode($query, int|array $modeId)
    {
        $modeIds = (array) $modeId;

        return $query->where(function ($query) use ($modeIds) {
            foreach ($modeIds as $id) {
                $query->orWhereJsonContains('mode_ids', $id)
                    ->orWhere(fn ($query) => $query
                        ->where(fn ($query) => $query->whereNull('mode_ids')->orWhereJsonLength('mode_ids', 0))
                        ->where('mode_id', $id));
            }
        });
    }

    private function candidateHasRequirementMode(Candidate $candidate, int|array $modeId): bool
    {
        $modes = array_map('intval', (array) ($candidate->clientRequirement?->mode_ids
            ?: array_filter([$candidate->clientRequirement?->mode_id])));
        $checkIds = (array) $modeId;

        return (bool) array_intersect($checkIds, $modes) || in_array((int) $candidate->mode_id, $checkIds, true);
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
