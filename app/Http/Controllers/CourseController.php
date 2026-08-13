<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('prerequisites')->paginate(10);

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        $courses = Course::orderBy('code')->get(['id', 'code', 'name']);

        return view('courses.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prerequisites' => 'array',
            'prerequisites.*' => 'exists:courses,id',
        ]);

        $course = Course::create($request->all());
        $course->prerequisites()->sync($request->input('prerequisites', []));

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $courses = Course::orderBy('code')->whereKeyNot($course->id)->get(['id', 'code', 'name']);
        $selectedPrerequisites = $course->prerequisites()->pluck('courses.id')->toArray();

        return view('courses.edit', compact('course', 'courses', 'selectedPrerequisites'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prerequisites' => 'array',
            'prerequisites.*' => 'exists:courses,id',
        ]);

        $course->update($request->all());
        $course->prerequisites()->sync($request->input('prerequisites', []));

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
