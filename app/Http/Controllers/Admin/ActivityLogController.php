<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexActivityLogsRequest;
use App\Http\Resources\Admin\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ActivityLogController extends Controller
{
    public function index(
        IndexActivityLogsRequest $request
    ): AnonymousResourceCollection {

        $filters = $request->validated();
        $user = $request->user();

        $query = ActivityLog::query()
            ->with('causer:id,name,username,avatar');

        // Moderators can only view their own activity.
        if ($user->role === UserRole::Moderator->value) {
            $query->where('causer_id', $user->id);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (!empty($filters['subject_type'])) {
            $subjectTypes = [
                'post' => (new Post())->getMorphClass(),
                'comment' => (new Comment())->getMorphClass(),
                'report' => (new Report())->getMorphClass(),
                'user' => (new User())->getMorphClass(),
                'ad' => (new Ad())->getMorphClass(),
            ];

            $query->where(
                'subject_type',
                $subjectTypes[$filters['subject_type']]
            );
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'causer',
                        function (Builder $causerQuery) use ($search) {
                            $causerQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        }
                    );
            });
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $logs = $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ActivityLogResource::collection($logs);
    }

    public function show(Request $request, ActivityLog $activityLog)
    {
        $user = $request->user();

        if (
            $user->role === UserRole::Moderator->value &&
            $activityLog->causer_id !== $user->id
        ) {
            return response()->json([
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        $activityLog->load(
            'causer:id,name,username,avatar'
        );

        return new ActivityLogResource($activityLog);
    }
}
