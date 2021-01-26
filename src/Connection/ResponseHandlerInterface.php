<?php

namespace Ellinaut\ElasticsearchConnector\Connection;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $response
     */
    public function handleResponse(array $response): void;
}
