<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(private StoryService $storyService) {}

    public function index(Request $request): JsonResponse
    {
        $feed = $this->storyService->getFeed(
            $request->user()
        );

        $data = $feed->map(
            function (array $group) use ($request) {
                return [
                    'user' => [
                        'id' => $group['user']->id,
                        'name' => $group['user']->name,
                        'username' => $group['user']->username,
                        'avatar' => $group['user']->avatar,
                    ],

                    'is_current_user' =>
                    $group['is_current_user'],

                    'has_unseen_stories' =>
                    $group['has_unseen_stories'],

                    'stories' => StoryResource::collection(
                        $group['stories']
                    )->resolve($request),
                ];
            }
        );

        return response()->json([
            'data' => $data,
        ]);
    }
    public function store(StoreStoryRequest $request): JsonResponse
    {
        $story = $this->storyService->create(
            user: $request->user(),
            media: $request->file('media'),
            caption: $request->input('caption'),
            visibility: $request->input('visibility')
        );

        return response()->json([
            'message' => 'Story created successfully.',
            'story' => new StoryResource($story),
        ], 201);
    }

    public function destroy(Story $story): JsonResponse
    {
        Gate::authorize('delete', $story);

        $this->storyService->delete($story);

        return response()->json([
            'message' => 'Story deleted successfully.',
        ]);
    }


    public function view(Request $request, Story $story): JsonResponse
    {
        Gate::authorize('view', $story);

        $this->storyService->recordView(
            $request->user(),
            $story
        );

        return response()->json([
            'message' => 'Story viewed successfully.',
        ]);
    }


    public function viewers(
        Story $story
    ): JsonResponse {
        Gate::authorize('viewViewers', $story);

        $views = $this->storyService->getViewers(
            $story
        );

        return response()->json([
            'views_count' => $views->count(),

            'viewers' => $views->map(function ($view) {
                return [
                    'id' => $view->viewer->id,
                    'name' => $view->viewer->name,
                    'username' => $view->viewer->username,
                    'avatar' => $view->viewer->avatar,
                    'viewed_at' => $view->viewed_at,
                ];
            })->values(),
        ]);
    }
}
