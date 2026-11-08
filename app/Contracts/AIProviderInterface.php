<?php

namespace App\Contracts;

interface AIProviderInterface
{
    public function chat(string $message): string;

    public function searchKnowledge(string $query): array;

    public function embed(string $content): array;

    public function healthCheck(): bool;
}
