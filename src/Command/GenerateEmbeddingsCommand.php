<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Service\EmbeddingGenerator;
use App\Service\FileDataReader;
use App\Service\Provider;
use App\Service\VectorStore;
use LLPhant\Embeddings\Document as LLPhantDocument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(
    name: 'app:document:generate-embeddings',
    description: 'Create an embeddings file from a text file.',
)]
class GenerateEmbeddingsCommand extends Command
{
    private const string BENCHMARK_ID = 'generation';

    public function __construct(
        #[Autowire('%app_splits_dataset_path%')]
        private readonly string $splitDatasetPath,
        private readonly VectorStore $vectorStore,
        private readonly DocumentRepository $documentRepository,
        private readonly EmbeddingGenerator $embeddingGenerator = new EmbeddingGenerator(Provider::MISTRAL),
    ) {
        parent::__construct();
    }

    #[\Override]
    public function configure(): void
    {
        parent::configure();
        $this->addOption('resume', null, InputOption::VALUE_OPTIONAL, 'resume from last document');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text("Reading documents from : {$this->splitDatasetPath}");
        $stopwatch = new Stopwatch(true);
        $stopwatch->start(self::BENCHMARK_ID);

        // iterable file data reader, custom reimplementation
        $reader = new FileDataReader($this->splitDatasetPath, Document::class);
        $documents = $reader->getDocuments();
        $this->createEmbeddings($io, $documents, $input->getOption('resume'), $input->getOption('locking'));

        $stopwatch->stop(self::BENCHMARK_ID);
        $io->text($stopwatch->getEvent(self::BENCHMARK_ID)->__toString());
        $io->success('Done');

        return Command::SUCCESS;
    }

    /**
     * @param array<Document|LLPhantDocument> $documents
     */
    private function createEmbeddings(SymfonyStyle $io, iterable $documents, ?int $resume = null, bool $enableLocking = false): void
    {
        $io->text('Generating embeddings for documents');

        try {
            /**
             * @var int $id
             * @var Document $document
             */
            foreach ($documents as $id => $document) {
                if ($resume !== null && $id < $resume) {
                    $io->text("[RESUME SKIP] {$id} skipped");
                    continue;
                }

                try {
                    $embeddedDocument = $this->documentRepository->findOneBy([
                        'content' => $document->content,
                    ]);
                    if ($embeddedDocument instanceof Document) {
                        $io->text("[SKIP] Document #{$embeddedDocument->getId()} ({$document->getSourceName()})");
                        continue;
                    }
                    $this->vectorStore->addDocument($this->embeddingGenerator->embedDocument($document));
                    $io->text("[SUCCESS] Document {$document->getSourceName()}");

                    if ($id % 500 === 0) {
                        $this->vectorStore->getEntityManager()->clear();
                    }

                    usleep(200); // rate limit

                } catch (\Throwable $e) {
                    $io->text("[FAIL] {$document->getSourceName()}");
                    $io->error($e->getMessage());
                    continue;
                }
            }
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return;
        }
    }
}
