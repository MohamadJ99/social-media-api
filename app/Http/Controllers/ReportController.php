<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function storePost(
        StoreReportRequest $request,
        Post $post
    ): JsonResponse {
        Gate::authorize('create', [
            Report::class,
            $post,
        ]);

        return $this->storeReport($request, $post);
    }

    public function storeComment(
        StoreReportRequest $request,
        Comment $comment
    ): JsonResponse {
        Gate::authorize('create', [
            Report::class,
            $comment,
        ]);

        return $this->storeReport($request, $comment);
    }

    private function storeReport(
        StoreReportRequest $request,
        Post|Comment $reportable
    ): JsonResponse {
        $validated = $request->validated();

        $report = $reportable->reports()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'reason' => $validated['reason'],
                'description' => $validated['description'] ?? null,
            ]
        );

        if (!$report->wasRecentlyCreated) {
            return response()->json([
                'message' => 'You have already reported this content.',
            ], 409);
        }

        return response()->json([
            'message' => 'Report submitted successfully.',
        ], 201);
    }
}
