<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
abstract class AbstractPipelineManager implements PipelineManagerInterface
{
    /**
     * @param string $externalPipelineName
     * @param Client $connection
     */
    public function createPipeline(string $externalPipelineName, Client $connection): void
    {
        $connection->ingest()->putPipeline([
            'id' => $externalPipelineName,
            'body' => $this->getPipelineDefinition()
        ]);
    }

    /**
     * @param string $externalPipelineName
     * @param Client $connection
     */
    public function deletePipeline(string $externalPipelineName, Client $connection): void
    {
        $connection->ingest()->deletePipeline(['id' => $externalPipelineName]);
    }

    /**
     * @return array
     */
    abstract protected function getPipelineDefinition(): array;
}
