<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\ContractExpiryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckContractExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiring contracts 3 days before expiry, notify administrators, and prune notifications for relieved/inactive employees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $threeDaysAhead = Carbon::today()->addDays(3);

        $this->info("Checking contract expiry between {$today->toDateString()} and {$threeDaysAhead->toDateString()}...");

        // 1. Prune notifications for employees who are relieved and/or inactive
        $this->pruneStaleNotifications($today);

        // 2. Identify administrators/managers to notify
        $adminUsers = User::query()
            ->where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('access_level', ['super-admin', 'admin', 'delivery-lead', 'recruiter-dl']);
            })
            ->get();

        if ($adminUsers->isEmpty()) {
            $adminUsers = User::query()->where('is_active', true)->where('id', 1)->get();
        }

        if ($adminUsers->isEmpty()) {
            $this->warn('No active administrators found to notify.');
            return self::SUCCESS;
        }

        $notifiedCount = 0;

        // 3. Process contract employees
        // Requirement: Exclude employees after relieving & inactive; notify 3 days before contract_to_date
        $contractEmployees = Employee::query()
            ->where('status', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('relieving_date')->orWhereDate('relieving_date', '>', $today->toDateString());
            })
            ->whereNotNull('contract_to_date')
            ->whereDate('contract_to_date', '>=', $today->toDateString())
            ->whereDate('contract_to_date', '<=', $threeDaysAhead->toDateString())
            ->get();

        foreach ($contractEmployees as $employee) {
            $contractTo = Carbon::parse($employee->contract_to_date);
            $daysLeft = (int) $today->diffInDays($contractTo, false);
            $status = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'danger' : 'warning');

            $dayText = $daysLeft === 0 ? 'today' : "in {$daysLeft} day(s)";
            $title = "Contract Expiry (3 Days Alert): {$employee->employee_name}";
            $message = "Contract for {$employee->employee_name} ({$employee->employee_no}) expires {$dayText} on {$contractTo->format('d-m-Y')}.";
            $actionUrl = route('admin.employees.edit', $employee->id);

            foreach ($adminUsers as $user) {
                $alreadyNotifiedToday = $user->notifications()
                    ->where('type', ContractExpiryNotification::class)
                    ->whereDate('created_at', $today->toDateString())
                    ->where('data->contractable_type', 'employee')
                    ->where('data->contractable_id', $employee->id)
                    ->exists();

                if (! $alreadyNotifiedToday) {
                    $user->notify(new ContractExpiryNotification(
                        $title,
                        $message,
                        $actionUrl,
                        $status,
                        'employee',
                        $employee->id,
                        $contractTo->toDateString(),
                        $daysLeft
                    ));
                    $notifiedCount++;
                }
            }
        }

        // 4. Process contract candidates
        $contractCandidates = Candidate::query()
            ->where('status', true)
            ->whereNotNull('contract_to_date')
            ->whereDate('contract_to_date', '>=', $today->toDateString())
            ->whereDate('contract_to_date', '<=', $threeDaysAhead->toDateString())
            ->get();

        foreach ($contractCandidates as $candidate) {
            // Check if matching employee is relieved/inactive
            $isMatchingEmployeeInactive = Employee::where(function ($m) use ($candidate) {
                if ($candidate->email) {
                    $m->whereRaw('LOWER(official_mail) = ?', [mb_strtolower($candidate->email)])
                      ->orWhereRaw('LOWER(personal_mail) = ?', [mb_strtolower($candidate->email)]);
                }
                if ($candidate->mobile_no) {
                    $m->orWhere('mobile_number', $candidate->mobile_no)
                      ->orWhere('alternate_mobile_number', $candidate->mobile_no);
                }
            })->where(function ($inact) use ($today) {
                $inact->where('status', false)
                      ->orWhere(fn ($r) => $r->whereNotNull('relieving_date')->whereDate('relieving_date', '<=', $today->toDateString()));
            })->exists();

            if ($isMatchingEmployeeInactive) {
                continue;
            }

            $contractTo = Carbon::parse($candidate->contract_to_date);
            $daysLeft = (int) $today->diffInDays($contractTo, false);
            $status = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'danger' : 'warning');

            $dayText = $daysLeft === 0 ? 'today' : "in {$daysLeft} day(s)";
            $title = "Contract Expiry (3 Days Alert): {$candidate->candidate_name}";
            $message = "Contract for candidate {$candidate->candidate_name} expires {$dayText} on {$contractTo->format('d-m-Y')}.";
            $actionUrl = route('admin.candidates.edit', $candidate->id);

            foreach ($adminUsers as $user) {
                $alreadyNotifiedToday = $user->notifications()
                    ->where('type', ContractExpiryNotification::class)
                    ->whereDate('created_at', $today->toDateString())
                    ->where('data->contractable_type', 'candidate')
                    ->where('data->contractable_id', $candidate->id)
                    ->exists();

                if (! $alreadyNotifiedToday) {
                    $user->notify(new ContractExpiryNotification(
                        $title,
                        $message,
                        $actionUrl,
                        $status,
                        'candidate',
                        $candidate->id,
                        $contractTo->toDateString(),
                        $daysLeft
                    ));
                    $notifiedCount++;
                }
            }
        }

        $this->info("Completed. Generated {$notifiedCount} notification(s).");
        return self::SUCCESS;
    }

    /**
     * Prune notifications for relieved or inactive contract employees.
     */
    protected function pruneStaleNotifications(Carbon $today): void
    {
        try {
            DB::table('notifications')
                ->where('type', ContractExpiryNotification::class)
                ->where('data->contractable_type', 'employee')
                ->get()
                ->each(function ($notif) use ($today) {
                    $data = json_decode((string) $notif->data, true);
                    $empId = $data['contractable_id'] ?? null;
                    if ($empId) {
                        $emp = Employee::withTrashed()->find($empId);
                        if (! $emp || ! $emp->status || ($emp->relieving_date && Carbon::parse($emp->relieving_date)->lte($today)) || $emp->trashed()) {
                            DB::table('notifications')->where('id', $notif->id)->delete();
                        }
                    }
                });
        } catch (\Throwable $e) {
            // Ignore DB errors during offline setup
        }
    }
}
