<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiBlogService
{
    public function __construct(private AiTextClient $textClient)
    {
    }

    public function generate(array $input): array
    {
        $topic = $input['topic'];
        $tone = $input['tone'] ?? null;
        $keywords = $input['keywords'] ?? [];

        $trends = $this->fetchTrends($topic);
        $research = [
            'topic' => $topic,
            'tone' => $tone,
            'keywords' => $keywords,
            'trends' => $trends,
        ];

        $prompt = $this->buildContentPrompt($research);
        $raw = $this->textClient->generate($prompt);

        $payload = $this->parseJson($raw);

        $title = (string) ($payload['title'] ?? $topic);
        $slug = (string) ($payload['slug'] ?? Str::slug($title));
        $excerpt = (string) ($payload['excerpt'] ?? '');
        $content = (string) ($payload['content_html'] ?? $payload['content'] ?? '');
        $metaTitle = (string) ($payload['meta_title'] ?? $title);
        $metaDescription = (string) ($payload['meta_description'] ?? Str::limit(strip_tags($excerpt), 155, ''));

        $featuredImage = $this->generateImage(
            $this->buildFeaturedImagePrompt($topic, $tone),
            "blog/{$slug}-featured-" . time() . '.png',
            1280,
            720
        );

        $headings = $this->extractHeadings($payload, $content);
        $inlineImages = [];
        foreach ($headings as $index => $heading) {
            $inlineImages[] = $this->generateImage(
                $this->buildSectionImagePrompt($heading, $topic, $tone),
                "blog/sections/{$slug}-section-{$index}-" . time() . '.png',
                1024,
                1024
            );
        }

        $contentWithImages = $this->injectInlineImages($content, $inlineImages);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $contentWithImages,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'featuredImageUrl' => $featuredImage,
            'inlineImageUrls' => $inlineImages,
        ];
    }

    private function buildContentPrompt(array $research): string
    {
        $keywords = is_array($research['keywords']) ? implode(', ', $research['keywords']) : (string) $research['keywords'];
        $trends = $research['trends'] ? implode(', ', $research['trends']) : 'No trend data available';
        $tone = $research['tone'] ?: 'professional';

        return <<<PROMPT
Generate a high-quality blog post as JSON only. Use the topic and signals below.

Topic: {$research['topic']}
Tone: {$tone}
Keywords: {$keywords}
External signals (trends): {$trends}

Return JSON with keys:
- title
- slug
- excerpt
- content_html (full HTML with headings, paragraphs, and lists)
- meta_title
- meta_description
- headings (array of H2 headings for image generation)

Do not include markdown fencing or extra text.
PROMPT;
    }

    private function buildFeaturedImagePrompt(string $topic, ?string $tone): string
    {
        $toneText = $tone ?: 'professional';
        return "Highly detailed editorial illustration for blog featured image about {$topic}, {$toneText} tone, cinematic lighting, ultra sharp, professional composition, 8k, vivid colors.";
    }

    private function buildSectionImagePrompt(string $heading, string $topic, ?string $tone): string
    {
        $toneText = $tone ?: 'professional';
        return "Highly detailed editorial illustration representing section '{$heading}' in a blog about {$topic}, {$toneText} tone, cinematic lighting, ultra sharp, vivid colors.";
    }

    private function generateImage(string $prompt, string $path, int $width, int $height): string
    {
        $endpoint = config('services.ai_blog.image_endpoint', 'https://image-ai.desmart79.workers.dev');
        $key = config('services.ai_blog.image_key');

        if (!$key) {
            throw new \RuntimeException('IMAGE_AI_KEY is not configured.');
        }

        $headers = str_starts_with($key, 'Bearer ') ? ['Authorization' => $key] : ['Authorization' => "Bearer {$key}"];
        try {
            $response = Http::timeout(120)
                ->withHeaders($headers)
                ->post($endpoint, [
                    'prompt' => $prompt,
                    'width' => $width,
                    'height' => $height,
                ]);
        } catch (\Throwable $e) {
            Log::error('AI image request failed.', [
                'endpoint' => $endpoint,
                'width' => $width,
                'height' => $height,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        if (!$response->ok()) {
            Log::error('AI image provider error response.', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Image generation failed: ' . $response->body());
        }

        Storage::disk('public')->put($path, $response->body());

        return Storage::url($path);
    }

    private function parseJson(string $raw): array
    {
        $clean = trim($raw);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```[a-zA-Z]*\n/', '', $clean);
            $clean = preg_replace('/\n```$/', '', $clean);
        }

        $data = json_decode($clean, true);
        if (!is_array($data)) {
            throw new \RuntimeException('AI response is not valid JSON.');
        }
        return $data;
    }

    private function extractHeadings(array $payload, string $content): array
    {
        $headings = [];
        if (!empty($payload['headings']) && is_array($payload['headings'])) {
            $headings = array_values(array_filter($payload['headings'], fn($h) => is_string($h) && trim($h) !== ''));
        }

        if (empty($headings)) {
            if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches)) {
                $headings = array_map(fn($h) => strip_tags($h), $matches[1]);
            }
        }

        return array_slice($headings, 0, 3);
    }

    private function injectInlineImages(string $content, array $imageUrls): string
    {
        if (empty($imageUrls)) {
            return $content;
        }

        $index = 0;
        return preg_replace_callback('/(<h2[^>]*>.*?<\/h2>)/i', function ($matches) use (&$index, $imageUrls) {
            if (!isset($imageUrls[$index])) {
                return $matches[1];
            }

            $url = $imageUrls[$index];
            $index++;
            $img = "<div class=\"blog-inline-image\"><img src=\"{$url}\" alt=\"Section illustration\" /></div>";
            return $matches[1] . $img;
        }, $content);
    }

    private function fetchTrends(string $topic): array
    {
        $endpoint = config('services.ai_blog.trends_endpoint');
        $key = config('services.ai_blog.trends_key');
        if (!$endpoint || !$key) {
            return [];
        }

        try {
            $response = Http::timeout(12)->get($endpoint, [
                'engine' => 'google_trends',
                'q' => $topic,
                'hl' => 'en',
                'gl' => 'us',
                'api_key' => $key,
            ]);

            if (!$response->ok()) {
                Log::warning('AI trends provider error response.', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [];
            }

            $trends = [];
            $relatedQueries = $data['related_queries']['top'] ?? [];
            if (is_array($relatedQueries)) {
                foreach ($relatedQueries as $item) {
                    if (!empty($item['query'])) {
                        $trends[] = $item['query'];
                    }
                }
            }

            if (empty($trends)) {
                $relatedTopics = $data['related_topics']['top'] ?? [];
                if (is_array($relatedTopics)) {
                    foreach ($relatedTopics as $item) {
                        $title = $item['topic']['title'] ?? $item['topic'] ?? null;
                        if (is_string($title) && $title !== '') {
                            $trends[] = $title;
                        }
                    }
                }
            }

            return array_slice(array_values(array_unique($trends)), 0, 5);
        } catch (\Throwable $e) {
            Log::warning('AI trends request failed.', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            return [];
        }

        return [];
    }
}
