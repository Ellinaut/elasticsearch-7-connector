<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;
use Ellinaut\ElasticsearchConnector\Document\DocumentMigratorInterface;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface IndexManagerInterface
{
    /**
     * @param string $externalIndexName
     * @param Client $connection
     */
    public function createIndex(string $externalIndexName, Client $connection): void;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     * @param DocumentMigratorInterface|null $documentMigrator
     */
    public function updateIndex(
        string $externalIndexName,
        Client $connection,
        ?DocumentMigratorInterface $documentMigrator = null
    ): void;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     */
    public function deleteIndex(string $externalIndexName, Client $connection): void;
}
