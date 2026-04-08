<?php

namespace App\Traits;

use App\Models\AcademicYear;
use App\Models\Candidacy;
use App\Models\ElectionVote;

trait VotingQueries
{
    /**
     * Get approved candidates scoped appropriately.
     */
    protected function approvedCandidatesQuery(?AcademicYear $activeAcademicYear)
    {
        return Candidacy::with('student.user')
            ->where('status', 'approved')
            ->whereIn('position_applied', array_keys($this->positionOrder()))
            ->when($activeAcademicYear, function ($query) use ($activeAcademicYear) {
                $query->where('academic_year_id', $activeAcademicYear->id);
            })
            ->orderByRaw($this->positionOrderCaseStatement())
            ->orderBy('created_at');
    }

    /**
     * Canonical position ordering for the ballot.
     */
    protected function positionOrder(): array
    {
        return [
            'president' => 'President',
            'vice_president' => 'Vice President',
            'secretary' => 'Secretary',
            'treasurer' => 'Treasurer',
            'auditor' => 'Auditor',
            'pio' => 'PIO',
            'business_manager' => 'Business Manager',
        ];
    }

    /**
     * SQL CASE statement for position ordering.
     */
    protected function positionOrderCaseStatement(): string
    {
        return "CASE position_applied
            WHEN 'president' THEN 1
            WHEN 'vice_president' THEN 2
            WHEN 'secretary' THEN 3
            WHEN 'treasurer' THEN 4
            WHEN 'auditor' THEN 5
            WHEN 'pio' THEN 6
            WHEN 'business_manager' THEN 7
            ELSE 99
        END";
    }

    /**
     * Convert a position key into a display label.
     */
    protected function formatPosition(string $position): string
    {
        return $this->positionOrder()[$position] ?? ucwords(str_replace('_', ' ', $position));
    }

    /**
     * Build a query for a student's votes scoped to the current election context.
     */
    protected function studentVotesQueryForElection(int $studentId, ?AcademicYear $activeAcademicYear)
    {
        return ElectionVote::query()
            ->where('student_id', $studentId)
            ->when(
                $activeAcademicYear,
                fn ($query) => $query->whereHas('candidacy', fn ($candidacyQuery) => $candidacyQuery->where('academic_year_id', $activeAcademicYear->id)),
                fn ($query) => $query->whereHas('candidacy', fn ($candidacyQuery) => $candidacyQuery->whereNull('academic_year_id'))
            );
    }

    /**
     * Build a base votes query scoped to an academic year via the candidacy relation.
     */
    protected function votesQueryForAcademicYear(?AcademicYear $activeAcademicYear)
    {
        return ElectionVote::query()
            ->when(
                $activeAcademicYear,
                fn ($query) => $query->whereHas('candidacy', fn ($candidacyQuery) => $candidacyQuery->where('academic_year_id', $activeAcademicYear->id)),
                fn ($query) => $query->whereHas('candidacy', fn ($candidacyQuery) => $candidacyQuery->whereNull('academic_year_id'))
            );
    }
}
