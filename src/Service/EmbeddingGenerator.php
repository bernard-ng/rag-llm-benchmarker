<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Google\GoogleConfig;
use App\Service\Google\GoogleEmbeddingGenerator;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\EmbeddingGenerator\Mistral\MistralEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;

/**
 * Class EmbeddingGenerator.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class EmbeddingGenerator implements EmbeddingGeneratorInterface
{
    private EmbeddingGeneratorInterface $embeddingGenerator;

    /**
     * @throws \Exception
     */
    public function __construct(Provider $provider = Provider::MISTRAL)
    {
        $config = Config::get($provider);

        switch ($provider) {
            case Provider::MISTRAL:
                /** @var OpenAIConfig|null $config */
                $this->embeddingGenerator = new MistralEmbeddingGenerator($config);
                break;
            case Provider::OLLAMA:
                /** @var OllamaConfig $config */
                $this->embeddingGenerator = new OllamaEmbeddingGenerator($config);
                break;
            case Provider::OPENAI:
                /** @var OpenAIConfig|null $config */
                $this->embeddingGenerator = new OpenAI3SmallEmbeddingGenerator($config);
                break;
            case Provider::GOOGLE:
                /** @var GoogleConfig $config */
                $this->embeddingGenerator = new GoogleEmbeddingGenerator($config);
                break;
            default:
                throw new InvalidArgumentException('invalid provider or model');
        }
    }

    /**
     * @throws \JsonException
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedText(string $text): array
    {
        return $this->embeddingGenerator->embedText($text);
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedDocument(Document $document): Document
    {
        return $this->embeddingGenerator->embedDocument($document);
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedDocuments(array $documents): array
    {
        return $this->embeddingGenerator->embedDocuments($documents);
    }

    #[\Override]
    public function getEmbeddingLength(): int
    {
        return $this->embeddingGenerator->getEmbeddingLength();
    }
}
