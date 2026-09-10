<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function __construct(
        protected PayslipService $payslipService
    ) {}

    /**
     * Display the payslip view & download page.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $canManageAll = $currentUser->isSuperAdmin()
            || $currentUser->can('read', Attendance::class)
            || $currentUser->can('read', Employee::class);

        // Resolve target user
        $targetUser = $currentUser;
        if ($canManageAll && $request->filled('user_id')) {
            $foundUser = User::find($request->input('user_id'));
            if ($foundUser) {
                $targetUser = $foundUser;
            }
        }

        $defaultMonth = now()->day < 5 ? now()->subMonth()->month : now()->month;
        $defaultYear = now()->day < 5 ? now()->subMonth()->year : now()->year;

        $month = (int) $request->input('month', $defaultMonth);
        $year = (int) $request->input('year', $defaultYear);

        $payslipData = $this->payslipService->getPayslipData($targetUser, $month, $year, $request->all());

        $allUsers = $canManageAll
            ? User::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'email'])
            : collect([$currentUser]);

        return view('backend.payslip.index', array_merge($payslipData, [
            'can_manage_all' => $canManageAll,
            'all_users' => $allUsers,
            'target_user' => $targetUser,
        ]));
    }

    /**
     * Generate and download the payslip PDF.
     */
    public function download(Request $request)
    {
        $currentUser = Auth::user();
        $canManageAll = $currentUser->isSuperAdmin()
            || $currentUser->can('read', Attendance::class)
            || $currentUser->can('read', Employee::class);

        $targetUser = $currentUser;
        if ($canManageAll && $request->filled('user_id')) {
            $foundUser = User::find($request->input('user_id'));
            if ($foundUser) {
                $targetUser = $foundUser;
            }
        }

        $defaultMonth = now()->day < 5 ? now()->subMonth()->month : now()->month;
        $defaultYear = now()->day < 5 ? now()->subMonth()->year : now()->year;

        $month = (int) $request->input('month', $defaultMonth);
        $year = (int) $request->input('year', $defaultYear);

        $data = $this->payslipService->getPayslipData($targetUser, $month, $year, $request->all());

        $pdf = Pdf::loadView('backend.payslip.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Payslip_' . ($data['employee_no'] ?: 'SZ') . '_' . $data['month_name'] . '_' . $data['year'] . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview the payslip PDF directly in browser.
     */
    public function preview(Request $request)
    {
        $currentUser = Auth::user();
        $canManageAll = $currentUser->isSuperAdmin()
            || $currentUser->can('read', Attendance::class)
            || $currentUser->can('read', Employee::class);

        $targetUser = $currentUser;
        if ($canManageAll && $request->filled('user_id')) {
            $foundUser = User::find($request->input('user_id'));
            if ($foundUser) {
                $targetUser = $foundUser;
            }
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $data = $this->payslipService->getPayslipData($targetUser, $month, $year, $request->all());

        $pdf = Pdf::loadView('backend.payslip.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Payslip_' . ($data['employee_no'] ?: 'SZ') . '_' . $data['month_name'] . '_' . $data['year'] . '.pdf';

        return $pdf->stream($filename);
    }
}

