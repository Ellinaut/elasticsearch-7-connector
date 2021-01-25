<?php

namespace Ellinaut\ElasticsearchConnector\IndexName;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface IndexNameProviderInterface
{
    /**
     * @param string $internalIndexName
     * @return string
     */
    public function getExternalIndexName(string $internalIndexName): string;

    /**
     * @param string $externalIndexName
     * @return string
     */
    public function getInternalIndexName(string $externalIndexName): string;
}
