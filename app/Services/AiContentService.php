<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiContentService
{
    public function __construct(private AiTextClient $textClient)
    {
    }

    public function generateService(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $audience = trim((string) ($input['audience'] ?? ''));
        $goals = trim((string) ($input['goals'] ?? ''));
        $tone = trim((string) ($input['tone'] ?? 'professional'));
        $keywords = $this->normalizeStringList($input['keywords'] ?? []);

        $prompt = <<<PROMPT
Generate JSON only for a professional digital service page.

Service title: {$title}
Service details: {$description}
Target audience: {$audience}
Business goals: {$goals}
Tone: {$tone}
Keywords: {$this->joinList($keywords)}

Return JSON with keys:
- title
- shortDescription (max 180 chars)
- fullDescription (HTML with paragraphs and one list)
- icon (one of: Globe, Bot, Smartphone, Workflow, Palette, TrendingUp, Code, Shield, Zap)
- features (array of 5 strings)
- benefits (array of 5 strings)
- processSteps (array of 4 objects: {step,title,description} where step is "01","02","03","04")
- metaTitle
- metaDescription
Do not include markdown fences or extra text.
PROMPT;

        $payload = $this->parseJson($this->textClient->generate($prompt));
        $finalTitle = (string) ($payload['title'] ?? $title ?: 'Service');
        $slug = Str::slug($finalTitle);

        $imageUrl = $this->generateImage(
            "Highly detailed editorial illustration for a service featured image about {$finalTitle}, {$tone} tone, modern business aesthetic, cinematic lighting, ultra sharp, vivid colors.",
            "services/{$slug}-featured-" . time() . '.png',
            1280,
            720
        );

        return [
            'title' => $finalTitle,
            'shortDescription' => (string) ($payload['shortDescription'] ?? ''),
            'fullDescription' => (string) ($payload['fullDescription'] ?? ''),
            'icon' => $this->normalizeIcon((string) ($payload['icon'] ?? 'Globe')),
            'features' => $this->normalizeStringList($payload['features'] ?? []),
            'benefits' => $this->normalizeStringList($payload['benefits'] ?? []),
            'processSteps' => $this->normalizeProcessSteps($payload['processSteps'] ?? []),
            'metaTitle' => (string) ($payload['metaTitle'] ?? $finalTitle),
            'metaDescription' => (string) ($payload['metaDescription'] ?? ''),
            'imageUrl' => $imageUrl,
        ];
    }

    public function generateCaseStudy(array $input): array
    {
        $projectDescription = trim((string) ($input['projectDescription'] ?? ''));
        $clientName = trim((string) ($input['clientName'] ?? ''));
        $industry = trim((string) ($input['industry'] ?? ''));
        $goals = trim((string) ($input['goals'] ?? ''));
        $challenges = trim((string) ($input['challenges'] ?? ''));
        $tone = trim((string) ($input['tone'] ?? 'professional'));
        $technologies = $this->normalizeStringList($input['technologies'] ?? []);

        $prompt = <<<PROMPT
Generate JSON only for a professional case study page.

Project description: {$projectDescription}
Client name: {$clientName}
Industry: {$industry}
Project goals: {$goals}
Known challenges: {$challenges}
Preferred tone: {$tone}
Technologies: {$this->joinList($technologies)}

Return JSON with keys:
- title
- clientName
- industry
- duration
- year
- shortDescription (max 200 chars)
- challengeOverview
- challengePoints (array of 4 strings)
- solutionOverview
- solutionPoints (array of 4 strings)
- results (array of 3 objects: {value,label,description})
- processSteps (array of 4 objects: {title,description})
- technologies (array of strings)
- testimonialQuote
- testimonialAuthor
- testimonialRole
- metaTitle
- metaDescription
Do not include markdown fences or extra text.
PROMPT;

        $payload = $this->parseJson($this->textClient->generate($prompt));
        $title = (string) ($payload['title'] ?? 'Case Study');
        $slug = Str::slug($title);

        $imageUrl = $this->generateImage(
            "Highly detailed editorial illustration for case study featured image about {$title} in {$industry} industry, {$tone} tone, cinematic composition, ultra sharp, vivid colors.",
            "case-studies/{$slug}-featured-" . time() . '.png',
            1280,
            720
        );

        return [
            'title' => $title,
            'clientName' => (string) ($payload['clientName'] ?? $clientName),
            'industry' => (string) ($payload['industry'] ?? $industry),
            'duration' => (string) ($payload['duration'] ?? ''),
            'year' => (string) ($payload['year'] ?? date('Y')),
            'shortDescription' => (string) ($payload['shortDescription'] ?? ''),
            'challengeOverview' => (string) ($payload['challengeOverview'] ?? ''),
            'challengePoints' => $this->normalizeStringList($payload['challengePoints'] ?? []),
            'solutionOverview' => (string) ($payload['solutionOverview'] ?? ''),
            'solutionPoints' => $this->normalizeStringList($payload['solutionPoints'] ?? []),
            'results' => $this->normalizeResults($payload['results'] ?? []),
            'processSteps' => $this->normalizeCaseStudyProcessSteps($payload['processSteps'] ?? []),
            'technologies' => $this->normalizeStringList($payload['technologies'] ?? $technologies),
            'testimonialQuote' => (string) ($payload['testimonialQuote'] ?? ''),
            'testimonialAuthor' => (string) ($payload['testimonialAuthor'] ?? ''),
            'testimonialRole' => (string) ($payload['testimonialRole'] ?? ''),
            'metaTitle' => (string) ($payload['metaTitle'] ?? $title),
            'metaDescription' => (string) ($payload['metaDescription'] ?? ''),
            'featuredImageUrl' => $imageUrl,
        ];
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
            Log::error('AI image request failed for AI content generator.', [
                'endpoint' => $endpoint,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        if (!$response->ok()) {
            Log::error('AI image provider returned error for AI content generator.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Image generation failed: ' . $response->body());
        }

        Storage::disk('public')->put($path, $response->body());
        return Storage::url($path);
    }

    private function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => is_string($item) ? trim($item) : '',
            $value
        )));
    }

    private function normalizeProcessSteps(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];
        foreach ($value as $index => $step) {
            if (!is_array($step)) {
                continue;
            }
            $rows[] = [
                'step' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'title' => (string) ($step['title'] ?? ''),
                'description' => (string) ($step['description'] ?? ''),
            ];
        }

        return $rows;
    }

    private function normalizeCaseStudyProcessSteps(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $rows = [];
        foreach ($value as $step) {
            if (!is_array($step)) {
                continue;
            }
            $rows[] = [
                'title' => (string) ($step['title'] ?? ''),
                'description' => (string) ($step['description'] ?? ''),
            ];
        }
        return $rows;
    }

    private function normalizeResults(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $rows = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }
            $rows[] = [
                'value' => (string) ($item['value'] ?? ''),
                'label' => (string) ($item['label'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
            ];
        }
        return $rows;
    }

    private function normalizeIcon(string $icon): string
    {
        $allowed = ['Globe', 'Bot', 'Smartphone', 'Workflow', 'Palette', 'TrendingUp', 'Code', 'Shield', 'Zap'];
        foreach ($allowed as $value) {
            if (strcasecmp($value, trim($icon)) === 0) {
                return $value;
            }
        }
        return 'Globe';
    }

    private function joinList(array $items): string
    {
        return empty($items) ? 'None provided' : implode(', ', $items);
    }
}

