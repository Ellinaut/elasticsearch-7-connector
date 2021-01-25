<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;
use Ellinaut\ElasticsearchConnector\Document\DocumentMigratorInterface;
use Ellinaut\ElasticsearchConnector\Exception\IndexAlreadyExistException;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
abstract class AbstractIndexManager implements IndexManagerInterface
{
    /**
     * @return array
     */
    abstract protected function getIndexDefinition(): array;

    /**
     * @param string $externalIndexName
     * @param Client $connection
     * @throws IndexAlreadyExistException
     */
    public function createIndex(string $externalIndexName, Client $connection): void
    {
        if ($connection->indices()->exists(['index' => $externalIndexName])) {
            throw new IndexAlreadyExistException($externalIndexName);
        }

        $connection->indices()->create(
            [
                'index' => $externalIndexName,
                'body' => $this->getIndexDefinition()
            ]
        );
    }

    /**
     * @param string $externalIndexName
     * @param Client $connection
     * @param DocumentMigratorInterface|null $documentMigrator
     */
    public function updateIndex(
        string $externalIndexName,
        Client $connection,
        ?DocumentMigratorInterface $documentMigrator = null
    ): void {
        $migrationIndexName = $externalIndexName . '__migrating';

        $this->createIndex($migrationIndexName, $connection);

        // fetch documents from current index and store to new index
        $searchResult = $connection->search(
            [
                'index' => $externalIndexName,
                'scroll' => '1m',
            ]
        );

        $this->migrateDocuments($searchResult, $connection, $migrationIndexName, $documentMigrator);

        $context = $searchResult['_scroll_id'];
        while (true) {
            $scrollResult = $connection->scroll([
                'scroll_id' => $context,
                'scroll' => '1m',
            ]);

            if (count($scrollResult['hits']['hits']) === 0) {
                break;
            }

            $this->migrateDocuments($scrollResult, $connection, $migrationIndexName, $documentMigrator);

            $context = $scrollResult['_scroll_id'];
        }

        // remove old index
        $this->deleteIndex($externalIndexName, $connection);

        // recreate the index
        $this->createIndex($externalIndexName, $connection);

        // fetch documents from current index and store to new index
        $searchResult = $connection->search(
            [
                'index' => $migrationIndexName,
                'scroll' => '1m',
            ]
        );

        $this->moveDocuments($searchResult, $connection, $externalIndexName);

        $context = $searchResult['_scroll_id'];
        while (true) {
            $scrollResult = $connection->scroll([
                'scroll_id' => $context,
                'scroll' => '1m',
            ]);

            if (count($scrollResult['hits']['hits']) === 0) {
                break;
            }

            $this->moveDocuments($scrollResult, $connection, $externalIndexName);

            $context = $scrollResult['_scroll_id'];
        }

        $this->deleteIndex($migrationIndexName, $connection);
    }

    /**
     * @param string $externalIndexName
     * @param Client $connection
     */
    public function deleteIndex(string $externalIndexName, Client $connection): void
    {
        $connection->indices()->delete(['index' => $externalIndexName]);
    }

    /**
     * @param array $searchResult
     * @param Client $connection
     * @param string $toIndexName
     * @param DocumentMigratorInterface|null $documentManager
     * @return void
     */
    protected function migrateDocuments(
        array $searchResult,
        Client $connection,
        string $toIndexName,
        ?DocumentMigratorInterface $documentManager = null
    ): void {
        foreach ($searchResult['hits']['hits'] as $hit) {
            $connection->index([
                'index' => $toIndexName,
                'id' => $hit['_id'],
                'body' => $documentManager ? $documentManager->migrate($hit['_source']) : $hit['_source']
            ]);
        }
    }

    /**
     * @param array $searchResult
     * @param Client $connection
     * @param string $toIndexName
     */
    protected function moveDocuments(array $searchResult, Client $connection, string $toIndexName): void
    {
        foreach ($searchResult['hits']['hits'] as $hit) {
            $connection->index([
                'index' => $toIndexName,
                'id' => $hit['_id'],
                'body' => $hit['_source']
            ]);
        }
    }
}
