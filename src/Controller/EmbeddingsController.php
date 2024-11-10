<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\EmbeddingsRequest;
use App\Service\EmbeddingGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class EmbeddingsController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class EmbeddingsController extends AbstractController
{
    #[Route('/v1/embeddings', name: 'embeddings', methods: ['POST'])]
    public function embeddings(
        #[MapRequestPayload(acceptFormat: 'json')]
        EmbeddingsRequest $request
    ): JsonResponse {
        $watch = new Stopwatch(true);
        $watch->start('embeddings');

        $embeddingGenerator = new EmbeddingGenerator(
            $request->embeddingsModel->provider,
            $request->embeddingsModel->model
        );
        $embeddings = $embeddingGenerator->embedText($request->text);
        $benchmark = $watch->stop('embeddings');

        return $this->json([
            'embeddings' => $embeddings,
            'createdAt' => (new \DateTimeImmutable())->format('U'),
            'benchmark' => [
                'duration' => $benchmark->getDuration(),
                'memory' => $benchmark->getMemory(),
            ],
            'embeddingsModel' => [
                'provider' => $request->embeddingsModel->provider->value,
                'model' => $request->embeddingsModel->model,
            ],
        ]);
    }
}
