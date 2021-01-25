<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
abstract class AbstractPipelineManager implements PipelineManagerInterface
{
    /**
     * @param Client $connection
     */
    public function createPipeline(Client $connection): void
    {
        $connection->ingest()->putPipeline([
            'id' => static::getPipelineName(),
            'body' => $this->getPipelineDefinition()
        ]);
    }

    /**
     * @param Client $connection
     */
    public function deletePipeline(Client $connection): void
    {
        $connection->ingest()->deletePipeline(['id' => static::getPipelineName()]);
    }

    /**
     * @return string
     */
    abstract protected static function getPipelineName(): string;

    /**
     * @return array
     */
    abstract protected function getPipelineDefinition(): array;
}
