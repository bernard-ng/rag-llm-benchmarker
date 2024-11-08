<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CitationIndexRepository;
use App\Service\Config;
use App\Service\EmbeddingGenerator;
use App\Service\Google\GoogleChat;
use App\Service\Google\GoogleConfig;
use App\Service\Model;
use App\Service\Provider;
use App\Service\QuestionAnswering;
use App\Service\VectorStore;
use LLPhant\Chat\MistralAIChat;
use LLPhant\Chat\OllamaChat;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class CompletionController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
class CompletionController extends AbstractController
{
    use ConfigSelectionTrait;

    public function __construct(
        private readonly VectorStore $vectorStore,
        private readonly CitationIndexRepository $citationIndexRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/generate', name: 'generate', methods: ['POST'])]
    #[Route('/api/chats/completion', name: 'completion', methods: ['POST'])]
    public function completion(Request $request): JsonResponse
    {
        [$provider, $model, $useKnowledgeBase] = $this->selectModel($request);
        $rag = $this->initializeRagSystem($provider, $model);
        $question = $request->getPayload()->getString('prompt');
        if ($question === '') {
            throw new BadRequestHttpException('prompt must not be empty !');
        }

        $watch = new Stopwatch(true);
        $watch->start('completion');

        $augmentedContext = $rag->retrieveRelatedDocuments($question, 4, [
            'useKnowledgeBase' => $useKnowledgeBase,
        ]);
        $response = $rag->generateAnswer($question, $augmentedContext);
        $benchmark = $watch->stop('completion');

        return $this->json([
            'response' => $response,
            'model' => $model,
            'provider' => $provider,
            'created_at' => (new \DateTimeImmutable())->format('U'),
            'benchmark' => [
                'duration' => $benchmark->getDuration(),
                'memory' => $benchmark->getMemory(),
            ],
            'done' => true,
        ]);
    }

    /**
     * @throws \Exception
     */
    private function initializeRagSystem(Provider $provider, Model $model): QuestionAnswering
    {
        $embeddingGenerator = new EmbeddingGenerator(Provider::MISTRAL);
        $config = Config::get($provider, $model);

        switch ($provider) {
            case Provider::MISTRAL:
                /** @var OpenAIConfig|null $config */
                $chat = new MistralAIChat($config);
                break;
            case Provider::OPENAI:
                /** @var OpenAIConfig|null $config */
                $chat = new OpenAIChat($config);
                break;
            case Provider::OLLAMA:
                /** @var OllamaConfig $config */
                $chat = new OllamaChat($config);
                break;
            case Provider::GOOGLE:
                /** @var GoogleConfig $config */
                $chat = new GoogleChat($config);
                break;
            default:
                throw new BadRequestHttpException('invalid provider');
        }

        return new QuestionAnswering(
            $this->vectorStore,
            $embeddingGenerator,
            $chat,
            $this->logger,
            $this->citationIndexRepository
        );
    }
}
