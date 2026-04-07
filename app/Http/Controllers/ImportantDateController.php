<?php

namespace App\Http\Controllers;

use App\Models\ImportantDate;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportantDateController extends Controller
{
    /**
     * Display a listing of important dates.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $today = now()->toDateString(); // Get current date in YYYY-MM-DD

        $query = ImportantDate::with(['categories', 'author']);

        // 1. Filter by Category (if selected)
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
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

        $dates = $query->paginate(10);

        return view('important_dates.index', compact('dates', 'categories'));
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
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string', // Adding this ensures the key exists in $validated
        'start_date'  => 'required|date',
        'end_date'    => 'nullable|date|after_or_equal:start_date',
        'categories'  => 'required|array',
        'categories.*' => 'exists:categories,id',
    ]);


        // 1. Create the date record
        $importantDate = ImportantDate::create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'start_date'  => $validated['start_date'],
            'end_date'  => $validated['end_date'],

            'user_id'     => Auth::id(),
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
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string', // Adding this ensures the key exists in $validated
        'start_date'  => 'required|date',
        'end_date'    => 'nullable|date|after_or_equal:start_date',
        'categories'  => 'required|array',
        'categories.*' => 'exists:categories,id',
    ]);

    // Now $validated['description'] will safely exist (even if it's null)
    $importantDate->update([
        'title'       => $validated['title'],
        'description' => $validated['description'], 
        'start_date'  => $validated['start_date'],
        'end_date'    => $validated['end_date'],
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