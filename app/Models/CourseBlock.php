<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseBlock extends Model
{
    protected $fillable = [
        'section_id',
        'course_id',
        'faculty_id',
        'academic_year_id',
        'semester',
        'room_name',
        'schedule_string',
        'finalized',
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function sections()
{
    // Ensure the table name 'course_block_section' matches your DB exactly
    return $this->belongsToMany(Section::class, 'course_block_section', 'course_block_id', 'section_id')
                ->withPivot('academic_year_id', 'semester');
}

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Employee::class, 'faculty_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function batchYear(): ?int
    {
        $startYear = $this->academicYear?->start_year;
        if (!$startYear) {
            return null;
        }

        $sections = $this->sections()->get();

        $grade = $sections->pluck('name')
            ->map(function ($name) {
                preg_match('/\d+/', (string) $name, $matches);
                return isset($matches[0]) ? (int) $matches[0] : null;
            })
            ->filter()
            ->min();

        if (!$grade) {
            return (int) $startYear;
        }

        $firstGrade = $grade >= 11 ? 11 : 1;

        return (int) $startYear - ($grade - $firstGrade);
    }

    protected static function booted()
    {
        static::created(function ($courseBlock) {
            // Sections are linked through course_block_section; fall back to the
            // legacy section_id column when no pivot links exist yet.
            $sections = $courseBlock->sections()->get();
            if ($sections->isEmpty() && $courseBlock->section_id) {
                $sections = Section::where('id', $courseBlock->section_id)->get();
            }

            foreach ($sections as $section) {
                // 1. Find all students already registered in this section
                $registrations = \App\Models\SectionStudent::where('section_id', $section->id)
                    ->where('academic_year_id', $courseBlock->academic_year_id)
                    ->where('semester', $courseBlock->semester)
                    ->get();

                // 2. Enroll them in this specific newly created course block
                foreach ($registrations as $reg) {
                    \App\Models\Enrollment::firstOrCreate([
                        'student_id'       => $reg->student_id,
                        'course_id'        => $courseBlock->course_id,
                        'section_id'       => $section->id,
                        'academic_year_id' => $courseBlock->academic_year_id,
                        'semester'         => $courseBlock->semester,
                    ]);
                }
            }
        });
    }

    public function attainment(): HasOne
    {
        // 'course_session_id' is the foreign key in your course_attainments table
        return $this->hasOne(CourseAttainment::class, 'course_session_id');
    }

    public function syllabus(): HasOne
    {
        return $this->hasOne(CourseSyllabus::class, 'course_block_id');
    }

    // Pivot table mapping from student_courseblock
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_courseblock', 'course_block_id', 'student_id')
                    ->withPivot('grade', 'remarks');
    }
}