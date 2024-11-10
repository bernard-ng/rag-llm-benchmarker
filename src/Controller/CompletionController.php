<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\CompletionRequest;
use App\Service\Factory\QuestionAnsweringFactory;
use App\Service\VectorStore;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class CompletionController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
class CompletionController extends AbstractController
{
    public function __construct(
        private readonly VectorStore $vectorStore,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/v1/generate', name: 'generate', methods: ['POST'])]
    #[Route('/v1/chats/completion', name: 'completion', methods: ['POST'])]
    #[Route('/v1/completion', name: 'completion_legacy', methods: ['POST'])]
    public function completion(
        #[MapRequestPayload(acceptFormat: 'json')]
        CompletionRequest $request
    ): JsonResponse {
        $question = $request->prompt;
        $qa = QuestionAnsweringFactory::create(
            $request->embeddingsModel,
            $request->generativeModel,
            $this->vectorStore,
            $this->logger
        );

        $watch = new Stopwatch(true);
        $watch->start('completion');

        $augmentedContext = $qa->retrieveRelatedDocuments($question, 4, [
            'useContext' => $request->useContext,
        ]);
        $response = $qa->generateAnswer($question, $augmentedContext);
        $benchmark = $watch->stop('completion');

        return $this->json([
            'response' => $response,
            'createdAt' => (new \DateTimeImmutable())->format('U'),
            'benchmark' => [
                'duration' => $benchmark->getDuration(),
                'memory' => $benchmark->getMemory(),
            ],
            'generativeModel' => [
                'provider' => $request->generativeModel->provider->value,
                'model' => $request->generativeModel->model,
            ],
            'embeddingsModel' => [
                'provider' => $request->embeddingsModel->provider->value,
                'model' => $request->embeddingsModel->model,
            ],
        ]);
    }
}
