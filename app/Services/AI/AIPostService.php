<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AIPostService
{
    public function generate(
        string $topic,
        string $tone,
        string $language
    ): string {
        $response = Http::withToken(
            config('services.openrouter.key')
        )
            ->acceptJson()
            ->timeout(60)
            ->post(
                config('services.openrouter.base_url')
                    . '/chat/completions',
                [
                    'model' => config(
                        'services.openrouter.model'
                    ),

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' =>
                                $this->systemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' =>
                                $this->buildPrompt(
                                    $topic,
                                    $tone,
                                    $language
                                ),
                        ],
                    ],

                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Unable to generate post at this time.'
            );
        }

        $content = $response->json(
            'choices.0.message.content'
        );

        if (
            !is_string($content) ||
            trim($content) === ''
        ) {
            throw new RuntimeException(
                'The AI provider returned an empty response.'
            );
        }

        return trim($content);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI writing assistant for a social media application.

Generate a natural, engaging social media post based on the user's topic.

Rules:
- Return only the final post.
- Do not explain your answer.
- Do not use markdown headings.
- Keep the post concise and suitable for social media.
- Follow the requested tone and language.
PROMPT;
    }

    private function buildPrompt(
        string $topic,
        string $tone,
        string $language
    ): string {
        return <<<PROMPT
Topic:
{$topic}

Tone:
{$tone}

Language:
{$language}

Write one polished social media post.
PROMPT;
    }
}