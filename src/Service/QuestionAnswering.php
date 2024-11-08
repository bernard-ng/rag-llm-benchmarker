<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CitationIndexRepository;
use LLPhant\Chat\ChatInterface;
use LLPhant\Query\SemanticSearch\QuestionAnswering as LLPhantQuestionAnswering;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

/**
 * Class QuestionAnswering.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[WithMonologChannel('app')]
final class QuestionAnswering extends LLPhantQuestionAnswering
{
    /**
     * @throws \Exception
     */
    public function __construct(
        VectorStore $vectorStoreBase,
        EmbeddingGenerator $embeddingGenerator,
        ChatInterface $chat,
        public readonly LoggerInterface $logger,
        public readonly CitationIndexRepository $citationIndexRepository,
        private array $retrievedDocuments = []
    ) {
        parent::__construct($vectorStoreBase, $embeddingGenerator, $chat);
    }

    /**
     * @return array{prompt: string, sources: string}
     */
    public function retrieveRelatedDocuments(string $question, int $k, array $additionalArguments): array
    {
        $useKnowledgeBase = $additionalArguments['useKnowledgeBase'] ?? true;
        if ($useKnowledgeBase === false) {
            return [
                'prompt' => SystemPrompt::format(useKnowledgeBase: false),
                'sources' => '',
            ];
        }

        $this->retrievedDocuments = $this->similaritySearch($question, $k, $additionalArguments);
        if ($this->retrievedDocuments === []) {
            $this->logger->debug('[LLM] unable to build augmented context', [
                'question' => $question,
            ]);

            return [
                'prompt' => SystemPrompt::format(),
                'sources' => "\nAucune source trouvée relative à la question",
            ];
        }

        $context = $this->getAugmentedContext($this->retrievedDocuments);
        $sources = $this->getCitationSources($this->retrievedDocuments);
        $this->logger->debug('[LLM] augmented context', [
            'question' => $question,
            'documents' => $this->retrievedDocuments,
            'context' => $context,
            'sources' => $sources,
        ]);

        return [
            'prompt' => SystemPrompt::format($context),
            'sources' => empty($sources) ? '' : "\nSources consultées : \n{$sources}",
        ];
    }

    public function generateAnswer(string $question, array $context): string
    {
        $this->chat->setSystemMessage($context['prompt']);

        $response = $this->chat->generateText($question);
        $response .= $context['sources'];

        return $response;
    }

    private function getAugmentedContext(array $retrievedDocuments): string
    {
        $context = '';
        foreach ($this->retrievedDocuments as $document) {
            $context .= $document->content . ' ';
        }

        return $context;
    }

    private function getCitationSources(array $retrievedDocuments): string
    {
        $sources = '';
        foreach ($this->retrievedDocuments as $document) {
            $id = preg_replace('/(-?\d+)?(.txt|.pdf)/', '', $document->sourceName);
            $ids = [];

            try {
                $results = $this->citationIndexRepository->find($id);
                if ($results['found'] > 0) {
                    $source = $results['hits'][0]['document'];

                    if (! in_array($id, $ids)) {
                        $title = str_replace('content/Drive/MyDrive/DATA/PDF', '', $source['title']);
                        $sources .= "- {$title}\n";
                        $this->logger->debug('[CitationIndex] source found', [
                            'hash' => $source['hash'],
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
            }
        }

        return $sources;
    }

    private function similaritySearch(string $question, int $k, array $additionalArguments): array
    {
        $embeddings = $this->embeddingGenerator->embedText($question);
        return $this->vectorStoreBase->similaritySearch($embeddings, $k, $additionalArguments);
    }
}
