<?php

namespace App\Traits;

use App\Models\AcademicYear;
use App\Models\Candidacy;
use App\Models\ElectionVote;
use App\Models\Position;

trait VotingQueries
{
    /**
     * Get approved candidates scoped appropriately.
     */
    protected function approvedCandidatesQuery(?AcademicYear $activeAcademicYear, ?string $programType = null)
    {
        $query = Candidacy::with('student.user')
            ->where('status', 'approved')
            ->whereIn('position_applied', array_keys($this->positionOrder($programType)))
            ->when($activeAcademicYear, function ($query) use ($activeAcademicYear) {
                $query->where('academic_year_id', $activeAcademicYear->id);
            })
            ->orderByRaw($this->positionOrderCaseStatement())
            ->orderBy('created_at');

        return $query;
    }

    /**
     * Canonical position ordering for the ballot.
     * Fetches from DB positions table, optionally filtered by program type.
     */
    protected function positionOrder(?string $programType = null): array
    {
        $query = Position::where('is_active', true)->orderBy('sort_order');

        if ($programType) {
            $query->whereIn('program_type', [$programType, 'both']);
        }

        return $query->pluck('name', 'slug')->toArray();
    }

    /**
     * SQL CASE statement for position ordering.
     */
    protected function positionOrderCaseStatement(): string
    {
        $cases = Position::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('slug')
            ->values();

        $sql = 'CASE position_applied';
        $index = 1;
        foreach ($cases as $slug) {
            $sql .= " WHEN '{$slug}' THEN {$index}";
            $index++;
        }
        $sql .= ' ELSE 99 END';

        return $sql;
    }

    /**
     * Convert a position key into a display label.
     */
    protected function formatPosition(string $position): string
    {
        $positionModel = Position::where('slug', $position)->first();
        return $positionModel ? $positionModel->name : ucwords(str_replace('_', ' ', $position));
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
