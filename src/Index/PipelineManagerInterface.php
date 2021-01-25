<?php

namespace Ellinaut\ElasticsearchConnector\Index;

use Elasticsearch\Client;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface PipelineManagerInterface
{
    /**
     * @param Client $connection
     */
    public function createPipeline(Client $connection): void;

    /**
     * @param Client $connection
     */
    public function deletePipeline(Client $connection): void;
}
