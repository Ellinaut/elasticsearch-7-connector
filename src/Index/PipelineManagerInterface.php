<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface PipelineManagerInterface
{
    /**
     * @param string $externalPipelineName
     * @param Client $connection
     */
    public function createPipeline(string $externalPipelineName, Client $connection): void;

    /**
     * @param string $externalPipelineName
     * @param Client $connection
     */
    public function deletePipeline(string $externalPipelineName, Client $connection): void;
}
