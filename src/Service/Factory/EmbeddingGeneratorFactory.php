<?php

declare(strict_types=1);

namespace App\Service\Factory;

use App\Service\Google\GoogleConfig;
use App\Service\Google\GoogleEmbeddingGenerator;
use App\Service\Provider;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\EmbeddingGenerator\Mistral\MistralEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3LargeEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAIADA002EmbeddingGenerator;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;

/**
 * Class EmbeddingGeneratorFactory.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract readonly class EmbeddingGeneratorFactory
{
    public static function create(
        Provider $provider,
        OpenAIConfig|OllamaConfig|GoogleConfig $config
    ): EmbeddingGeneratorInterface {
        return match ($provider) {
            Provider::MISTRAL => new MistralEmbeddingGenerator($config),
            Provider::OLLAMA => new OllamaEmbeddingGenerator($config),
            Provider::OPENAI => match ($config->model) {
                'text-embedding-3-small' => new OpenAI3SmallEmbeddingGenerator($config),
                'text-embedding-3-large' => new OpenAI3LargeEmbeddingGenerator($config),
                'text-embedding-ada-002' => new OpenAIADA002EmbeddingGenerator($config),
                default => throw new \InvalidArgumentException('invalid or unsupported model')
            },
            Provider::GOOGLE => new GoogleEmbeddingGenerator($config),
        };
    }
}
