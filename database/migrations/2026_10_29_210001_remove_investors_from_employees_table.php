<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        // 1. Delete employee records where designation indicates an investor role
        DB::table('employees')
            ->where(function ($q) {
                $q->whereRaw('LOWER(designation) LIKE ?', ['%invest%']);
            })
            ->delete();

        // 2. Delete employee records whose email matches an investor
        if (Schema::hasTable('investers')) {
            $investorEmails = DB::table('investers')
                ->pluck('primary_email')
                ->merge(DB::table('investers')->pluck('alternate_email'))
                ->filter()
                ->map(fn ($e) => strtolower(trim((string) $e)))
                ->unique()
                ->values()
                ->all();

            $investorIds = DB::table('investers')
                ->pluck('invester_id')
                ->filter()
                ->values()
                ->all();

            if (!empty($investorEmails)) {
                DB::table('employees')
                    ->where(function ($q) use ($investorEmails) {
                        $q->whereIn(DB::raw('LOWER(official_mail)'), $investorEmails)
                          ->orWhereIn(DB::raw('LOWER(personal_mail)'), $investorEmails);
                    })
                    ->delete();
            }

            if (!empty($investorIds)) {
                DB::table('employees')
                    ->whereIn('employee_no', $investorIds)
                    ->delete();
            }
        }

        // 3. Delete employee records whose email belongs to a user with role_id = 6 or investor role
        if (Schema::hasTable('users')) {
            $investorUserEmails = DB::table('users')
                ->where(function ($q) {
                    $q->where('role_id', 6);
                    if (Schema::hasTable('roles')) {
                        $q->orWhereExists(function ($rq) {
                            $rq->selectRaw('1')
                                ->from('roles')
                                ->whereColumn('roles.id', 'users.role_id')
                                ->where(function ($r) {
                                    $r->whereRaw('LOWER(roles.access_level) LIKE ?', ['%invest%'])
                                      ->orWhereRaw('LOWER(roles.name) LIKE ?', ['%invest%']);
                                });
                        });
                    }
                })
                ->pluck('email')
                ->filter()
                ->map(fn ($e) => strtolower(trim((string) $e)))
                ->unique()
                ->values()
                ->all();

            if (!empty($investorUserEmails)) {
                DB::table('employees')
                    ->where(function ($q) use ($investorUserEmails) {
                        $q->whereIn(DB::raw('LOWER(official_mail)'), $investorUserEmails)
                          ->orWhereIn(DB::raw('LOWER(personal_mail)'), $investorUserEmails);
                    })
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed
    }
};

