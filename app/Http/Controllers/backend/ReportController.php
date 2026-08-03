<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Recruiter;
use App\Models\Report;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Report::class);
        [$query, $filters, $recruiters, $scopeLabel] = $this->reportQuery($request);
        $total = (clone $query)->count();

        return view('backend.reports.index', [
            'filters' => $filters,
            'recruiters' => $recruiters,
            'scopeLabel' => $scopeLabel,
            'total' => $total,
            'recruiterReport' => $this->breakdown($query, 'recruiters', 'recruiter_id', 'recruiter_name', $total, 'Unassigned recruiter'),
            'clientReport' => $this->breakdown($query, 'clients', 'client_id', 'client', $total, 'Unassigned client'),
            'levelReport' => $this->breakdown($query, 'level_of_interviews', 'level_of_interview_id', 'level', $total, 'No interview level', 'sort_order'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Report::class);
        [$query] = $this->reportQuery($request);
        $fileName = 'candidate-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Created Date', 'Recruiter', 'Client', 'Candidate', 'Interview Level', 'Status']);
            (clone $query)->with(['recruiter', 'client', 'interviewLevel'])->orderBy('id')->chunkById(500, function ($candidates) use ($output) {
                foreach ($candidates as $candidate) {
                    fputcsv($output, [
                        $candidate->created_at?->format('Y-m-d'),
                        $candidate->recruiter?->recruiter_name ?? 'Unassigned recruiter',
                        $candidate->client?->client ?? 'Unassigned client',
                        $candidate->candidate_name,
                        $candidate->interviewLevel?->level ?? 'No interview level',
                        $candidate->status ? 'Active' : 'Inactive',
                    ]);
                }
            });
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportQuery(Request $request): array
    {
        $user = $request->user()->loadMissing('role');
        $role = $user->role?->access_level;
        $isRecruiter = (int) $user->role_id === 3 || $role === 'recruiter';
        // Any non-recruiter role granted the report permission receives the
        // organization view; recruiter accounts remain restricted to themselves.
        $canViewAll = ! $isRecruiter;

        $validated = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'recruiter_id' => ['nullable', 'integer', 'exists:recruiters,id'],
        ]);

        $recruiters = $canViewAll
            ? Recruiter::orderBy('recruiter_name')->get(['id', 'recruiter_name'])
            : collect();
        $linkedRecruiter = $isRecruiter
            ? Recruiter::whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->first()
            : null;

        $selectedRecruiterId = $isRecruiter
            ? ($linkedRecruiter?->id ?? 0)
            : (isset($validated['recruiter_id']) ? (int) $validated['recruiter_id'] : null);
        $query = Candidate::query()
            ->when($selectedRecruiterId !== null, fn (Builder $q) => $q->where('recruiter_id', $selectedRecruiterId))
            ->when(isset($validated['from_date']), fn (Builder $q) => $q->where('candidates.created_at', '>=', CarbonImmutable::parse($validated['from_date'])->startOfDay()))
            ->when(isset($validated['to_date']), fn (Builder $q) => $q->where('candidates.created_at', '<=', CarbonImmutable::parse($validated['to_date'])->endOfDay()));

        return [$query, [
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'recruiter_id' => $selectedRecruiterId,
            'is_recruiter' => $isRecruiter,
            'linked' => ! $isRecruiter || (bool) $linkedRecruiter,
        ], $recruiters, $isRecruiter ? ($linkedRecruiter?->recruiter_name ?? 'Unlinked recruiter account') : ($selectedRecruiterId ? ($recruiters->firstWhere('id', $selectedRecruiterId)?->recruiter_name ?? 'Recruiter') : 'All recruiters')];
    }

    private function breakdown(Builder $base, string $table, string $foreignKey, string $labelColumn, int $total, string $emptyLabel, ?string $orderColumn = null)
    {
        return (clone $base)
            ->leftJoin($table, $table.'.id', '=', 'candidates.'.$foreignKey)
            ->selectRaw('candidates.'.$foreignKey.' as item_id, COALESCE('.$table.'.'.$labelColumn.', ?) as label, COUNT(candidates.id) as total', [$emptyLabel])
            ->groupBy('candidates.'.$foreignKey, $table.'.'.$labelColumn, ...($orderColumn ? [$table.'.'.$orderColumn] : []))
            ->orderBy($orderColumn ? $table.'.'.$orderColumn : 'total', $orderColumn ? 'asc' : 'desc')
            ->get()
            ->map(function ($row) use ($total) {
                $row->percentage = $total > 0 ? round(((int) $row->total / $total) * 100, 2) : 0.0;
                return $row;
            });
    }
}
