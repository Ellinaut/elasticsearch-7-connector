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
     * @param callable|null $responseHandler
     */
    public function createIndex(string $externalIndexName, Client $connection, ?callable $responseHandler = null): void;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     * @param DocumentMigratorInterface|null $documentMigrator
     * @param callable|null $responseHandler
     */
    public function updateIndex(
        string $externalIndexName,
        Client $connection,
        ?DocumentMigratorInterface $documentMigrator = null,
        ?callable $responseHandler = null
    ): void;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     * @param callable|null $responseHandler
     */
    public function deleteIndex(string $externalIndexName, Client $connection, ?callable $responseHandler = null): void;
}
