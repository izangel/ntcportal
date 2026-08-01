<?php
// app/Models/CourseLearningOutcome.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseLearningOutcome extends Model
{
    protected $fillable = ['course_id', 'blooms_taxonomy_id', 'code', 'description','effective_batch_year'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function bloomsTaxonomy(): BelongsTo
    {
        return $this->belongsTo(BloomsTaxonomy::class);
    }

    public function programOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(ProgramOutcome::class, 'clo_program_outcome')
                    ->withPivot('level')
                    ->withTimestamps();
    }

    public function assessmentItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssessmentItem::class);
    }

    public function getAttainmentAttribute()
    {
        $totalMaxMarks = 0;
        $totalObtainedMarks = 0;

        // Load assessment items with their student marks
        $this->loadMissing('assessmentItems');

        foreach ($this->assessmentItems as $item) {
            $marks = StudentAssessmentMark::where('assessment_item_id', $item->id)->get();
            $studentCount = $marks->count();

            if ($studentCount > 0) {
                $totalMaxMarks += ($item->max_marks * $studentCount);
                $totalObtainedMarks += $marks->sum('marks_obtained');
            }
        }

        return $totalMaxMarks > 0 
            ? round(($totalObtainedMarks / $totalMaxMarks) * 100, 1) 
            : 0;
    }

    
}