<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Models\News;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImportLegacyWordPress extends Command
{
    protected $signature = 'legacy:import {--url=https://lightgreen-whale-188146.hostingersite.com} {--skip-media}';

    protected $description = 'Import pages, posts, and media from the former WordPress website';

    private array $mediaUrls = [];

    public function handle(): int
    {
        $origin = rtrim((string) $this->option('url'), '/');
        $api = $origin.'/wp-json/wp/v2';
        $client = Http::retry(3, 750)->timeout(45)->acceptJson();

        $this->info("Importing legacy content from {$origin}");

        if (! $this->option('skip-media')) {
            $this->importMedia($client, $api);
        } else {
            $this->mediaUrls = MediaAsset::whereNotNull('source_url')->pluck('file_path', 'source_url')->all();
        }

        $this->importPosts($client, $api);
        $this->importPages($client, $api);

        $this->newLine();
        $this->table(['Content', 'Imported'], [
            ['News', News::whereNotNull('legacy_id')->count()],
            ['Pages', Page::whereNotNull('legacy_id')->count()],
            ['Media', MediaAsset::whereNotNull('legacy_id')->count()],
        ]);

        return self::SUCCESS;
    }

    private function importMedia(PendingRequest $client, string $api): void
    {
        $media = $this->fetchAll($client, $api.'/media', ['orderby' => 'id', 'order' => 'asc']);
        $bar = $this->output->createProgressBar(count($media));
        $bar->setFormat(' Media %current%/%max% [%bar%] %percent:3s%%');

        foreach (array_chunk($media, 12) as $chunk) {
            $downloads = [];
            foreach ($chunk as $item) {
                $source = (string) data_get($item, 'source_url');
                $asset = MediaAsset::where('legacy_id', data_get($item, 'id'))->first();
                $existingPath = $this->existingMediaPath((string) data_get($item, 'id'));
                if ($source !== '' && (! $asset || ! Storage::disk('public')->exists($asset->file_path)) && ! $existingPath) {
                    $downloads[(string) data_get($item, 'id')] = $source;
                }
            }

            $responses = Http::pool(fn (Pool $pool) => collect($downloads)->map(
                fn (string $source, $id) => $pool->as((string) $id)->timeout(90)->get($source)
            )->all());

            foreach ($chunk as $item) {
                try {
                    $id = (string) data_get($item, 'id');
                    $source = (string) data_get($item, 'source_url');
                    if ($source === '') {
                        $bar->advance();

                        continue;
                    }

                    $asset = MediaAsset::where('legacy_id', $id)->first();
                    $path = $asset?->file_path ?: $this->existingMediaPath($id);
                    if (! $path || ! Storage::disk('public')->exists($path)) {
                        $response = $responses[$id];
                        $response->throw();
                        $extension = $this->safeExtension($source, $response->header('Content-Type'));
                        $path = 'legacy-media/'.$id.'-'.Str::slug(pathinfo(parse_url($source, PHP_URL_PATH), PATHINFO_FILENAME)).'.'.$extension;
                        Storage::disk('public')->put($path, $response->body());
                    }

                    $asset = MediaAsset::updateOrCreate(['legacy_id' => $id], [
                        'title' => $this->plain(data_get($item, 'title.rendered')),
                        'caption' => $this->plain(data_get($item, 'caption.rendered')),
                        'alt_text' => $this->plain(data_get($item, 'alt_text')),
                        'mime_type' => data_get($item, 'mime_type'),
                        'file_path' => $path,
                        'source_url' => $source,
                        'file_size' => Storage::disk('public')->size($path),
                        'width' => data_get($item, 'media_details.width'),
                        'height' => data_get($item, 'media_details.height'),
                        'published_at' => data_get($item, 'date'),
                    ]);

                    $this->mediaUrls[$source] = $asset->file_path;
                    foreach ((array) data_get($item, 'media_details.sizes', []) as $size) {
                        if ($sizeUrl = data_get($size, 'source_url')) {
                            $this->mediaUrls[$sizeUrl] = $asset->file_path;
                        }
                    }
                } catch (Throwable $exception) {
                    $this->newLine();
                    $this->warn('Skipped media '.$id.': '.$exception->getMessage());
                }
                $bar->advance();
            }
        }
        $bar->finish();
        $this->newLine();
    }

    private function importPosts(PendingRequest $client, string $api): void
    {
        $posts = $this->fetchAll($client, $api.'/posts', ['_embed' => 1, 'status' => 'publish']);
        foreach ($posts as $post) {
            $image = $this->localMediaUrl((int) data_get($post, 'featured_media'));
            News::updateOrCreate(['legacy_id' => data_get($post, 'id')], [
                'title' => $this->plain(data_get($post, 'title.rendered')),
                'slug' => data_get($post, 'slug'),
                'category' => $this->plain(data_get($post, '_embedded.wp:term.0.0.name', 'Berita')),
                'excerpt' => Str::limit($this->plain(data_get($post, 'excerpt.rendered')), 450),
                'content' => $this->cleanHtml(data_get($post, 'content.rendered')),
                'thumbnail' => $image,
                'author' => $this->plain(data_get($post, '_embedded.author.0.name', 'Dinas PUPR Kota Prabumulih')),
                'status' => 'PUBLISHED',
                'published_at' => data_get($post, 'date'),
                'source_url' => data_get($post, 'link'),
            ]);
        }
        $this->info(' Imported '.count($posts).' articles.');
    }

    private function importPages(PendingRequest $client, string $api): void
    {
        $pages = $this->fetchAll($client, $api.'/pages', ['_embed' => 1, 'status' => 'publish']);
        foreach ($pages as $page) {
            Page::updateOrCreate(['legacy_id' => data_get($page, 'id')], [
                'title' => $this->plain(data_get($page, 'title.rendered')),
                'slug' => data_get($page, 'slug'),
                'content' => $this->cleanHtml(data_get($page, 'content.rendered')),
                'excerpt' => Str::limit($this->plain(data_get($page, 'excerpt.rendered')), 450),
                'featured_image' => $this->localMediaUrl((int) data_get($page, 'featured_media')),
                'source_url' => data_get($page, 'link'),
                'status' => 'PUBLISHED',
                'published_at' => data_get($page, 'date'),
            ]);
        }
        $this->info(' Imported '.count($pages).' pages.');
    }

    private function fetchAll(PendingRequest $client, string $endpoint, array $query = []): array
    {
        $results = [];
        $page = 1;
        do {
            $response = $client->get($endpoint, [...$query, 'per_page' => 100, 'page' => $page]);
            $response->throw();
            $batch = $response->json();
            $results = [...$results, ...$batch];
            $pages = (int) $response->header('X-WP-TotalPages', 1);
            $page++;
        } while ($page <= $pages);

        return $results;
    }

    private function localMediaUrl(int $legacyId): ?string
    {
        if (! $legacyId) {
            return null;
        }
        $asset = MediaAsset::where('legacy_id', $legacyId)->first();

        return $asset ? '/storage/'.$asset->file_path : null;
    }

    private function cleanHtml(?string $html): string
    {
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('#<(script|style|iframe)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';
        foreach ($this->mediaUrls as $remote => $local) {
            $html = str_replace($remote, '/storage/'.$local, $html);
        }

        return strip_tags($html, '<p><br><h2><h3><h4><h5><ul><ol><li><strong><em><blockquote><figure><figcaption><img><a><div><span><table><thead><tbody><tr><th><td>');
    }

    private function plain(?string $value): string
    {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function safeExtension(string $url, ?string $mime): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf', 'mp4', 'webm'];
        if (in_array($extension, $allowed, true)) {
            return $extension;
        }

        return match (strtolower((string) $mime)) {
            'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
            'image/svg+xml' => 'svg', 'application/pdf' => 'pdf', 'video/mp4' => 'mp4',
            default => 'jpg',
        };
    }

    private function existingMediaPath(string $legacyId): ?string
    {
        return collect(Storage::disk('public')->files('legacy-media'))
            ->first(fn (string $path) => str_starts_with(basename($path), $legacyId.'-'));
    }
}
