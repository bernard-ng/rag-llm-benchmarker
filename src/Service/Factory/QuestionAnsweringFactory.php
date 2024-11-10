<?php

declare(strict_types=1);

namespace App\Service\Factory;

use App\Request\Config;
use App\Service\Chat;
use App\Service\EmbeddingGenerator;
use App\Service\QuestionAnswering;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Psr\Log\LoggerInterface;

/**
 * Class QuestionAnsweringFactory.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract class QuestionAnsweringFactory
{
    public static function create(
        Config $embeddingsModel,
        Config $generativeModel,
        VectorStoreBase $vectorStore,
        LoggerInterface $logger
    ) {
        return new QuestionAnswering(
            $vectorStore,
            new EmbeddingGenerator($embeddingsModel->provider, $embeddingsModel->model),
            new Chat($generativeModel->provider, $generativeModel->model),
            $logger,
        );
    }
}
