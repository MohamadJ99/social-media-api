<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GeneratePostRequest;
use App\Services\AI\AIPostService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AIPostController extends Controller
{
    public function generate(
        GeneratePostRequest $request,
        AIPostService $aiPostService
    ): JsonResponse {
        try {
            $content = $aiPostService->generate(
                $request->string('topic')->toString(),
                $request->string('tone')->toString(),
                $request->string('language')->toString()
            );

            return response()->json([
                'content' => $content,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' =>
                    'Unable to generate post at this time.',
            ], 503);
        }
    }
}