<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Holiday::class);

        $year = (int) ($request->input('year', now()->year));
        $search = $request->input('search');

        $query = Holiday::forYear($year)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        $totalHolidays = Holiday::forYear($year)->count();
        $upcomingCount = Holiday::forYear($year)->whereDate('date', '>=', now()->toDateString())->count();
        $pastCount = Holiday::forYear($year)->whereDate('date', '<', now()->toDateString())->count();
        $nextHoliday = Holiday::upcoming()->first();

        $holidays = $query->orderBy('date', 'asc')->paginate(25)->withQueryString();

        $availableYears = Holiday::selectRaw('DISTINCT year')->orderByDesc('year')->pluck('year')->all();
        $systemYears = [now()->year - 1, now()->year, now()->year + 1, now()->year + 2];
        $allYears = array_values(array_unique(array_merge($systemYears, $availableYears)));
        sort($allYears);

        return view('backend.holidays.index', compact(
            'holidays',
            'year',
            'search',
            'allYears',
            'totalHolidays',
            'upcomingCount',
            'pastCount',
            'nextHoliday'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Holiday::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'date' => ['required', 'date_format:Y-m-d', 'unique:holidays,date'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $holiday = Holiday::create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'year' => (int) Carbon::parse($validated['date'])->year,
            'type' => $validated['type'] ?: 'Company',
            'description' => $validated['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.holidays.index', ['year' => $holiday->year])
            ->with('success', "Holiday '{$holiday->title}' added successfully on {$holiday->formatted_date}. Attendance records have been marked as Holiday.");
    }

    public function update(Request $request, Holiday $holiday)
    {
        $this->authorize('edit', Holiday::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'date' => ['required', 'date_format:Y-m-d', 'unique:holidays,date,' . $holiday->id],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $holiday->update([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'year' => (int) Carbon::parse($validated['date'])->year,
            'type' => $validated['type'] ?: 'Company',
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.holidays.index', ['year' => $holiday->year])
            ->with('success', "Holiday '{$holiday->title}' updated successfully.");
    }

    public function destroy(Holiday $holiday)
    {
        $this->authorize('delete', Holiday::class);

        $title = $holiday->title;
        $year = $holiday->year;
        $holiday->delete();

        return redirect()
            ->route('admin.holidays.index', ['year' => $year])
            ->with('success', "Holiday '{$title}' removed successfully and attendance updated.");
    }

    public function listJson(Request $request)
    {
        $year = (int) ($request->input('year', now()->year));

        $holidays = Holiday::forYear($year)->orderBy('date', 'asc')->get();

        return response()->json([
            'year' => $year,
            'total' => $holidays->count(),
            'holidays' => $holidays->map(function ($h) {
                return [
                    'id' => $h->id,
                    'title' => $h->title,
                    'date' => $h->date->toDateString(),
                    'formatted_date' => $h->formatted_date,
                    'day_name' => $h->day_name,
                    'type' => $h->type ?: 'Company',
                    'description' => $h->description,
                    'is_upcoming' => $h->is_upcoming,
                    'is_today' => $h->is_today,
                    'is_past' => $h->is_past,
                ];
            }),
        ]);
    }
}
