<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\AcademicYear;
use App\Models\CourseBlock;
use App\Models\Employee;
use App\Notifications\ObeDataReminder;
use App\Services\ObeDataCompleteness;

class SendObeDataReminders extends Command
{
    protected $signature = 'obe:send-reminders
        {--ay= : Only remind for this academic year start year (e.g. 2025)}
        {--faculty= : Only remind for this employee (faculty) id}
        {--dry-run : Report what would be sent without sending}';

    protected $description = 'Notify faculty about course blocks with incomplete OBE data (assessments, scores, CLO attainment)';

    public function handle(): int
    {
        $query = CourseBlock::query()
            ->with(['course', 'academicYear', 'faculty.user', 'students'])
            ->whereNotNull('faculty_id');

        if ($ay = $this->option('ay')) {
            $query->whereHas('academicYear', fn ($q) => $q->where('start_year', (int) $ay));
        }

        if ($faculty = $this->option('faculty')) {
            $query->where('faculty_id', (int) $faculty);
        }

        $blocks = $query->get();

        if ($blocks->isEmpty()) {
            $this->info('No course blocks found to check.');
            return self::SUCCESS;
        }

        $missingByBlock = ObeDataCompleteness::evaluateMany($blocks);

        $sent = 0;
        $alreadyNotified = 0;
        $noUser = 0;

        foreach ($blocks as $block) {
            $missing = $missingByBlock[$block->id] ?? [];

            if (empty($missing)) {
                continue;
            }

            $user = $block->faculty?->user;

            if (!$user) {
                $noUser++;
                continue;
            }

            $already = DatabaseNotification::query()
                ->where('notifiable_id', $user->id)
                ->where('type', ObeDataReminder::class)
                ->whereNull('read_at')
                ->where('data->course_block_id', $block->id)
                ->exists();

            if ($already) {
                $alreadyNotified++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [DRY] Would notify {$user->email} for block #{$block->id} ({$block->course->code}): ".implode(', ', $missing));
                $sent++;
                continue;
            }

            $user->notify(new ObeDataReminder($block, $missing));
            $sent++;
        }

        $this->info("Checked {$blocks->count()} block(s). Sent {$sent} new reminder(s). Already pending: {$alreadyNotified}. Skipped (no linked user): {$noUser}.");

        return self::SUCCESS;
    }
}
