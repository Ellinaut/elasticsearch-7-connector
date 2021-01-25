<?php

namespace Ellinaut\IndexName;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class PrefixedIndexNameProvider implements IndexNameProviderInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * PrefixedIndexNameProvider constructor.
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param string $internalIndexName
     * @return string
     */
    public function getExternalIndexName(string $internalIndexName): string
    {
        return $this->prefix . $internalIndexName;
    }

    /**
     * @param string $externalIndexName
     * @return string
     */
    public function getInternalIndexName(string $externalIndexName): string
    {
        return substr($externalIndexName, strlen($this->prefix) - 1);
    }
}
