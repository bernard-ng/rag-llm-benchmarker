<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EmbeddingGenerator;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class EmbeddingsController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class EmbeddingsController extends AbstractController
{
    use ConfigSelectionTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws \Exception
     */
    #[Route('api/embeddings', name: 'embeddings', methods: ['POST'])]
    public function embeddings(Request $request): JsonResponse
    {
        [$provider, $model, $context] = $this->selectModel($request);
        $text = $request->getPayload()->getString('text');
        if ($text === '') {
            throw new BadRequestHttpException('text cannot be empty');
        }

        $watch = new Stopwatch(true);
        $watch->start('embeddings');

        $embeddingGenerator = new EmbeddingGenerator($provider);
        $embeddings = $embeddingGenerator->embedText($text);
        $benchmark = $watch->stop('embeddings');

        return $this->json([
            'provider' => $provider->value,
            'embeddings' => $embeddings,
            'created_at' => (new \DateTimeImmutable())->format('U'),
            'benchmark' => [
                'duration' => $benchmark->getDuration(),
                'memory' => $benchmark->getMemory(),
            ],
            'done' => true,
        ]);
    }
}
