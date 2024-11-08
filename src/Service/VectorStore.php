<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LLPhant\Embeddings\Document as EmbeddingsDocument;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

/**
 * Class VectorStore.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class VectorStore extends VectorStoreBase
{
    private readonly DoctrineVectorStore $store;

    /**
     * @throws \Throwable
     */
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->store = new DoctrineVectorStore($this->em, entityClassName: Document::class);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[\Override]
    public function addDocument(EmbeddingsDocument $document): void
    {
        $this->store->addDocument($document);
    }

    /**
     * @param array<EmbeddingsDocument> $documents
     * @throws \Exception
     */
    #[\Override]
    public function addDocuments(array $documents): void
    {
        $this->store->addDocuments($documents);
    }

    /**
     * @param array<string, int|string> $additionalArguments
     */
    #[\Override]
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        return $this->store->similaritySearch($embedding, $k, $additionalArguments);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
