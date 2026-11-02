<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\PettyCash;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class PettyCashController extends Controller
{
    /**
     * Enforce strict admin-only authorization for all Petty Cash actions.
     */
    protected function authorizeAdmin(): void
    {
        $user = Auth::user();
        $isAuthorized = $user && (
            $user->id === 1 ||
            (int) $user->role_id === 1 ||
            $user->isAdmin() ||
            $user->isSuperAdmin()
        );

        abort_unless($isAuthorized, 403, 'Unauthorized access. The Petty Cash (Petition Amount) module is restricted to administrators only.');
    }

    /**
     * Display a listing of petty cash transactions or DataTables payload.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        if ($request->ajax()) {
            $query = PettyCash::query()->with('creator:id,name');

            // Date Range Filter
            if ($request->filled('from_date')) {
                $query->whereDate('transaction_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('transaction_date', '<=', $request->to_date);
            }

            // Transaction Type Filter (credit = In, debit = Out)
            if ($request->filled('transaction_type') && in_array($request->transaction_type, ['credit', 'debit'], true)) {
                $query->where('transaction_type', $request->transaction_type);
            }

            // Category Filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Search Keyword
            if ($request->filled('search_keyword')) {
                $search = '%' . trim($request->search_keyword) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('spent_by', 'like', $search)
                        ->orWhere('reference_no', 'like', $search)
                        ->orWhere('notes', 'like', $search)
                        ->orWhere('payment_mode', 'like', $search);
                });
            }

            $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('transaction_date', function ($row) {
                    $dateStr = $row->transaction_date ? $row->transaction_date->format('d M Y') : '-';
                    $timeStr = $row->transaction_time ? '<span class="text-muted small d-block">' . Carbon::parse($row->transaction_time)->format('h:i A') . '</span>' : '';
                    return '<div><strong>' . $dateStr . '</strong>' . $timeStr . '</div>';
                })
                ->editColumn('transaction_type', function ($row) {
                    if ($row->transaction_type === 'credit') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12 fw-semibold">
                                    <i class="ri-arrow-down-circle-line me-1"></i> Cash In (Fund)
                                </span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-12 fw-semibold">
                                <i class="ri-arrow-up-circle-line me-1"></i> Cash Out (Expense)
                            </span>';
                })
                ->editColumn('category', function ($row) {
                    return '<span class="badge bg-light text-dark border px-2 py-1">' . e($row->category) . '</span>';
                })
                ->editColumn('title', function ($row) {
                    $html = '<div class="fw-semibold text-dark">' . e($row->title) . '</div>';
                    if ($row->notes) {
                        $html .= '<div class="text-muted small text-truncate" style="max-width: 280px;" title="' . e($row->notes) . '">' . e($row->notes) . '</div>';
                    }
                    return $html;
                })
                ->editColumn('spent_by', function ($row) {
                    return e($row->spent_by ?: '-');
                })
                ->editColumn('payment_mode', function ($row) {
                    return '<span class="badge bg-secondary-subtle text-secondary px-2 py-1">' . e($row->payment_mode ?: 'Cash') . '</span>';
                })
                ->addColumn('in_amount', function ($row) {
                    if ($row->transaction_type === 'credit') {
                        return '<span class="text-success fw-bold">+₹' . number_format((float) $row->amount, 2) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('out_amount', function ($row) {
                    if ($row->transaction_type === 'debit') {
                        return '<span class="text-danger fw-bold">-₹' . number_format((float) $row->amount, 2) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('receipt', function ($row) {
                    if ($row->receipt_file) {
                        return '<a href="' . route('admin.petty-cash.receipt', $row->id) . '" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2" title="View / Download Receipt">
                                    <i class="ri-attachment-line me-1"></i> Receipt
                                </a>';
                    }
                    return '<span class="text-muted small">None</span>';
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button type="button" class="btn btn-sm btn-outline-primary me-1 edit-transaction-btn" data-id="' . $row->id . '" title="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>';
                    $viewBtn = '<button type="button" class="btn btn-sm btn-outline-secondary me-1 view-transaction-btn" data-id="' . $row->id . '" title="View Details">
                                    <i class="ri-eye-line"></i>
                                </button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger delete-transaction-btn" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                  </button>';
                    return '<div class="d-flex justify-content-center">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['transaction_date', 'transaction_type', 'category', 'title', 'payment_mode', 'in_amount', 'out_amount', 'receipt', 'action'])
                ->make(true);
        }

        $stats = PettyCash::getSummaryStats();
        $categories = PettyCash::CATEGORIES;
        $paymentModes = PettyCash::PAYMENT_MODES;
        $recentTransactions = PettyCash::with('creator:id,name')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->limit(6)->get();

        return view('backend.petty-cash.index', compact('stats', 'categories', 'paymentModes', 'recentTransactions'));
    }

    /**
     * Store a newly created petty cash transaction (Credit or Debit).
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'transaction_type' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['nullable', 'string', 'max:20'],
            'category' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:500'],
            'spent_by' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('petty-cash-receipts', 'public');
        }

        $transaction = PettyCash::create([
            'transaction_type' => $validated['transaction_type'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'transaction_time' => $validated['transaction_time'] ?? now()->format('H:i:s'),
            'category' => $validated['category'],
            'title' => $validated['title'],
            'spent_by' => $validated['spent_by'] ?? null,
            'payment_mode' => $validated['payment_mode'],
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'receipt_file' => $receiptPath,
            'created_by' => Auth::id(),
        ]);

        $updatedStats = PettyCash::getSummaryStats();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $transaction->transaction_type === 'credit'
                    ? 'Funds (₹' . number_format($transaction->amount, 2) . ') added successfully! Updated Balance: ₹' . number_format($updatedStats['balance'], 2)
                    : 'Expense of ₹' . number_format($transaction->amount, 2) . ' recorded successfully! Available Balance: ₹' . number_format($updatedStats['balance'], 2),
                'transaction' => $transaction,
                'stats' => $updatedStats,
            ]);
        }

        return redirect()->route('admin.petty-cash.index')->with('success', 'Transaction recorded successfully! Current Balance: ₹' . number_format($updatedStats['balance'], 2));
    }

    /**
     * Display the specified petty cash transaction details as JSON.
     */
    public function show($id): JsonResponse
    {
        $this->authorizeAdmin();

        $transaction = PettyCash::with('creator:id,name')->findOrFail($id);

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'transaction_type' => $transaction->transaction_type,
                'amount' => (float) $transaction->amount,
                'amount_formatted' => '₹' . number_format((float) $transaction->amount, 2),
                'transaction_date' => $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : null,
                'transaction_date_formatted' => $transaction->transaction_date ? $transaction->transaction_date->format('d M Y') : '-',
                'transaction_time' => $transaction->transaction_time ? Carbon::parse($transaction->transaction_time)->format('H:i') : null,
                'transaction_time_formatted' => $transaction->transaction_time ? Carbon::parse($transaction->transaction_time)->format('h:i A') : '-',
                'category' => $transaction->category,
                'title' => $transaction->title,
                'spent_by' => $transaction->spent_by,
                'payment_mode' => $transaction->payment_mode,
                'reference_no' => $transaction->reference_no,
                'notes' => $transaction->notes,
                'receipt_url' => $transaction->receipt_file ? route('admin.petty-cash.receipt', $transaction->id) : null,
                'receipt_filename' => $transaction->receipt_file ? basename($transaction->receipt_file) : null,
                'creator_name' => $transaction->creator?->name ?? 'Admin',
                'created_at_formatted' => $transaction->created_at ? $transaction->created_at->format('d M Y, h:i A') : '-',
            ],
        ]);
    }

    /**
     * Update an existing petty cash transaction.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $transaction = PettyCash::findOrFail($id);

        $validated = $request->validate([
            'transaction_type' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['nullable', 'string', 'max:20'],
            'category' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:500'],
            'spent_by' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($request->hasFile('receipt_file')) {
            if ($transaction->receipt_file && Storage::disk('public')->exists($transaction->receipt_file)) {
                Storage::disk('public')->delete($transaction->receipt_file);
            }
            $validated['receipt_file'] = $request->file('receipt_file')->store('petty-cash-receipts', 'public');
        }

        $transaction->update($validated);

        $updatedStats = PettyCash::getSummaryStats();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully! Updated Balance: ₹' . number_format($updatedStats['balance'], 2),
                'transaction' => $transaction,
                'stats' => $updatedStats,
            ]);
        }

        return redirect()->route('admin.petty-cash.index')->with('success', 'Transaction updated successfully! Updated Balance: ₹' . number_format($updatedStats['balance'], 2));
    }

    /**
     * Remove the specified petty cash transaction.
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin();

        $transaction = PettyCash::findOrFail($id);
        $transaction->delete();

        $updatedStats = PettyCash::getSummaryStats();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction removed successfully. Available Balance: ₹' . number_format($updatedStats['balance'], 2),
                'stats' => $updatedStats,
            ]);
        }

        return redirect()->route('admin.petty-cash.index')->with('success', 'Transaction removed successfully.');
    }

    /**
     * Return live summary metrics for dashboard or ledger AJAX updates.
     */
    public function summary(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json([
            'success' => true,
            'stats' => PettyCash::getSummaryStats(),
        ]);
    }

    /**
     * Download or view attached receipt file.
     */
    public function receipt($id)
    {
        $this->authorizeAdmin();

        $transaction = PettyCash::findOrFail($id);
        abort_unless($transaction->receipt_file && Storage::disk('public')->exists($transaction->receipt_file), 404, 'Receipt not found.');

        return Storage::disk('public')->response($transaction->receipt_file);
    }

    /**
     * Stream CSV export of petty cash ledger.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $query = PettyCash::query()->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('transaction_type') && in_array($request->transaction_type, ['credit', 'debit'], true)) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $records = $query->get();
        $filename = 'petty-cash-ledger-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Date',
                'Time',
                'Type',
                'Category',
                'Reason / Title',
                'Spent By / Paid To',
                'Payment Mode',
                'Reference / Bill No',
                'Cash In (+₹)',
                'Cash Out (-₹)',
                'Running Balance (₹)',
                'Notes',
            ]);

            $runningBalance = 0.0;
            foreach ($records as $item) {
                $amount = (float) $item->amount;
                $in = 0.0;
                $out = 0.0;

                if ($item->transaction_type === 'credit') {
                    $in = $amount;
                    $runningBalance += $amount;
                } else {
                    $out = $amount;
                    $runningBalance -= $amount;
                }

                fputcsv($handle, [
                    $item->id,
                    $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '',
                    $item->transaction_time ? Carbon::parse($item->transaction_time)->format('H:i') : '',
                    $item->transaction_type === 'credit' ? 'Cash In (Credit / Top-up)' : 'Cash Out (Debit / Expense)',
                    $item->category,
                    $item->title,
                    $item->spent_by,
                    $item->payment_mode,
                    $item->reference_no,
                    $in > 0 ? number_format($in, 2, '.', '') : '',
                    $out > 0 ? number_format($out, 2, '.', '') : '',
                    number_format($runningBalance, 2, '.', ''),
                    $item->notes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

