<?php

declare(strict_types=1);

namespace App\Service\Google;

use GeminiAPI\Client;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class GoogleEmbeddingGenerator.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class GoogleEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    private Client $client;

    public function __construct(GoogleConfig $config)
    {
        $this->client = new Client($config->apiKey);
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedText(string $text): array
    {
        $response = $this->client->embeddingModel(ModelName::Embedding)
            ->embedContent(new TextPart($text));

        return $response->embedding->values;
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedDocument(Document $document): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text);

        return $document;
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function embedDocuments(array $documents): array
    {
        $embedDocuments = [];
        foreach ($documents as $document) {
            $embedDocuments[] = $this->embedDocument($document);
        }

        return $embedDocuments;
    }

    #[\Override]
    public function getEmbeddingLength(): int
    {
        return 768;
    }
}
