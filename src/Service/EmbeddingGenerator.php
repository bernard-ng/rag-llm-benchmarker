<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Factory\ConfigFactory;
use App\Service\Factory\EmbeddingGeneratorFactory;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;

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
    public function __construct(
        Provider $provider = Provider::MISTRAL,
        string $model = 'mistral-embed'
    ) {
        $config = ConfigFactory::create($provider, $model);
        $this->embeddingGenerator = EmbeddingGeneratorFactory::create($provider, $config);
    }

    #[\Override]
    public function embedText(string $text): array
    {
        return $this->embeddingGenerator->embedText($text);
    }

    #[\Override]
    public function embedDocument(Document $document): Document
    {
        return $this->embeddingGenerator->embedDocument($document);
    }

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
