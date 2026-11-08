<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexReportsRequest;
use App\Http\Resources\Admin\ReportResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function index(
        IndexReportsRequest $request
    ): AnonymousResourceCollection {

        $filters = $request->validated();

        $query = Report::query()
            ->with([
                'user:id,name,username,avatar',

                'reportable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Post::class => [
                            'user:id,name,username,avatar',
                        ],

                        Comment::class => [
                            'user:id,name,username,avatar',
                            'post:id,content,user_id',
                        ],
                    ]);
                },
            ]);

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (!empty($filters['reason'])) {
            $query->where(
                'reason',
                $filters['reason']
            );
        }

        if (!empty($filters['type'])) {
            $type = $filters['type'] === 'post'
                ? Post::class
                : Comment::class;

            $query->where(
                'reportable_type',
                $type
            );
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search) {

                $builder->whereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHasMorph(
                    'reportable',
                    [Post::class, Comment::class],
                    function (Builder $contentQuery) use ($search) {
                        $contentQuery->where(
                            'content',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            });
        }

        if (!empty($filters['from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['from']
            );
        }

        if (!empty($filters['to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['to']
            );
        }

        $reports = $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ReportResource::collection($reports);
    }

    public function show(Report $report): ReportResource
    {
        $report->load([
            'user:id,name,username,avatar',

            'reportable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Post::class => [
                        'user:id,name,username,avatar',
                    ],

                    Comment::class => [
                        'user:id,name,username,avatar',
                        'post:id,content,user_id',
                    ],
                ]);
            },
        ]);

        return new ReportResource($report);
    }
}
