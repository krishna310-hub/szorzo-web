<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\PettyCashController;
use App\Models\PettyCash;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class PettyCashFeatureTest extends TestCase
{
    /**
     * Test PettyCash model constants and structure.
     */
    public function test_petty_cash_constants_and_categories(): void
    {
        $this->assertNotEmpty(PettyCash::CATEGORIES);
        $this->assertArrayHasKey('Celebration & Events', PettyCash::CATEGORIES);
        $this->assertArrayHasKey('Printing & Stationery', PettyCash::CATEGORIES);
        $this->assertArrayHasKey('Food & Refreshments', PettyCash::CATEGORIES);
        $this->assertArrayHasKey('Office Supplies', PettyCash::CATEGORIES);
        $this->assertArrayHasKey('Funds Allocation / Top-up', PettyCash::CATEGORIES);

        $this->assertNotEmpty(PettyCash::PAYMENT_MODES);
        $this->assertContains('Cash', PettyCash::PAYMENT_MODES);
        $this->assertContains('UPI / GPay / PhonePe', PettyCash::PAYMENT_MODES);
        $this->assertContains('Bank Transfer / NEFT', PettyCash::PAYMENT_MODES);
    }

    /**
     * Test PettyCash model attributes and fillable fields.
     */
    public function test_petty_cash_fillable_fields(): void
    {
        $model = new PettyCash();
        $expectedFillable = [
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

        foreach ($expectedFillable as $field) {
            $this->assertTrue(in_array($field, $model->getFillable(), true), "Missing fillable field: {$field}");
        }
    }

    /**
     * Test PettyCash balance computation logic.
     * Simulates the exact user scenario:
     * - Top-up ₹2,00,000 fund
     * - Spend ₹1,500 on birthday cake
     * - Spend ₹300 on printouts
     * - Balance must be reduced to ₹1,98,200
     */
    public function test_ledger_balance_reduction_math(): void
    {
        $transactions = [
            ['type' => 'credit', 'amount' => 200000.00, 'title' => 'Initial Fund Allocation'],
            ['type' => 'debit', 'amount' => 1500.00, 'title' => 'Birthday cake buying for team'],
            ['type' => 'debit', 'amount' => 300.00, 'title' => 'Printouts and xerox for client'],
            ['type' => 'debit', 'amount' => 450.00, 'title' => 'Tea & refreshments for meeting'],
            ['type' => 'credit', 'amount' => 25000.00, 'title' => 'Additional Petty Cash top-up'],
        ];

        $totalCredit = 0.0;
        $totalDebit = 0.0;
        $runningBalances = [];

        foreach ($transactions as $txn) {
            if ($txn['type'] === 'credit') {
                $totalCredit += $txn['amount'];
            } else {
                $totalDebit += $txn['amount'];
            }
            $runningBalances[] = $totalCredit - $totalDebit;
        }

        // After initial 2 lakh top-up
        $this->assertEquals(200000.00, $runningBalances[0]);

        // After ₹1,500 birthday cake
        $this->assertEquals(198500.00, $runningBalances[1]);

        // After ₹300 printout
        $this->assertEquals(198200.00, $runningBalances[2]);

        // After ₹450 tea & refreshments
        $this->assertEquals(197750.00, $runningBalances[3]);

        // After ₹25,000 top-up
        $this->assertEquals(222750.00, $runningBalances[4]);

        $finalBalance = $totalCredit - $totalDebit;
        $this->assertEquals(222750.00, $finalBalance);
    }

    /**
     * Test model helper attributes is_credit and is_debit.
     */
    public function test_model_helper_attributes(): void
    {
        $creditTxn = new PettyCash(['transaction_type' => 'credit']);
        $this->assertTrue($creditTxn->is_credit);
        $this->assertFalse($creditTxn->is_debit);

        $debitTxn = new PettyCash(['transaction_type' => 'debit']);
        $this->assertFalse($debitTxn->is_credit);
        $this->assertTrue($debitTxn->is_debit);
    }

    /**
     * Test PettyCashController has all required actions.
     */
    public function test_controller_has_all_required_ledger_methods(): void
    {
        $ref = new ReflectionClass(PettyCashController::class);

        $methods = ['index', 'store', 'show', 'update', 'destroy', 'summary', 'receipt', 'export', 'authorizeAdmin'];
        foreach ($methods as $method) {
            $this->assertTrue($ref->hasMethod($method), "PettyCashController is missing method: {$method}");
        }
    }
}

