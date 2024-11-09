<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Class Provider.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
enum Provider: string
{
    case OPENAI = 'openai';
    case MISTRAL = 'mistral';
    case OLLAMA = 'ollama';
    case GOOGLE = 'google';

    public function isSupportedModel(string $model): bool
    {
        return in_array($model, $this->getSupportedModelNames(), true);
    }

    public function getSupportedModelNames(): array
    {
        return match ($this) {
            self::OPENAI => [
                'gpt-4',
                'gpt-4o',
                'gpt-4-turbo',
                'gpt-3.5-turbo',
                'gpt-4o-mini',
                'chatgpt-4o-latest',
                'text-embedding-3-large',
                'text-embedding-3-small',
                'text-embedding-ada-002',
            ],
            self::MISTRAL => [
                'mistral-embed',
                'mistral-large-latest',
                'mistral-small-latest',
                'mistral-medium-latest',
            ],
            self::OLLAMA => [
                'vicuna',
                'lama2',
                'mistral',
            ],
            self::GOOGLE => [
                'text-bison-001',
                'gemini-pro',
                'gemini-1.0-pro',
                'gemini-1.0-pro-latest',
                'gemini-1.5-pro',
                'gemini-1.5-flash',
                'gemini-pro-vision',
                'embedding-001',
                'aqa',
            ],
        };
    }
}
