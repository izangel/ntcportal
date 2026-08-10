<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImportantDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportantDateController extends Controller
{
    /**
     * Display a listing of important dates as a month calendar (default)
     * or as a list table.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $today = now()->toDateString(); // Get current date in YYYY-MM-DD

        $query = ImportantDate::with(['categories', 'author']);

        // 1. Filter by Category (if selected)
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // 2. Priority Sorting: Ongoing (1), Upcoming (2), Passed (3)
        $query->orderByRaw("
            CASE 
                WHEN '$today' BETWEEN start_date AND COALESCE(end_date, start_date) THEN 1
                WHEN start_date > '$today' THEN 2
                ELSE 3
            END ASC
        ")
        // 3. Secondary Sorting (Show the soonest dates first within their groups)
            ->orderBy('start_date', 'asc');

        // View mode: calendar (default) or list
        $view = $request->input('view', 'calendar');

        // Paginated list for the list view
        $dates = $query->paginate(10);

        // --- Calendar grid (month navigation) ---
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        $firstOfMonth = Carbon::createFromDate($year, $month, 1);
        $monthStart = $firstOfMonth->copy()->startOfMonth();
        $monthEnd = $firstOfMonth->copy()->endOfMonth();

        // Fetch events overlapping the visible month (multi-day events included)
        $events = (clone $query)
            ->where('start_date', '<=', $monthEnd->toDateString())
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('end_date')
                    ->where('start_date', '>=', $monthStart->toDateString())
                    ->orWhere('end_date', '>=', $monthStart->toDateString());
            })
            ->get();

        // Map each date in the month to its events
        $eventsByDate = [];
        foreach ($events as $event) {
            $from = $event->start_date->copy()->max($monthStart);
            $to = ($event->end_date ?? $event->start_date)->copy()->min($monthEnd);
            for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
                $eventsByDate[$day->format('Y-m-d')][] = $event;
            }
        }
        foreach ($eventsByDate as $key => $list) {
            usort($list, fn ($a, $b) => $a->start_date->timestamp <=> $b->start_date->timestamp);
            $eventsByDate[$key] = $list;
        }

        // Build the week grid starting on Monday
        $offset = ($firstOfMonth->dayOfWeek + 6) % 7;
        $totalCells = (int) ceil(($offset + $firstOfMonth->daysInMonth) / 7) * 7;
        $gridStart = $firstOfMonth->copy()->subDays($offset);

        $calendarGrid = [];
        $cursor = $gridStart->copy();
        for ($w = 0, $weeks = $totalCells / 7; $w < $weeks; $w++) {
            $week = [];
            foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $label) {
                $key = $cursor->format('Y-m-d');
                $week[] = [
                    'label' => $label,
                    'date' => $cursor->copy(),
                    'in_month' => $cursor->month === $month,
                    'is_today' => $key === $today,
                    'events' => $eventsByDate[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $calendarGrid[] = $week;
        }

        $prevMonth = $firstOfMonth->copy()->subMonthNoOverflow();
        $nextMonth = $firstOfMonth->copy()->addMonthNoOverflow();

        return view('important_dates.index', [
            'categories' => $categories,
            'dates' => $dates,
            'view' => $view,
            'categoryId' => $request->input('category_id', ''),
            'month' => $month,
            'year' => $year,
            'calendarTitle' => $firstOfMonth->format('F Y'),
            'calendarGrid' => $calendarGrid,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * Show the form for creating a new date.
     */
    public function create()
    {
        // Get all categories so the admin/teacher can select them
        $categories = Category::all();

        return view('important_dates.create', compact('categories'));
    }

    /**
     * Store a newly created date in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string', // Adding this ensures the key exists in $validated
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        // 1. Create the date record
        $importantDate = ImportantDate::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],

            'user_id' => Auth::id(),
        ]);

        // 2. Attach the multiple categories via the pivot table
        $importantDate->categories()->sync($request->categories);

        return redirect()->route('important_dates.index')
            ->with('success', 'Important date posted successfully.');
    }

    /**
     * Show the form for editing the specified date.
     */
    public function edit(ImportantDate $importantDate)
    {
        $categories = Category::all();

        // Get the IDs of categories currently linked to this date
        $selectedCategories = $importantDate->categories->pluck('id')->toArray();

        return view('important_dates.edit', compact('importantDate', 'categories', 'selectedCategories'));
    }

    /**
     * Update the specified date in storage.
     */
    public function update(Request $request, ImportantDate $importantDate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string', // Adding this ensures the key exists in $validated
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Now $validated['description'] will safely exist (even if it's null)
        $importantDate->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        $importantDate->categories()->sync($request->categories);

        return redirect()->route('important_dates.index')->with('success', 'Updated successfully!');
    }

    /**
     * Remove the specified date from storage.
     */
    public function destroy(ImportantDate $importantDate)
    {
        $importantDate->delete();

        return redirect()->back()->with('success', 'Date deleted.');
    }
}
