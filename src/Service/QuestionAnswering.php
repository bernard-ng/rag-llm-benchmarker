<?php

declare(strict_types=1);

namespace App\Service;

use LLPhant\Chat\ChatInterface;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use LLPhant\Query\SemanticSearch\QuestionAnswering as LLPhantQuestionAnswering;
use Psr\Log\LoggerInterface;

/**
 * Class QuestionAnswering.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class QuestionAnswering extends LLPhantQuestionAnswering
{
    /**
     * @throws \Exception
     */
    public function __construct(
        VectorStoreBase $vectorStoreBase,
        EmbeddingGeneratorInterface $embeddingGenerator,
        ChatInterface $chat,
        public readonly LoggerInterface $logger,
        private array $retrievedDocuments = []
    ) {
        parent::__construct($vectorStoreBase, $embeddingGenerator, $chat);
    }

    public function retrieveRelatedDocuments(string $question, int $k, array $additionalArguments): string
    {
        $useContext = $additionalArguments['useContext'] ?? true;
        if ($useContext === false) {
            return SystemPrompt::format(useContext: false);
        }

        $this->retrievedDocuments = $this->similaritySearch($question, $k, $additionalArguments);
        if ($this->retrievedDocuments === []) {
            $this->logger->debug('[LLM] unable to build augmented context', [
                'question' => $question,
            ]);

            return SystemPrompt::format();
        }

        $context = $this->getAugmentedContext($this->retrievedDocuments);
        $this->logger->debug('[LLM] augmented context', [
            'question' => $question,
            'documents' => $this->retrievedDocuments,
            'context' => $context,
        ]);

        return SystemPrompt::format($context);
    }

    public function generateAnswer(string $question, string $context): string
    {
        $this->chat->setSystemMessage($context);

        return $this->chat->generateText($question);
    }

    private function getAugmentedContext(array $retrievedDocuments): string
    {
        $context = '';
        foreach ($this->retrievedDocuments as $document) {
            $context .= $document->content . ' ';
        }

        return $context;
    }

    private function similaritySearch(string $question, int $k, array $additionalArguments): array
    {
        $embeddings = $this->embeddingGenerator->embedText($question);
        return $this->vectorStoreBase->similaritySearch($embeddings, $k, $additionalArguments);
    }
}
