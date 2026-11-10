<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReportReviewService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    public function transition(
        Report $report,
        User $reviewer,
        ReportStatus $status,
        string $note
    ): Report {
        return DB::transaction(function () use (
            $report,
            $reviewer,
            $status,
            $note
        ) {
            $lockedReport = Report::query()
                ->lockForUpdate()
                ->findOrFail($report->getKey());

            $previousStatus = $lockedReport->status;

            if (!$this->canTransition($previousStatus, $status)) {
                throw new ConflictHttpException(
                    'Invalid report status transition.'
                );
            }

            $lockedReport->status = $status;
            $lockedReport->save();

            $lockedReport->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'previous_status' => $previousStatus->value,
                'new_status' => $status->value,
                'note' => $note,
            ]);

            $this->activityLogService->log(
                "report.{$status->value}",
                $lockedReport,
                "Report #{$lockedReport->id} marked as {$status->value}",
                [
                    'previous_status' => $previousStatus->value,
                    'new_status' => $status->value,
                    'note' => $note,
                ]
            );

            return $lockedReport->refresh();

        }, 3);
    }

    private function canTransition(
        ReportStatus $current,
        ReportStatus $target
    ): bool {
        return match ($current) {
            ReportStatus::Pending =>
                $target === ReportStatus::Reviewed,

            ReportStatus::Reviewed =>
                in_array($target, [
                    ReportStatus::Resolved,
                    ReportStatus::Dismissed,
                ], true),

            ReportStatus::Resolved,
            ReportStatus::Dismissed => false,
        };
    }
}
