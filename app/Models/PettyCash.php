<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCash extends Model
{
    use SoftDeletes;

    protected $table = 'petty_cashes';

    protected $fillable = [
        'transaction_type',
        'amount',
        'transaction_date',
        'transaction_time',
        'category',
        'title',
        'spent_by',
        'payment_mode',
        'reference_no',
        'receipt_file',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const CATEGORIES = [
        'Celebration & Events' => 'Celebration & Events (Birthday cake, parties, team celebrations)',
        'Food & Refreshments' => 'Food & Refreshments (Tea, coffee, snacks, pantry items)',
        'Printing & Stationery' => 'Printing & Stationery (Printouts, xerox, paper, stationery)',
        'Office Supplies' => 'Office Supplies (Cleaning, sanitation, pantry supplies, hardware)',
        'Travel & Conveyance' => 'Travel & Conveyance (Cab, auto, petrol, local travel)',
        'Repairs & Maintenance' => 'Repairs & Maintenance (AC, electrical, plumbing, furniture)',
        'Courier & Postage' => 'Courier & Postage (Courier charges, speed post)',
        'Funds Allocation / Top-up' => 'Funds Allocation / Top-up (Initial fund, bank withdrawal)',
        'Utility & Bills' => 'Utility & Bills (Internet, electricity, drinking water)',
        'Miscellaneous' => 'Miscellaneous (Other office expenses)',
    ];

    public const PAYMENT_MODES = [
        'Cash',
        'UPI / GPay / PhonePe',
        'Bank Transfer / NEFT',
        'Company Card',
        'Cheque',
        'Other',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('transaction_type', 'credit');
    }

    public function scopeDebit(Builder $query): Builder
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('transaction_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('transaction_date', '<=', $to);
        }

        return $query;
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->transaction_type === 'credit';
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->transaction_type === 'debit';
    }

    public static function getTotalCredit(): float
    {
        return (float) static::where('transaction_type', 'credit')->sum('amount');
    }

    public static function getTotalDebit(): float
    {
        return (float) static::where('transaction_type', 'debit')->sum('amount');
    }

    public static function getBalance(): float
    {
        return static::getTotalCredit() - static::getTotalDebit();
    }

    public static function getSummaryStats(): array
    {
        $totalIn = static::getTotalCredit();
        $totalOut = static::getTotalDebit();
        $balance = $totalIn - $totalOut;

        $thisMonthOut = (float) static::where('transaction_type', 'debit')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $todayOut = (float) static::where('transaction_type', 'debit')
            ->whereDate('transaction_date', now()->toDateString())
            ->sum('amount');

        $totalTransactions = static::count();

        return [
            'balance' => $balance,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'this_month_out' => $thisMonthOut,
            'today_out' => $todayOut,
            'total_transactions' => $totalTransactions,
        ];
    }
}

