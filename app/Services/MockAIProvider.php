<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Models\AiKnowledgeSource;

class MockAIProvider implements AIProviderInterface
{
    public function searchKnowledge(string $query): array
    {
        $terms = collect(preg_split('/\s+/', mb_strtolower($query)))->filter(fn ($t) => mb_strlen($t) > 2);

        return AiKnowledgeSource::where('is_enabled', true)->get()->map(function ($source) use ($terms) {
            $hay = mb_strtolower($source->title.' '.$source->content);
            $score = $terms->sum(fn ($term) => substr_count($hay, $term));

            return ['score' => $score, 'source' => $source];
        })->filter(fn ($r) => $r['score'] > 0)->sortByDesc('score')->take(3)->values()->all();
    }

    public function chat(string $message): string
    {
        $matches = $this->searchKnowledge($message);
        if (! $matches) {
            return 'Informasi tersebut belum tersedia pada basis pengetahuan resmi kami. Silakan hubungi petugas melalui halaman Kontak atau kanal Pengaduan.';
        }

        return $matches[0]['source']->content.' Sumber: '.$matches[0]['source']->title.'.';
    }

    public function embed(string $content): array
    {
        return [];
    }

    public function healthCheck(): bool
    {
        return true;
    }
}
