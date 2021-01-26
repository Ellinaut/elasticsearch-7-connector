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
     * @param callable|null $responseHandler
     */
    public function createPipeline(
        string $externalPipelineName,
        Client $connection,
        ?callable $responseHandler = null
    ): void;

    /**
     * @param string $externalPipelineName
     * @param Client $connection
     * @param callable|null $responseHandler
     */
    public function deletePipeline(
        string $externalPipelineName,
        Client $connection,
        ?callable $responseHandler = null
    ): void;
}
