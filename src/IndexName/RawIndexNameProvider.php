<?php

namespace Ellinaut\IndexName;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class RawIndexNameProvider implements IndexNameProviderInterface
{
    /**
     * @param string $internalIndexName
     * @return string
     */
    public function getExternalIndexName(string $internalIndexName): string
    {
        return $internalIndexName;
    }

    /**
     * @param string $externalIndexName
     * @return string
     */
    public function getInternalIndexName(string $externalIndexName): string
    {
        return $externalIndexName;
    }
}
