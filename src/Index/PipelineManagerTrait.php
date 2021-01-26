<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
trait PipelineManagerTrait
{
    /**
     * @return array
     */
    abstract protected function getPipelineDefinition(): array;

    /**
     * @param string $externalPipelineName
     * @param Client $connection
     * @param callable|null $responseHandler
     */
    public function createPipeline(
        string $externalPipelineName,
        Client $connection,
        ?callable $responseHandler = null
    ): void {
        $response = $connection->ingest()->putPipeline([
            'id' => $externalPipelineName,
            'body' => $this->getPipelineDefinition()
        ]);
        if (is_callable($responseHandler)) {
            $responseHandler($response);
        }
    }

    /**
     * @param string $externalPipelineName
     * @param Client $connection
     * @param callable|null $responseHandler
     */
    public function deletePipeline(
        string $externalPipelineName,
        Client $connection,
        ?callable $responseHandler = null
    ): void {
        $response = $connection->ingest()->deletePipeline(['id' => $externalPipelineName]);
        if (is_callable($responseHandler)) {
            $responseHandler($response);
        }
    }
}
