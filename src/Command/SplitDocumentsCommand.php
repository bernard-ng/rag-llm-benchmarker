<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Document;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(
    name: 'app:document:split',
    description: 'split documents into smaller parts.',
)]
class SplitDocumentsCommand extends Command
{
    private const string BENCHMARK_ID = 'splitting';

    public function __construct(
        #[Autowire('%app_texts_dataset_path%')]
        private readonly string $textsDatasetPath,
        #[Autowire('%app_splits_dataset_path%')]
        private readonly string $splitsDatasetPath,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text("Reading documents from : {$this->textsDatasetPath}");
        $stopwatch = new Stopwatch(true);
        $stopwatch->start(self::BENCHMARK_ID);

        // LLPhant data reader
        $reader = new FileDataReader($this->textsDatasetPath, Document::class);
        $documents = $reader->getDocuments();
        $io->text(sprintf('Found %s documents', count($documents)));

        // create splits directory if it does not exist, usefully for the first run
        if (! is_dir($this->splitsDatasetPath)) {
            $this->filesystem->mkdir($this->splitsDatasetPath);
        }

        foreach ($documents as $k => $document) {
            $filename = pathinfo($document->sourceName, PATHINFO_FILENAME);
            $splits = DocumentSplitter::splitDocument($document, 500);
            $io->text(sprintf('Document #%d => #%d splits', $k, count($splits)));

            foreach ($splits as $i => $split) {
                $this->filesystem->dumpFile("{$this->splitsDatasetPath}/{$filename}-{$i}.txt", $split->content);
            }
        }

        $stopwatch->stop(self::BENCHMARK_ID);
        $io->text($stopwatch->getEvent(self::BENCHMARK_ID)->__toString());
        $io->success('Done');

        return Command::SUCCESS;
    }
}
