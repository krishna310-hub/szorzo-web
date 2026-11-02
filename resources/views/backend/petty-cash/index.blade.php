@extends('backend.layouts.master')
@section('title', 'Petition Amount (Petty Cash Ledger)')

@push('style')
<style>
    .petty-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    }
    .petty-hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .balance-card-highlight {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #fff;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .balance-card-highlight:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(5, 150, 105, 0.35);
    }
    .balance-card-highlight.low-balance {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.28);
    }
    .balance-card-highlight.negative-balance {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 8px 24px rgba(220, 38, 38, 0.28);
    }
    .stat-mini-card {
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 18px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-mini-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }
    .stat-mini-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .filter-pill-btn {
        border-radius: 20px;
        font-size: 12px;
        padding: 4px 12px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .filter-pill-btn:hover, .filter-pill-btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
    .badge-credit {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .badge-debit {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .table-ledger th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        background-color: #f8fafc;
        color: #475569;
    }

    /* Petty Cash Modals Stacking Fix */
    #addExpenseModal, #addFundsModal, #editTransactionModal, #viewTransactionModal {
        z-index: 1080 !important;
    }
    #addExpenseModal .modal-dialog, #addFundsModal .modal-dialog, #editTransactionModal .modal-dialog, #viewTransactionModal .modal-dialog {
        z-index: 1085 !important;
        position: relative !important;
    }
    #addExpenseModal .modal-content, #addFundsModal .modal-content, #editTransactionModal .modal-content, #viewTransactionModal .modal-content {
        background-color: #ffffff !important;
        position: relative !important;
        z-index: 1090 !important;
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4) !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb & Header -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 text-dark fw-bold">
                                <i class="ri-wallet-3-line text-primary me-2"></i>Petition Amount (Petty Cash Ledger)
                            </h4>
                            <p class="text-muted small mb-0 mt-1">
                                Track Cash In (Fund Top-up) and Cash Out (Office Expenses) with automatic balance reduction.
                            </p>
                        </div>
                        <div class="page-title-right d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="ri-shield-keyhole-line me-1"></i> Admin Only
                            </span>
                            <a href="{{ route('admin.petty-cash.export') }}" id="exportCsvBtn" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                                <i class="ri-file-download-line me-1"></i> Export CSV
                            </a>
                            <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                                <i class="ri-add-circle-line me-1"></i> + Add Funds (In)
                            </button>
                            <button type="button" class="btn btn-danger btn-sm rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="ri-subtract-line me-1"></i> - Record Expense (Out)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Highlighted KPI Stats Row -->
            <div class="row g-3 mb-4">
                <!-- Highlighted Available Balance Card -->
                <div class="col-12 col-xl-4">
                    <div id="balanceCardHighlight" class="balance-card-highlight h-100 d-flex flex-column justify-content-between {{ $stats['balance'] < 0 ? 'negative-balance' : ($stats['balance'] < 5000 ? 'low-balance' : '') }}">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase fw-semibold tracking-wide small" style="opacity: 0.9;">
                                    <i class="ri-shield-check-line me-1"></i> Total Available Balance
                                </span>
                                <span class="badge bg-white text-dark rounded-pill px-2 py-1 small fw-bold">Live</span>
                            </div>
                            <h2 class="display-6 fw-bold mb-1" id="statBalanceText">
                                ₹{{ number_format($stats['balance'], 2) }}
                            </h2>
                            <p class="mb-0 small" style="opacity: 0.85;">
                                Fund remaining after all office debits.
                            </p>
                        </div>
                        <div class="d-flex gap-2 pt-3 border-top border-white-50 mt-3">
                            <button type="button" class="btn btn-light btn-sm fw-bold text-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                                <i class="ri-add-line"></i> Top-up Fund
                            </button>
                            <button type="button" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="ri-shopping-cart-line"></i> Record Expense
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mini Cards Row -->
                <div class="col-12 col-xl-8">
                    <div class="row g-3 h-100">
                        <div class="col-sm-6 col-md-3">
                            <div class="stat-mini-card h-100 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase">Total Funds In</div>
                                        <h4 class="fw-bold text-success mt-1 mb-0" id="statTotalInText">
                                            +₹{{ number_format($stats['total_in'], 2) }}
                                        </h4>
                                    </div>
                                    <div class="stat-mini-icon bg-success-subtle text-success">
                                        <i class="ri-arrow-down-circle-line"></i>
                                    </div>
                                </div>
                                <div class="text-muted small">All top-ups & credits</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="stat-mini-card h-100 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase">Total Spent Out</div>
                                        <h4 class="fw-bold text-danger mt-1 mb-0" id="statTotalOutText">
                                            -₹{{ number_format($stats['total_out'], 2) }}
                                        </h4>
                                    </div>
                                    <div class="stat-mini-icon bg-danger-subtle text-danger">
                                        <i class="ri-arrow-up-circle-line"></i>
                                    </div>
                                </div>
                                <div class="text-muted small">All expenses deducted</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="stat-mini-card h-100 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase">This Month</div>
                                        <h4 class="fw-bold text-dark mt-1 mb-0" id="statThisMonthText">
                                            ₹{{ number_format($stats['this_month_out'], 2) }}
                                        </h4>
                                    </div>
                                    <div class="stat-mini-icon bg-primary-subtle text-primary">
                                        <i class="ri-calendar-event-line"></i>
                                    </div>
                                </div>
                                <div class="text-muted small">{{ now()->format('F Y') }} expenses</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="stat-mini-card h-100 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase">Today's Expense</div>
                                        <h4 class="fw-bold text-dark mt-1 mb-0" id="statTodayText">
                                            ₹{{ number_format($stats['today_out'], 2) }}
                                        </h4>
                                    </div>
                                    <div class="stat-mini-icon bg-warning-subtle text-warning">
                                        <i class="ri-time-line"></i>
                                    </div>
                                </div>
                                <div class="text-muted small">{{ now()->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Panel -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="ri-filter-3-line text-primary"></i> Filter & Search Ledger
                    </h5>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="small text-muted me-1">Quick Date:</span>
                        <button type="button" class="filter-pill-btn active" data-range="all">All Time</button>
                        <button type="button" class="filter-pill-btn" data-range="today">Today</button>
                        <button type="button" class="filter-pill-btn" data-range="week">This Week</button>
                        <button type="button" class="filter-pill-btn" data-range="month">This Month</button>
                        <button type="button" class="filter-pill-btn" data-range="year">This Year</button>
                    </div>
                </div>
                <div class="card-body bg-light-subtle p-3 p-lg-4">
                    <form id="pettyFilterForm" class="row g-3 align-items-end">
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small fw-semibold text-muted">From Date</label>
                            <input type="date" id="filterFromDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small fw-semibold text-muted">To Date</label>
                            <input type="date" id="filterToDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small fw-semibold text-muted">Transaction Type</label>
                            <select id="filterType" class="form-select form-select-sm">
                                <option value="">All (In & Out)</option>
                                <option value="credit">Cash In (Credit / Funds)</option>
                                <option value="debit">Cash Out (Debit / Expenses)</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-3">
                            <label class="form-label small fw-semibold text-muted">Category</label>
                            <select id="filterCategory" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catKey }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 col-lg-2">
                            <label class="form-label small fw-semibold text-muted">Search Keyword</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="ri-search-line"></i></span>
                                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Reason, Person, Ref...">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-1 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                                <i class="ri-search-line"></i>
                            </button>
                            <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ledger Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="ri-file-list-3-line text-success"></i> Transaction Ledger
                    </h5>
                    <div class="text-muted small">
                        Showing all recorded cash inflows & office expense outflows
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="pettyCashTable" class="table table-hover table-bordered align-middle mb-0 w-100 table-ledger">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">S.No</th>
                                    <th style="width: 120px;">Date & Time</th>
                                    <th style="width: 140px;">Type</th>
                                    <th>Reason / Description</th>
                                    <th>Category</th>
                                    <th>Spent By / Paid To</th>
                                    <th>Payment Mode</th>
                                    <th class="text-end" style="width: 120px;">Cash In (+₹)</th>
                                    <th class="text-end" style="width: 120px;">Cash Out (-₹)</th>
                                    <th style="width: 90px;" class="text-center">Receipt</th>
                                    <th style="width: 110px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Add Expense Modal (Cash Out) -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
            <form id="addExpenseForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="transaction_type" value="debit">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="addExpenseModalLabel">
                        <i class="ri-subtract-line me-1"></i> Record Office Expense (Cash Out)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger-subtle border border-danger-subtle text-danger small mb-3">
                        <i class="ri-information-line me-1"></i>
                        This will automatically deduct the amount from your available balance.
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Expense Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg fw-bold text-danger" placeholder="e.g. 1500.00" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Expense Reason / Description <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Office stationery / Team refreshments" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach ($categories as $catKey => $catLabel)
                                    @if ($catKey !== 'Funds Allocation / Top-up')
                                        <option value="{{ $catKey }}">{{ $catKey }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                @foreach ($paymentModes as $mode)
                                    <option value="{{ $mode }}">{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" name="transaction_time" class="form-control" value="{{ now()->format('H:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Spent By / Paid To</label>
                            <input type="text" name="spent_by" class="form-control" placeholder="e.g. Office Boy, Bakery, Vendor">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bill / Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="e.g. Bill #1234 or UPI Ref">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload Bill / Receipt (Optional)</label>
                            <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                            <div class="form-text small">Accepted: JPG, PNG, PDF up to 10MB</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Additional Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Any specific remarks or approvals..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-semibold px-4">
                        <i class="ri-check-line me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Add Funds Modal (Cash In / Top-up) -->
<div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
            <form id="addFundsForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="transaction_type" value="credit">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="addFundsModalLabel">
                        <i class="ri-add-circle-line me-1"></i> Add Funds (Petition Amount Top-up)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success-subtle border border-success-subtle text-success small mb-3">
                        <i class="ri-information-line me-1"></i>
                        This adds funds to the balance (e.g. ₹2,00,000 top-up or monthly office allocation).
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Fund Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg fw-bold text-success" placeholder="e.g. 200000.00" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Top-up Description / Reason <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Monthly Petty Cash Fund / Bank Cash Withdrawal" value="Office Petty Cash Allocation" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Funds Allocation / Top-up" selected>Funds Allocation / Top-up</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                @foreach ($paymentModes as $mode)
                                    <option value="{{ $mode }}" @selected($mode === 'Bank Transfer / NEFT' || $mode === 'Cash')>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" name="transaction_time" class="form-control" value="{{ now()->format('H:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Source / Deposited By</label>
                            <input type="text" name="spent_by" class="form-control" placeholder="e.g. Admin / Director / Bank">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cheque / UTR / Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="e.g. UTR123456 or Cheque #987">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload Voucher / Slip (Optional)</label>
                            <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                            <div class="form-text small">Accepted: JPG, PNG, PDF up to 10MB</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">
                        <i class="ri-check-line me-1"></i> Add Funds
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
            <form id="editTransactionForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editTransactionId" name="id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editTransactionModalLabel">
                        <i class="ri-edit-line me-1"></i> Edit Transaction
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transaction Type <span class="text-danger">*</span></label>
                            <select id="editTransactionType" name="transaction_type" class="form-select" required>
                                <option value="credit">Cash In (Credit / Fund)</option>
                                <option value="debit">Cash Out (Debit / Expense)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" min="0.01" id="editAmount" name="amount" class="form-control fw-bold" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason / Description <span class="text-danger">*</span></label>
                            <input type="text" id="editTitle" name="title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="editCategory" name="category" class="form-select" required>
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catKey }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select id="editPaymentMode" name="payment_mode" class="form-select" required>
                                @foreach ($paymentModes as $mode)
                                    <option value="{{ $mode }}">{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" id="editDate" name="transaction_date" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" id="editTime" name="transaction_time" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Spent By / Paid To</label>
                            <input type="text" id="editSpentBy" name="spent_by" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference No</label>
                            <input type="text" id="editReferenceNo" name="reference_no" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Replace Receipt / Bill (Optional)</label>
                            <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                            <div id="currentReceiptNote" class="form-text small text-info mt-1"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea id="editNotes" name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="ri-check-line me-1"></i> Update Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. View Details Modal -->
<div class="modal fade" id="viewTransactionModal" tabindex="-1" aria-labelledby="viewTransactionModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="viewTransactionModalLabel">
                    <i class="ri-file-text-line me-1"></i> Transaction Voucher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewTransactionBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
function ensurePettyModalsInBody() {
    ['addExpenseModal', 'addFundsModal', 'editTransactionModal', 'viewTransactionModal'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
    });
}
ensurePettyModalsInBody();

$(function () {
    ensurePettyModalsInBody();

    $('#addExpenseModal, #addFundsModal, #editTransactionModal, #viewTransactionModal').on('show.bs.modal', function () {
        ensurePettyModalsInBody();
    });

    $('#addExpenseModal, #addFundsModal, #editTransactionModal, #viewTransactionModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
    });

    // Configure CSRF for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const updateExportUrl = function() {
        const params = new URLSearchParams();
        if ($('#filterFromDate').val()) params.append('from_date', $('#filterFromDate').val());
        if ($('#filterToDate').val()) params.append('to_date', $('#filterToDate').val());
        if ($('#filterType').val()) params.append('transaction_type', $('#filterType').val());
        if ($('#filterCategory').val()) params.append('category', $('#filterCategory').val());
        $('#exportCsvBtn').attr('href', '{{ route('admin.petty-cash.export') }}?' + params.toString());
    };

    // Initialize DataTable
    const table = $('#pettyCashTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 25,
        ajax: {
            url: '{{ route('admin.petty-cash.index') }}',
            data: function (d) {
                d.from_date = $('#filterFromDate').val();
                d.to_date = $('#filterToDate').val();
                d.transaction_type = $('#filterType').val();
                d.category = $('#filterCategory').val();
                d.search_keyword = $('#filterSearch').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center' },
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'transaction_type', name: 'transaction_type', className: 'text-center' },
            { data: 'title', name: 'title' },
            { data: 'category', name: 'category' },
            { data: 'spent_by', name: 'spent_by' },
            { data: 'payment_mode', name: 'payment_mode', className: 'text-center' },
            { data: 'in_amount', name: 'in_amount', className: 'text-end fw-semibold' },
            { data: 'out_amount', name: 'out_amount', className: 'text-end fw-semibold' },
            { data: 'receipt', name: 'receipt', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center' }
        ],
        language: {
            emptyTable: 'No transactions found. Record an expense or add funds to get started.'
        }
    });

    // Update Live KPI metrics
    function refreshMetrics() {
        $.getJSON('{{ route('admin.petty-cash.summary') }}', function (res) {
            if (res && res.success && res.stats) {
                const s = res.stats;
                const formattedBalance = '₹' + Number(s.balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                $('#statBalanceText').text(formattedBalance);
                $('#statTotalInText').text('+₹' + Number(s.total_in).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#statTotalOutText').text('-₹' + Number(s.total_out).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#statThisMonthText').text('₹' + Number(s.this_month_out).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#statTodayText').text('₹' + Number(s.today_out).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                const $card = $('#balanceCardHighlight');
                $card.removeClass('negative-balance low-balance');
                if (s.balance < 0) {
                    $card.addClass('negative-balance');
                } else if (s.balance < 5000) {
                    $card.addClass('low-balance');
                }
            }
        });
    }

    // Filter Form Submit
    $('#pettyFilterForm').on('submit', function (e) {
        e.preventDefault();
        updateExportUrl();
        table.ajax.reload();
    });

    // Reset Filter Button
    $('#resetFilterBtn').on('click', function () {
        $('#pettyFilterForm')[0].reset();
        $('.filter-pill-btn').removeClass('active');
        $('[data-range="all"]').addClass('active');
        updateExportUrl();
        table.ajax.reload();
    });

    // Quick Date Pills
    $('.filter-pill-btn').on('click', function () {
        $('.filter-pill-btn').removeClass('active');
        $(this).addClass('active');
        const range = $(this).data('range');
        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (range === 'all') {
            $('#filterFromDate').val('');
            $('#filterToDate').val('');
        } else if (range === 'today') {
            const todayStr = formatDate(today);
            $('#filterFromDate').val(todayStr);
            $('#filterToDate').val(todayStr);
        } else if (range === 'week') {
            const first = today.getDate() - today.getDay();
            const firstDay = new Date(today.setDate(first));
            const lastDay = new Date(today.setDate(first + 6));
            $('#filterFromDate').val(formatDate(firstDay));
            $('#filterToDate').val(formatDate(lastDay));
        } else if (range === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            $('#filterFromDate').val(formatDate(firstDay));
            $('#filterToDate').val(formatDate(lastDay));
        } else if (range === 'year') {
            const firstDay = new Date(today.getFullYear(), 0, 1);
            const lastDay = new Date(today.getFullYear(), 11, 31);
            $('#filterFromDate').val(formatDate(firstDay));
            $('#filterToDate').val(formatDate(lastDay));
        }

        updateExportUrl();
        table.ajax.reload();
    });

    // Handle Add Expense Form Submit
    $('#addExpenseForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Saving...');

        $.ajax({
            url: '{{ route('admin.petty-cash.store') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#addExpenseModal').modal('hide');
                $('#addExpenseForm')[0].reset();
                toastr.success(res.message || 'Expense recorded successfully!');
                table.ajax.reload(null, false);
                refreshMetrics();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to record expense. Please check your inputs.';
                toastr.error(msg);
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Save Expense');
            }
        });
    });

    // Handle Add Funds Form Submit
    $('#addFundsForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Adding...');

        $.ajax({
            url: '{{ route('admin.petty-cash.store') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#addFundsModal').modal('hide');
                $('#addFundsForm')[0].reset();
                toastr.success(res.message || 'Funds added successfully!');
                table.ajax.reload(null, false);
                refreshMetrics();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to add funds. Please check your inputs.';
                toastr.error(msg);
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Add Funds');
            }
        });
    });

    // Open View Details Modal
    $(document).on('click', '.view-transaction-btn', function () {
        const id = $(this).data('id');
        $('#viewTransactionModal').modal('show');
        $('#viewTransactionBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

        $.getJSON('{{ url('admin/petty-cash') }}/' + id, function (res) {
            if (res.success && res.transaction) {
                const t = res.transaction;
                const isCredit = t.transaction_type === 'credit';
                const badgeHtml = isCredit
                    ? '<span class="badge bg-success-subtle text-success border border-success px-3 py-1 fs-6"><i class="ri-arrow-down-circle-line me-1"></i> Cash In (Fund Top-up)</span>'
                    : '<span class="badge bg-danger-subtle text-danger border border-danger px-3 py-1 fs-6"><i class="ri-arrow-up-circle-line me-1"></i> Cash Out (Expense)</span>';

                let html = `
                    <div class="text-center mb-3">
                        ${badgeHtml}
                        <h2 class="fw-bold mt-2 ${isCredit ? 'text-success' : 'text-danger'}">${isCredit ? '+' : '-'}${t.amount_formatted}</h2>
                        <div class="text-muted small">${t.transaction_date_formatted} ${t.transaction_time_formatted !== '-' ? 'at ' + t.transaction_time_formatted : ''}</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th class="bg-light w-35">Reason / Description</th>
                                    <td><strong class="text-dark">${t.title}</strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Category</th>
                                    <td><span class="badge bg-light text-dark border">${t.category}</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">${isCredit ? 'Deposited / Sourced By' : 'Spent By / Paid To'}</th>
                                    <td>${t.spent_by || '-'}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Payment Mode</th>
                                    <td>${t.payment_mode}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Reference / Bill No</th>
                                    <td>${t.reference_no || '-'}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Recorded By</th>
                                    <td>${t.creator_name} (${t.created_at_formatted})</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Notes</th>
                                    <td>${t.notes || '<span class="text-muted">None</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Receipt / Bill</th>
                                    <td>
                                        ${t.receipt_url
                                            ? `<a href="${t.receipt_url}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3"><i class="ri-file-download-line me-1"></i> Download / View Receipt (${t.receipt_filename})</a>`
                                            : '<span class="text-muted">No attachment</span>'}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                $('#viewTransactionBody').html(html);
            }
        }).fail(function () {
            $('#viewTransactionBody').html('<div class="alert alert-danger">Failed to load transaction details.</div>');
        });
    });

    // Open Edit Modal
    $(document).on('click', '.edit-transaction-btn', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('admin/petty-cash') }}/' + id, function (res) {
            if (res.success && res.transaction) {
                const t = res.transaction;
                $('#editTransactionId').val(t.id);
                $('#editTransactionType').val(t.transaction_type);
                $('#editAmount').val(t.amount);
                $('#editTitle').val(t.title);
                $('#editCategory').val(t.category);
                $('#editPaymentMode').val(t.payment_mode);
                $('#editDate').val(t.transaction_date);
                $('#editTime').val(t.transaction_time);
                $('#editSpentBy').val(t.spent_by || '');
                $('#editReferenceNo').val(t.reference_no || '');
                $('#editNotes').val(t.notes || '');

                if (t.receipt_url) {
                    $('#currentReceiptNote').html('Current receipt: <a href="' + t.receipt_url + '" target="_blank">' + t.receipt_filename + '</a>. Uploading a new file will replace it.');
                } else {
                    $('#currentReceiptNote').text('');
                }

                $('#editTransactionModal').modal('show');
            }
        });
    });

    // Submit Edit Form
    $('#editTransactionForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#editTransactionId').val();
        const formData = new FormData(this);
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Updating...');

        $.ajax({
            url: '{{ url('admin/petty-cash') }}/' + id,
            type: 'POST', // with _method=PUT in formData
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#editTransactionModal').modal('hide');
                toastr.success(res.message || 'Transaction updated successfully!');
                table.ajax.reload(null, false);
                refreshMetrics();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to update transaction.';
                toastr.error(msg);
            },
            complete: function () {
                $submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Update Transaction');
            }
        });
    });

    // Delete Transaction
    $(document).on('click', '.delete-transaction-btn', function () {
        const id = $(this).data('id');
        const title = $(this).data('title');

        if (!confirm('Are you sure you want to delete this transaction: "' + title + '"? This will re-adjust your available balance.')) {
            return;
        }

        $.ajax({
            url: '{{ url('admin/petty-cash') }}/' + id,
            type: 'DELETE',
            success: function (res) {
                toastr.success(res.message || 'Transaction deleted successfully.');
                table.ajax.reload(null, false);
                refreshMetrics();
            },
            error: function () {
                toastr.error('Failed to delete transaction.');
            }
        });
    });
});
</script>
@endsection

