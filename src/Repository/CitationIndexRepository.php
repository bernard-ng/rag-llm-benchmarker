<?php

declare(strict_types=1);

namespace App\Repository;

use Http\Client\Exception;
use Symfony\Component\HttpClient\HttplugClient;
use Typesense\Client;
use Typesense\Exceptions\ConfigError;
use Typesense\Exceptions\TypesenseClientError;

/**
 * Class CitationIndexRepository.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class CitationIndexRepository
{
    private Client $client;

    /**
     * @throws ConfigError
     */
    public function __construct()
    {
        $this->client = new Client(
            [
                'api_key' => $_ENV['TYPESENSE_API_KEY'],
                'nodes' => [
                    [
                        'host' => $_ENV['TYPESENSE_SERVER_URL'],
                        'port' => '8108',
                        'protocol' => 'http',
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

    /**
     * @throws Exception
     * @throws TypesenseClientError
     */
    public function find(string $id): array
    {
        return $this->client->collections['documents']->documents->search([
            'q' => $id,
            'query_by' => 'hash',
            'sort_by' => 'created_at:desc',
        ]);
    }

    /**
     * @throws Exception
     * @throws TypesenseClientError|\JsonException
     */
    public function import(string $filepath): void
    {
        /** @var string $documents */
        $documents = file_get_contents($filepath);

        $this->client->collections['documents']->documents->import($documents, [
            'action' => 'create',
        ]);
    }

    public function createIndex(): void
    {
        $this->client->collections->create([
            'name' => 'documents',
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'facet' => false,
                ],
                [
                    'name' => 'original_filename',
                    'type' => 'string',
                    'facet' => false,
                ],
                [
                    'name' => 'content',
                    'type' => 'string',
                    'facet' => false,
                ],
                [
                    'name' => 'hash',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'source_url',
                    'type' => 'string',
                    'facet' => false,
                ],
                [
                    'name' => 'created_at',
                    'type' => 'int32',
                    'facet' => false,
                ],
                [
                    'name' => 'reviewed',
                    'type' => 'bool',
                    'facet' => false,
                ],
                [
                    'name' => 'keywords',
                    'type' => 'string[]',
                    'facet' => false,
                ],
            ],
            'default_sorting_field' => 'created_at',
        ]);
    }
}
