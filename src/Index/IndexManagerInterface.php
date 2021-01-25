<?php

namespace Ellinaut\Index;

use Elasticsearch\Client;
use Ellinaut\Document\DocumentManagerInterface;

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
     * @param DocumentManagerInterface $documentManager
     */
    public function updateIndex(
        string $externalIndexName,
        Client $connection,
        DocumentManagerInterface $documentManager
    ): void;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     */
    public function deleteIndex(string $externalIndexName, Client $connection): void;
}
