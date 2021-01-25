<?php

namespace Ellinaut;

use Elasticsearch\Client;
use Ellinaut\Document\DocumentManagerInterface;
use Ellinaut\ElasticsearchConnector\Connection\ConnectionFactoryInterface;
use Ellinaut\Exception\MissingDocumentManagerException;
use Ellinaut\Exception\MissingIndexManagerException;
use Ellinaut\Index\IndexManagerInterface;
use Ellinaut\Index\PipelineManagerInterface;
use Ellinaut\IndexName\IndexNameProviderInterface;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class ElasticsearchConnector
{
    /**
     * @var ConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @var IndexNameProviderInterface
     */
    private $indexNameProvider;

    /**
     * @var IndexManagerInterface[]
     */
    private $indexManagers = [];

    /**
     * @var PipelineManagerInterface[]
     */
    private $pipelineManagers;

    /**
     * @var DocumentManagerInterface[]
     */
    private $documentManagers = [];

    /**
     * @var Client|null
     */
    private $connection;

    /**
     * @var int
     */
    private $maxQueueSize;

    /**
     * @var int
     */
    private $queueSize = 0;

    /**
     * @var string|null
     */
    private $queuePipeline;

    /**
     * @var array
     */
    private $executionQueue = [];

    /**
     * @var bool
     */
    private $forceRefresh;

    /**
     * ElasticsearchConnector constructor.
     * @param ConnectionFactoryInterface $connectionFactory
     * @param IndexNameProviderInterface $indexNameProvider
     * @param int $bulkSize
     * @param bool $forceRefresh
     */
    public function __construct(
        ConnectionFactoryInterface $connectionFactory,
        IndexNameProviderInterface $indexNameProvider,
        int $bulkSize = 50,
        bool $forceRefresh = true
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->indexNameProvider = $indexNameProvider;
        $this->maxQueueSize = $bulkSize;
        $this->forceRefresh = $forceRefresh;
    }

    /**
     * @param string $internalIndexName
     * @param IndexManagerInterface $indexManager
     */
    public function addIndexManager(string $internalIndexName, IndexManagerInterface $indexManager): void
    {
        $this->indexManagers[$internalIndexName] = $indexManager;
    }

    /**
     * @param PipelineManagerInterface $pipelineManager
     */
    public function addPipelineManager(PipelineManagerInterface $pipelineManager): void
    {
        $this->pipelineManagers[] = $pipelineManager;
    }

    /**
     * @param string $internalIndexName
     * @param DocumentManagerInterface $documentManager
     */
    public function addDocumentManager(string $internalIndexName, DocumentManagerInterface $documentManager): void
    {
        $this->documentManagers[$internalIndexName] = $documentManager;
    }

    public function executeSetupProcess(): void
    {
        $this->createPipelines();

        foreach (array_keys($this->indexManagers) as $internalIndexName) {
            $this->recreateIndex($internalIndexName);
        }
    }

    /**
     * @return Client
     */
    public function getConnection(): Client
    {
        if (!$this->connection) {
            $this->connectionFactory->createConnection();
        }

        return $this->connection;
    }

    /**
     * @return IndexNameProviderInterface
     */
    public function getIndexNameProvider(): IndexNameProviderInterface
    {
        return $this->indexNameProvider;
    }

    /**
     * @param string $internalIndexName
     * @return IndexManagerInterface
     */
    public function getIndexManager(string $internalIndexName): IndexManagerInterface
    {
        if (!array_key_exists($internalIndexName, $this->indexManagers)) {
            throw new MissingIndexManagerException($internalIndexName);
        }

        return $this->indexManagers[$internalIndexName];
    }

    /**
     * @param string $internalIndexName
     * @return DocumentManagerInterface
     */
    public function getDocumentManager(string $internalIndexName): DocumentManagerInterface
    {
        if (!array_key_exists($internalIndexName, $this->indexManagers)) {
            throw new MissingDocumentManagerException($internalIndexName);
        }

        return $this->documentManagers[$internalIndexName];
    }

    /**
     * @param string $internalIndexName
     */
    public function createIndexIfNotExist(string $internalIndexName): void
    {
        $indexName = $this->getIndexNameProvider()->getExternalIndexName($internalIndexName);
        if ($this->getConnection()->indices()->exists(['index' => $indexName])) {
            return;
        }

        $this->createIndex($internalIndexName);
    }

    /**
     * @param string $internalIndexName
     */
    public function createIndex(string $internalIndexName): void
    {
        $this->getIndexManager($internalIndexName)->createIndex(
            $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
            $this->getConnection()
        );
    }

    /**
     * @param string $internalIndexName
     */
    public function recreateIndex(string $internalIndexName): void
    {
        $indexName = $this->getIndexNameProvider()->getExternalIndexName($internalIndexName);
        if ($this->getConnection()->indices()->exists(['index' => $indexName])) {
            $this->deleteIndex($internalIndexName);
        }

        $this->createIndex($internalIndexName);
    }

    /**
     * @param string $internalIndexName
     */
    public function updateIndex(string $internalIndexName): void
    {
        $this->getIndexManager($internalIndexName)->updateIndex(
            $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
            $this->getConnection(),
            $this->getDocumentManager($internalIndexName)
        );
    }

    /**
     * @param string $internalIndexName
     */
    public function deleteIndex(string $internalIndexName): void
    {
        $this->getIndexManager($internalIndexName)->deleteIndex(
            $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
            $this->getConnection()
        );
    }

    public function createPipelines(): void
    {
        foreach ($this->pipelineManagers as $pipelineManager) {
            $pipelineManager->createPipeline($this->getConnection());
        }
    }

    public function deletePipelines(): void
    {
        foreach ($this->pipelineManagers as $pipelineManager) {
            $pipelineManager->deletePipeline($this->getConnection());
        }
    }

    /**
     * @param string $internalIndexName
     * @param string $id
     * @param object $object
     * @param string|null $pipeline
     */
    public function indexDocument(string $internalIndexName, string $id, object $object, ?string $pipeline = null): void
    {
        if ($this->maxQueueSize <= 0) {
            $this->indexDocumentImmediately($internalIndexName, $id, $object, $pipeline);

            return;
        }

        if ($pipeline !== $this->queuePipeline) {
            // execute the queue with the last pipeline, so next commands can use the new one
            $this->executeQueueImmediately();
            $this->queuePipeline = $pipeline;
        }

        $this->executionQueue[] = [
            'index' => [
                '_index' => $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
                '_id' => $id,
            ]
        ];
        $this->executionQueue[] = $this->getDocumentManager($internalIndexName)->buildSourceFromObject($object);
        $this->queueSize++;

        $this->executeQueueIfSizeReached();
    }

    /**
     * @param string $internalIndexName
     * @param string $id
     * @param object $object
     * @param string|null $pipeline
     */
    public function indexDocumentImmediately(
        string $internalIndexName,
        string $id,
        object $object,
        ?string $pipeline = null
    ): void {
        $request = [
            'index' => $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
            'id' => $id,
            'refresh' => $this->forceRefresh,
            'body' => $this->getDocumentManager($internalIndexName)->buildSourceFromObject($object),
        ];

        if ($pipeline) {
            $request['pipeline'] = $pipeline;
        }

        $this->getConnection()->index($request);
    }

    /**
     * @param string $internalIndexName
     * @param string $id
     */
    public function deleteDocument(string $internalIndexName, string $id): void
    {
        if ($this->maxQueueSize <= 0) {
            $this->deleteDocumentImmediately($internalIndexName, $id);

            return;
        }

        $this->executionQueue[] = [
            'delete' => [
                '_index' => $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
                '_id' => $id,
            ]
        ];
        $this->queueSize++;

        $this->executeQueueIfSizeReached();
    }

    /**
     * @param string $internalIndexName
     * @param string $id
     */
    public function deleteDocumentImmediately(string $internalIndexName, string $id): void
    {
        $this->getConnection()->delete([
            'index' => $this->getIndexNameProvider()->getExternalIndexName($internalIndexName),
            'id' => $id,
            'refresh' => $this->forceRefresh,
        ]);
    }

    protected function executeQueueIfSizeReached(): void
    {
        if ($this->queueSize >= $this->maxQueueSize) {
            $this->executeQueueImmediately();
        }
    }

    public function executeQueueImmediately(): void
    {
        // don't execute on empty queue
        if (count($this->executionQueue) === 0) {
            return;
        }

        $bulkRequest = ['body' => $this->executionQueue, 'refresh' => $this->forceRefresh];
        if ($this->queuePipeline) {
            $bulkRequest['pipeline'] = $this->queuePipeline;
        }

        $this->getConnection()->bulk($bulkRequest);

        // clear queue
        $this->queueSize = 0;
        $this->executionQueue = [];
    }

    /**
     * @param string $internalIndexName
     * @param array $parameters
     * @return array
     */
    public function executeSingleIndexSearch(string $internalIndexName, array $parameters): array
    {
        $parameters['index'] = $this->getIndexNameProvider()->getExternalIndexName($internalIndexName);

        return $this->getConnection()->search($parameters);
    }

    /**
     * @param string[] $internalIndexNames
     * @param array $parameters
     * @return array
     */
    public function executeSearch(array $internalIndexNames, array $parameters): array
    {
        $indexNames = [];
        foreach ($internalIndexNames as $internalIndexName) {
            $indexNames[] = $this->getIndexNameProvider()->getExternalIndexName($internalIndexName);
        }
        $parameters['index'] = implode(',', $indexNames);

        return $this->getConnection()->search($parameters);
    }

    /**
     * @param array $result
     * @return object[]
     */
    public function buildObjectsFromSearchResult(array $result): array
    {
        if (!array_key_exists('hits', $result) || !is_array($result['hits'])) {
            return [];
        }

        if (!array_key_exists('hits', $result['hits']) || !is_array($result['hits']['hits'])) {
            return [];
        }

        $objects = [];
        foreach ($result['hits']['hits'] as $hit) {
            $internalIndexName = $this->getIndexNameProvider()->getInternalIndexName($hit['_index']);
            $objects[] = $this->getDocumentManager($internalIndexName)->buildObjectFromSource(
                (string)$hit['_id'],
                $hit['_source'] ?? []
            );
        }

        return $objects;
    }
}
