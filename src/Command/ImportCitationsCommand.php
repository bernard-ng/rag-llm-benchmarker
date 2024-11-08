<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CitationIndexRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:import:citations')]
final class ImportCitationsCommand extends Command
{
    public function __construct(
        #[Autowire(value: '%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly CitationIndexRepository $repository
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $filepath = sprintf('%s/dataset/document.jsonl', $this->projectDir);

            $this->repository->createIndex();
            $this->repository->import($filepath);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
