<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // For Students and Staff to see the list
    public function index(Request $request)
    {
        $query = Announcement::with('author')->ordered();

        // Filtering by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $announcements = $query->paginate(10);
        $categories = Announcement::$categories;

        return view('announcements.index', compact('announcements', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category' => 'required|in:' . implode(',', Announcement::$categories),
        ]);

        auth()->user()->announcements()->create([
            'title' => $request->title,
            'body' => $request->body,
            'category' => $request->category,
            'is_pinned' => $request->has('is_pinned'),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement posted!');
    }
    // Admin/Teacher view to create
    public function create()
    {
        return view('announcements.create');
    }

    
    public function edit(Announcement $announcement)
    {
        // Ensure the user is authorized (extra safety)
        $this->authorize('post-announcements');
        
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('post-announcements');

        $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category' => 'required|in:' . implode(',', Announcement::$categories), // Added validation
        ]);

        $announcement->update([
            'title' => $request->title,
            'body' => $request->body,
            'category' => $request->category, // THIS was likely missing
            'is_pinned' => $request->has('is_pinned'),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully!');
    }

    public function show(Announcement $announcement)
    {
        // Eager load the author to avoid extra queries in the view
        $announcement->load('author');
        
        return view('announcements.show', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('post-announcements');
        
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }


}